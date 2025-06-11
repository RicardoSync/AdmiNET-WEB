<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo "Método no permitido";
  exit;
}

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];
$direccion = $_POST['direccion'];
$dia_corte = $_POST['dia_corte'];
$estado = $_POST['estado'];
$id_antena_ap = $_POST['id_antena_ap'] ?: null;
$id_servicio_plataforma = $_POST['id_servicio_plataforma'] ?: null;
$id_paquete = $_POST['id_paquete'];
$id_microtik = $_POST['id_microtik'];
$ubicacion_maps = $_POST['ubicacion_maps'];
$tipo_conexion = $_POST['tipo_conexion'];

try {
  // Obtener cliente actual
  $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
  $stmt->execute([$id]);
  $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$cliente) throw new Exception("Cliente no encontrado");

  $nombreAnterior = $cliente['nombre'];
  $ip_cliente = $cliente['ip_cliente']; // bloqueamos edición
  $id_paquete_anterior = $cliente['id_paquete'];

  // Actualizar base de datos (sin tocar IP)
  $sql = "UPDATE clientes SET nombre=?, telefono=?, email=?, direccion=?, dia_corte=?, estado=?, id_antena_ap=?, id_servicio_plataforma=?, id_paquete=?, id_microtik=?, ubicacion_maps=?, tipo_conexion=? WHERE id=?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([
    $nombre, $telefono, $email, $direccion, $dia_corte, $estado,
    $id_antena_ap, $id_servicio_plataforma, $id_paquete, $id_microtik,
    $ubicacion_maps, $tipo_conexion, $id
  ]);

  // Obtener MikroTik
  $stmt = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
  $stmt->execute([$cliente['id_microtik']]);
  $mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($mikrotik) {
    $ipMK = $mikrotik['ip'];
    $userMK = $mikrotik['username'];
    $passMK = $mikrotik['password'];
    $portMK = intval($mikrotik['port']);
    $target = $ip_cliente . "/32";

    // --- Cambio de nombre
    if ($nombre !== $nombreAnterior) {
      $cmd = "/queue simple set [find target=\"$target\"] name=\"$nombre\"";
      $ok = ejecutarComandoSSH($ipMK, $userMK, $passMK, $portMK, $cmd);

      $conn->prepare("INSERT INTO logs_acciones_red (id_cliente, ip_mikrotik, accion, resultado, mensaje) VALUES (?, ?, 'ActualizarNombre', ?, ?)")
        ->execute([$id, $ipMK, $ok ? 'OK' : 'ERROR', $cmd]);
    }

    // --- Cambio de paquete (velocidad)
    if ($id_paquete != $id_paquete_anterior) {
      $stmt = $conn->prepare("SELECT velocidad FROM paquetes WHERE id = ?");
      $stmt->execute([$id_paquete]);
      $paq = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($paq && $paq['velocidad']) {
        [$down, $up] = explode("/", $paq['velocidad']);

        // Convertir velocidad (ej: "5M", "2000K") a Kbps enteros
        function valorKbps($val) {
          $val = strtoupper($val);
          if (str_ends_with($val, 'M')) return intval(floatval($val) * 1000);
          if (str_ends_with($val, 'K')) return intval($val);
          return intval($val); // fallback
        }

        $downNum = valorKbps($down);
        $upNum = valorKbps($up);

        // Calcular burst como enteros
        $downBurst = intval($downNum * 1.5) . "K";
        $upBurst = intval($upNum * 1.5) . "K";

        $downThreshold = intval($downNum * 0.9) . "K";
        $upThreshold = intval($upNum * 0.9) . "K";

        $cmd = "/queue simple set [find target=\"$target\"] max-limit={$downNum}K/{$upNum}K burst-limit=$downBurst/$upBurst burst-threshold=$downThreshold/$upThreshold burst-time=20s/20s";


        $ok = ejecutarComandoSSH($ipMK, $userMK, $passMK, $portMK, $cmd);

        $conn->prepare("INSERT INTO logs_acciones_red (id_cliente, ip_mikrotik, accion, resultado, mensaje) VALUES (?, ?, 'ActualizarVelocidad', ?, ?)")
          ->execute([$id, $ipMK, $ok ? 'OK' : 'ERROR', $cmd]);
      }
    }
  }

  header("Location: ../clientes.php?msg=actualizado");
  exit;

} catch (Exception $e) {
  echo "Error: " . $e->getMessage();
}

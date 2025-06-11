<?php
require_once '../includes/db.php';
require_once __DIR__ . '/../includes/SSHHelper.php'; // ✅ correcto


if (!isset($_POST['id'])) {
  http_response_code(400);
  echo "ID de cliente no proporcionado.";
  exit;
}

$id_cliente = intval($_POST['id']);

try {
  $stmt = $conn->prepare("SELECT c.nombre, c.ip_cliente, m.ip AS mk_ip, m.username, m.password, m.port
                          FROM clientes c
                          INNER JOIN credenciales_microtik m ON c.id_microtik = m.id
                          WHERE c.id = ?");
  $stmt->execute([$id_cliente]);
  $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$cliente) {
    http_response_code(404);
    echo "Cliente no encontrado.";
    exit;
  }

  if (!$cliente['ip_cliente']) {
    echo "⚠️ El cliente no tiene una IP asignada.";
    exit;
  }

  $ip_cliente = $cliente['ip_cliente'];
  $comentario = addslashes($cliente['nombre']);
  $comando = "/ip/firewall/address-list/add list=corte address=\"$ip_cliente\" comment=\"$comentario\"";

  $exito = ejecutarComandoSSH(
    $cliente['mk_ip'],
    $cliente['username'],
    $cliente['password'],
    $cliente['port'] ?: 22,
    $comando
  );

  $resultado = $exito ? "Éxito" : "Error";
  $mensajeLog = $exito ? "Cliente suspendido correctamente." : "Fallo al ejecutar comando SSH.";

  if ($exito) {
    $updateEstado = $conn->prepare("UPDATE clientes SET estado = 'Bloqueado' WHERE id = ?");
    if ($updateEstado->execute([$id_cliente])) {
      error_log("✅ Estado actualizado para cliente ID $id_cliente");
    } else {
      error_log("❌ Falló al actualizar estado para cliente ID $id_cliente");
    }
  }

  // Log en la base de datos
  $log = $conn->prepare("INSERT INTO logs_acciones_red (id_cliente, ip_mikrotik, accion, resultado, mensaje) 
                         VALUES (?, ?, 'Suspensión', ?, ?)");
  $log->execute([$id_cliente, $cliente['mk_ip'], $resultado, $mensajeLog]);

  echo $mensajeLog;
} catch (Exception $e) {
  http_response_code(500);
  echo "Error inesperado: " . $e->getMessage();
}
?>

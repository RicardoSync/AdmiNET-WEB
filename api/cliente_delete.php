<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo "No autorizado";
  exit;
}

require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  http_response_code(400);
  echo "ID inválido";
  exit;
}

$id = intval($_GET['id']);

try {
  // 1. Obtener datos del cliente antes de eliminarlo
  $stmt = $conn->prepare("SELECT ip_cliente, id_microtik FROM clientes WHERE id = ?");
  $stmt->execute([$id]);
  $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$cliente) {
    echo "Cliente no encontrado.";
    exit;
  }

  $ip_cliente = $cliente['ip_cliente'] . "/32";

  // 2. Obtener credenciales del MikroTik
  $stmt = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
  $stmt->execute([$cliente['id_microtik']]);
  $mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

  $resultadoMK = "No se eliminó de MikroTik";

  if ($mikrotik && $ip_cliente) {
    $cmd = "/queue simple remove [find target=\"$ip_cliente\"]";
    $ok = ejecutarComandoSSH(
      $mikrotik['ip'],
      $mikrotik['username'],
      $mikrotik['password'],
      intval($mikrotik['port']),
      $cmd
    );

    $resultadoMK = $ok ? "Eliminado de MikroTik" : "Error al eliminar de MikroTik";

    // Log de eliminación en MikroTik
    $conn->prepare("INSERT INTO logs_acciones_red (id_cliente, ip_mikrotik, accion, resultado, mensaje) VALUES (?, ?, 'EliminarClienteMK', ?, ?)")
      ->execute([$id, $mikrotik['ip'], $ok ? 'OK' : 'ERROR', $cmd]);
  }

  // 3. Registrar eliminación en la base de datos ANTES de borrar al cliente
  $conn->prepare("INSERT INTO logs_acciones_red (id_cliente, ip_mikrotik, accion, resultado, mensaje) VALUES (?, ?, 'EliminarClienteBD', 'OK', 'Eliminado localmente de la base de datos')")
       ->execute([$id, $mikrotik['ip'] ?? 'N/A']);

  // 4. Eliminar cliente de la base de datos
  $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
  $stmt->execute([$id]);

  if ($stmt->rowCount()) {
    echo "Cliente eliminado correctamente. $resultadoMK";
  } else {
    echo "Cliente ya eliminado.";
  }

} catch (PDOException $e) {
  http_response_code(500);
  echo "Error al eliminar cliente: " . $e->getMessage();
}

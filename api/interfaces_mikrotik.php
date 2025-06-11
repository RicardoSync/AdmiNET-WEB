<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/SSHHelper.php';

if (!isset($_GET['id'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Falta el ID del MikroTik']);
  exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT ip, username, password, port FROM credenciales_microtik WHERE id = ?");
$stmt->execute([$id]);
$mk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mk) {
  http_response_code(404);
  echo json_encode(['error' => 'MikroTik no encontrado']);
  exit;
}

// Ejecutar comando para obtener interfaces
$comando = "/interface/print without-paging terse";
$respuesta = ejecutarComandoSSHRetorno(
  $mk['ip'],
  $mk['username'],
  $mk['password'],
  $mk['port'] ?: 22,
  $comando
);

// Filtrar solo los nombres de interfaz
$interfaces = [];
$lineas = explode("\n", $respuesta);
foreach ($lineas as $linea) {
  if (preg_match('/name=([^\s]+)/', $linea, $match)) {
    $interfaces[] = $match[1];
  }
}

echo json_encode($interfaces);

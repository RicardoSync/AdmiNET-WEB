<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if (!isset($_POST['id_mikrotik'])) {
  http_response_code(400);
  echo json_encode(["error" => "ID MikroTik no proporcionado"]);
  exit;
}

$id_mikrotik = intval($_POST['id_mikrotik']);

$stmt = $conn->prepare("SELECT ip, username, password, port FROM credenciales_microtik WHERE id = ?");
$stmt->execute([$id_mikrotik]);
$mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mikrotik) {
  http_response_code(404);
  echo json_encode(["error" => "MikroTik no encontrado"]);
  exit;
}

$salida = ejecutarComandoSSHRetorno(
  $mikrotik['ip'],
  $mikrotik['username'],
  $mikrotik['password'],
  $mikrotik['port'] ?: 22,
  '/queue/simple/print where parent=""'
);

$colas = [];
$lineas = explode("\n", $salida);
foreach ($lineas as $linea) {
  if (preg_match('/name="([^"]+)"/', $linea, $match)) {
    $colas[] = $match[1];
  }
}

echo json_encode($colas);

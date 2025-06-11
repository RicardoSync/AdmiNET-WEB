<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if (!isset($_POST['id_mikrotik'])) {
  http_response_code(400);
  echo json_encode(["error" => "ID de MikroTik no recibido"]);
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

$comando = '/queue/simple/print where parent=""';
$salida = ejecutarComandoSSHRetorno(
  $mikrotik['ip'],
  $mikrotik['username'],
  $mikrotik['password'],
  $mikrotik['port'] ?: 22,
  $comando
);

// Procesar salida para extraer los nombres
$colas = [];
foreach (explode("\n", $salida) as $linea) {
  if (preg_match('/name="([^"]+)"/', $linea, $match)) {
    $colas[] = $match[1];
  }
}

echo json_encode($colas);

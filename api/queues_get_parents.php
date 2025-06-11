<?php
require_once '../includes/db.php';
require_once __DIR__ . '/../includes/SSHHelper.php';

$id_microtik = $_GET['id'] ?? null;
if (!$id_microtik) {
  echo json_encode([]);
  exit;
}

$stmt = $conn->prepare("SELECT ip, username, password, port FROM credenciales_microtik WHERE id = ?");
$stmt->execute([$id_microtik]);
$mk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mk) {
  echo json_encode([]);
  exit;
}

$comando = '/queue/simple/print where parent=""';
$respuesta = ejecutarComandoSSHRetorno($mk['ip'], $mk['username'], $mk['password'], $mk['port'], $comando);

$parents = [];
foreach (explode("\n", $respuesta) as $linea) {
  if (preg_match('/name="(.+?)"/', $linea, $match)) {
    $parents[] = $match[1];
  }
}

echo json_encode($parents);

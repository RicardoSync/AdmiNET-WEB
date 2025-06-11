<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/SSHHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
  http_response_code(400);
  exit('Solicitud inválida');
}

$id = intval($_POST['id']);
$stmt = $conn->prepare("SELECT ip, username, password, port FROM credenciales_microtik WHERE id = ?");
$stmt->execute([$id]);
$mk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mk) {
  http_response_code(404);
  exit('MikroTik no encontrado');
}

// Comandos que aplicarás por SSH
$comandos = [
  '/ip/firewall/filter/add chain=forward action=drop src-address-list=corte comment=corteDeInternet',
  '/ip/firewall/filter/add chain=forward action=drop dst-address-list=corte comment=corteDeInternet'
];

$todoOk = true;
foreach ($comandos as $cmd) {
  $resultado = ejecutarComandoSSH($mk['ip'], $mk['username'], $mk['password'], $mk['port'], $cmd);
  if (!$resultado) {
    $todoOk = false;
    break;
  }
}

if ($todoOk) {
  echo '✅ Reglas de firewall aplicadas correctamente.';
} else {
  http_response_code(500);
  echo '❌ Ocurrió un error al aplicar las reglas.';
}

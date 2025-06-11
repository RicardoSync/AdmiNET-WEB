<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

$interfaz = 'ether23';

$stmt = $conn->prepare("SELECT ip, username, password, port FROM credenciales_microtik WHERE nombre = 'Principal' LIMIT 1");
$stmt->execute();
$mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mikrotik) {
  http_response_code(500);
  echo "Sin MikroTik principal.";
  exit;
}

$comando = "/interface/ethernet/print stats where name=\"$interfaz\"";
$salida = ejecutarComandoSSHRetorno($mikrotik['ip'], $mikrotik['username'], $mikrotik['password'], intval($mikrotik['port']), $comando);

if (!$salida) {
  http_response_code(500);
  echo "No hubo salida.";
  exit;
}

preg_match('/rx-bytes:\s*(\d+)/', $salida, $matchRx);
preg_match('/tx-bytes:\s*(\d+)/', $salida, $matchTx);

$rx = isset($matchRx[1]) ? (int)$matchRx[1] : 0;
$tx = isset($matchTx[1]) ? (int)$matchTx[1] : 0;

$ultima = $conn->query("SELECT rx, tx FROM consumo_internet ORDER BY fecha DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$rx_diff = $rx;
$tx_diff = $tx;

if ($ultima) {
  $rx_diff = max(0, $rx - (int)$ultima['rx']);
  $tx_diff = max(0, $tx - (int)$ultima['tx']);
}

$total = $rx_diff + $tx_diff;

if ($rx_diff > 0 || $tx_diff > 0) {
  $stmt = $conn->prepare("INSERT INTO consumo_internet (fecha, rx, tx, total) VALUES (NOW(), ?, ?, ?)");
  $stmt->execute([$rx_diff, $tx_diff, $total]);
}

echo "Guardado correctamente";

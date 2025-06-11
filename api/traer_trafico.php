<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/SSHHelper.php';

if (!isset($_GET['id']) || !isset($_GET['interfaz'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Faltan parámetros']);
  exit;
}

$id_mikrotik = intval($_GET['id']);
$interfaz = $_GET['interfaz'];

function convertirABits($valor, $unidad) {
  $factor = 1;
  if (strtolower($unidad) === 'kbps') $factor = 1000;
  if (strtolower($unidad) === 'mbps') $factor = 1000000;
  return floatval($valor) * $factor;
}

try {
  $stmt = $conn->prepare("SELECT ip, username, password, port FROM credenciales_microtik WHERE id = ?");
  $stmt->execute([$id_mikrotik]);
  $mk = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$mk) {
    http_response_code(404);
    echo json_encode(['error' => 'MikroTik no encontrado']);
    exit;
  }

  $comando = "/interface/monitor-traffic $interfaz once";
  $respuesta = ejecutarComandoSSHRetorno(
    $mk['ip'],
    $mk['username'],
    $mk['password'],
    $mk['port'] ?: 22,
    $comando
  );

  preg_match('/rx-bits-per-second:\s*([0-9.]+)\s*([kM]?bps)/i', $respuesta, $rxMatch);
  preg_match('/tx-bits-per-second:\s*([0-9.]+)\s*([kM]?bps)/i', $respuesta, $txMatch);

  $rx = isset($rxMatch[1]) ? convertirABits($rxMatch[1], $rxMatch[2]) : 0;
  $tx = isset($txMatch[1]) ? convertirABits($txMatch[1], $txMatch[2]) : 0;

  $rx_mbps = round($rx / 1000000, 2);
  $tx_mbps = round($tx / 1000000, 2);

  echo json_encode([
    'rx_mbps' => $rx_mbps,
    'tx_mbps' => $tx_mbps
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error interno']);
}

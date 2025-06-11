<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

function parseMikrotikTerse($output) {
  $lines = explode("\n", trim($output));
  $leases = [];

  foreach ($lines as $line) {
    $data = [];
    preg_match_all('/(\S+)=("[^"]+"|\S+)/', $line, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
      $clave = $m[1];
      $valor = trim($m[2], '"');
      $data[$clave] = $valor;
    }

    if (isset($data['address'], $data['mac-address'])) {
      $leases[] = [
        'ip' => $data['address'],
        'mac' => $data['mac-address'],
        'hostname' => $data['host-name'] ?? '(sin nombre)',
        'status' => $data['status'] ?? '',
        'last_seen' => $data['last-seen'] ?? '',
      ];
    }
  }

  return $leases;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_mikrotik'])) {
  $id = intval($_POST['id_mikrotik']);

  $stmt = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
  $stmt->execute([$id]);
  $mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$mikrotik) {
    echo json_encode([]);
    exit;
  }

  $comando = "/ip/dhcp-server/lease/print terse";
  $salida = ejecutarComandoSSHRetorno(
    $mikrotik['ip'],
    $mikrotik['username'],
    $mikrotik['password'],
    intval($mikrotik['port']),
    $comando
  );

  if (!$salida) {
    echo json_encode([]);
    exit;
  }

  $leases = parseMikrotikTerse($salida);
  header('Content-Type: application/json');
  echo json_encode($leases);
}

<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if (!isset($_POST['id'])) {
  http_response_code(400);
  echo json_encode(["error" => "Falta ID"]);
  exit;
}

$id = intval($_POST['id']);

// Obtenemos MikroTik del cliente
$stmt = $conn->prepare("
  SELECT m.ip, m.username, m.password, m.port
  FROM clientes c
  INNER JOIN credenciales_microtik m ON c.id_microtik = m.id
  WHERE c.id = ?
");
$stmt->execute([$id]);
$mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mikrotik) {
  http_response_code(404);
  echo json_encode(["error" => "MikroTik no encontrado"]);
  exit;
}

try {
  $salida = ejecutarComandoSSH(
    $mikrotik['ip'],
    $mikrotik['username'],
    $mikrotik['password'],
    $mikrotik['port'] ?: 22,
    '/queue/simple/print where parent=""'
  );

  // Extrae los nombres
  preg_match_all('/name="([^"]+)"/', $salida, $matches);
  echo json_encode($matches[1]);

} catch (Exception $e) {
  echo json_encode(["error" => $e->getMessage()]);
}

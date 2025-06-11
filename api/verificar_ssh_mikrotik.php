<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

header('Content-Type: application/json');

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
  echo json_encode(['estado' => 'error']);
  exit;
}

// Obtener MikroTik desde DB
$stmt = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
$stmt->execute([$id]);
$mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mikrotik) {
  echo json_encode(['estado' => 'no_encontrado']);
  exit;
}

$ip = $mikrotik['ip'];
$user = $mikrotik['username'];
$pass = $mikrotik['password'];
$port = $mikrotik['port'] ?: 22;

try {
  $ssh = new \phpseclib3\Net\SSH2($ip, intval($port));
  if ($ssh->login($user, $pass)) {
    echo json_encode(['estado' => 'ok']);
  } else {
    echo json_encode(['estado' => 'fail']);
  }
} catch (Exception $e) {
  echo json_encode(['estado' => 'fail']);
}

<?php
require_once '../includes/SSHHelper.php';

header('Content-Type: application/json');

$ip = $_POST['ip'] ?? '';
$usuario = $_POST['usuario'] ?? '';
$contrasena = $_POST['password'] ?? '';
$puerto = $_POST['puerto'] ?? 22;

if (empty($ip) || empty($usuario)) {
  echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos']);
  exit;
}

try {
  $ssh = new \phpseclib3\Net\SSH2($ip, intval($puerto));
  if ($ssh->login($usuario, $contrasena)) {
    echo json_encode(['status' => 'ok']);
  } else {
    echo json_encode(['status' => 'fail']);
  }
} catch (Exception $e) {
  echo json_encode(['status' => 'fail']);
}

<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if (!isset($_POST['id']) || !isset($_POST['comando'])) {
  http_response_code(400);
  exit('Faltan parámetros');
}

$id = $_POST['id'];
$comando = trim($_POST['comando']);

$stmt = $conn->prepare("SELECT ip, username, password, port FROM credenciales_microtik WHERE id = ?");
$stmt->execute([$id]);
$mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mikrotik) {
  http_response_code(404);
  exit('MikroTik no encontrado');
}

use phpseclib3\Net\SSH2;
require_once '../vendor/autoload.php'; // Si usas phpseclib 3

$ssh = new SSH2($mikrotik['ip'], (int)$mikrotik['port']);
if (!$ssh->login($mikrotik['username'], $mikrotik['password'])) {
  http_response_code(500);
  exit('Error de autenticación SSH');
}

$output = $ssh->exec($comando);
echo $output ?: "Sin respuesta";

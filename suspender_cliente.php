<?php
require 'vendor/autoload.php';
use phpseclib3\Net\SSH2;
require_once 'includes/db.php';

session_start();
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo "⛔ No autorizado";
  exit;
}

if (!isset($_POST['id']) || !isset($_POST['ip']) || !isset($_POST['nombre'])) {
  http_response_code(400);
  echo "⚠️ Faltan datos obligatorios";
  exit;
}

$id = intval($_POST['id']);
$ip_cliente = $_POST['ip'];
$nombre_cliente = $_POST['nombre'];

$stmt = $conn->prepare("SELECT m.ip, m.username, m.password FROM clientes c 
                        JOIN credenciales_microtik m ON c.id_mikrotik = m.id 
                        WHERE c.id = ?");
$stmt->execute([$id]);
$cred = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cred) {
  http_response_code(404);
  echo "❌ No se encontraron credenciales del MikroTik";
  exit;
}

try {
  $ssh = new SSH2($cred['ip']);
  if (!$ssh->login($cred['username'], $cred['password'])) {
    http_response_code(403);
    echo "❌ Error: No se pudo conectar al MikroTik. Verifica IP, usuario o contraseña.";
    exit;
  }

  // Ejecutar comandos para suspender
  $comentario_address_list = '"' . $nombre_cliente . '"';
  $comentario_queue = '"' . $nombre_cliente . ' - Cliente bloqueado automáticamente"';

  $comandos = [
    "/ip/firewall/address-list/add list=corte address=$ip_cliente comment=$comentario_address_list",
    "/queue/simple/set comment=$comentario_queue [find where target=$ip_cliente/32]"
  ];

  foreach ($comandos as $cmd) {
    $ssh->exec($cmd);
  }

  // Actualizar estado en base de datos
  $update = $conn->prepare("UPDATE clientes SET estado = 'Suspendido' WHERE id = ?");
  $update->execute([$id]);

  echo "✅ Cliente suspendido correctamente.";
} catch (Exception $e) {
  http_response_code(500);
  echo "❌ Error al ejecutar comandos MikroTik: " . $e->getMessage();
}

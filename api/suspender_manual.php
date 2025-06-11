<?php
require '../../vendor/autoload.php';
use phpseclib3\Net\SSH2;

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

if (!isset($_POST['id'], $_POST['ip'], $_POST['nombre'], $_POST['ip_mk'], $_POST['usuario_mk'], $_POST['clave_mk'])) {
  echo "Faltan datos.";
  exit;
}

$id = intval($_POST['id']);
$ip = $_POST['ip'];
$nombre = $_POST['nombre'];
$ip_mk = $_POST['ip_mk'];
$user_mk = $_POST['usuario_mk'];
$pass_mk = $_POST['clave_mk'];

try {
  $ssh = new SSH2($ip_mk);
  if (!$ssh->login($user_mk, $pass_mk)) {
    echo "<script>alert('❌ Error: No se pudo conectar al MikroTik');window.history.back();</script>";
    exit;
  }

  $comentario = '"' . $nombre . '"';
  $comandos = [
    "/ip/firewall/address-list/add list=corte address=$ip comment=$comentario",
    "/queue/simple/set comment=\"$nombre - Cliente bloqueado automáticamente\" [find where target=$ip/32]"
  ];

  foreach ($comandos as $cmd) {
    $ssh->exec($cmd);
  }

  echo "<script>alert('✅ Cliente suspendido exitosamente');window.location.href='../suspender_cliente_manual.php';</script>";
} catch (Exception $e) {
  echo "<script>alert('⚠️ Error: {$e->getMessage()}');window.history.back();</script>";
}

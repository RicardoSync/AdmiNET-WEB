<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if (!isset($_POST['nombre'], $_POST['subred'], $_POST['max_limit'], $_POST['id_mikrotik'])) {
  http_response_code(400);
  echo "Faltan datos obligatorios.";
  exit;
}

$nombre = trim($_POST['nombre']);
$subred = trim($_POST['subred']);
$max_limit = trim($_POST['max_limit']);
$id_mikrotik = intval($_POST['id_mikrotik']);

// Obtener credenciales del MikroTik
$stmt = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
$stmt->execute([$id_mikrotik]);
$mk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mk) {
  http_response_code(404);
  echo "MikroTik no encontrado.";
  exit;
}

try {
  // 1. Crear en MikroTik
  $cmd = "/queue/simple/add name=\"$nombre\" target=$subred max-limit=$max_limit comment=\"Parent AdmiNET\"";
  ejecutarComandoSSH($mk['ip'], $mk['username'], $mk['password'], $mk['port'], $cmd);

  // 2. Registrar en base de datos
  $insert = $conn->prepare("INSERT INTO queue_parent (nombre, subred, max_limit, id_mikrotik) VALUES (?, ?, ?, ?)");
  $insert->execute([$nombre, $subred, $max_limit, $id_mikrotik]);

  header("Location: ../queue_parent.php");
  exit;
} catch (Exception $e) {
  echo "❌ Error al crear Queue Parent: " . $e->getMessage();
}

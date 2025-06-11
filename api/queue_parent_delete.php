<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

session_start();

if (!isset($_GET['id'])) {
  $_SESSION['msg_error'] = "❌ ID no proporcionado.";
  header("Location: ../queue_parent.php");
  exit;
}

$id = intval($_GET['id']);

// Obtener datos del queue parent
$stmt = $conn->prepare("SELECT * FROM queue_parent WHERE id = ?");
$stmt->execute([$id]);
$qp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$qp) {
  $_SESSION['msg_error'] = "❌ Queue parent no encontrado.";
  header("Location: ../queue_parent.php");
  exit;
}

// Obtener credenciales MikroTik
$stmt_mk = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
$stmt_mk->execute([$qp['id_mikrotik']]);
$mk = $stmt_mk->fetch(PDO::FETCH_ASSOC);

if (!$mk) {
  $_SESSION['msg_error'] = "❌ MikroTik asociado no encontrado.";
  header("Location: ../queue_parent.php");
  exit;
}

try {
  // Eliminar en MikroTik por subred
  $cmd = '/queue/simple/remove [find where target="' . $qp['subred'] . '"]';
  ejecutarComandoSSH($mk['ip'], $mk['username'], $mk['password'], $mk['port'] ?: 22, $cmd);

  // Eliminar de la base de datos
  $del = $conn->prepare("DELETE FROM queue_parent WHERE id = ?");
  $del->execute([$id]);

  $_SESSION['msg_success'] = "✅ Queue parent eliminado correctamente.";
  header("Location: ../queue_parent.php");
} catch (Exception $e) {
  $_SESSION['msg_error'] = "❌ Error al eliminar: " . $e->getMessage();
  header("Location: ../queue_parent.php");
}

<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
  $_SESSION['msg_error'] = "ID no especificado.";
  header("Location: ../mikrotik.php");
  exit;
}

$id = intval($_GET['id']);

try {
  $stmt = $conn->prepare("DELETE FROM credenciales_microtik WHERE id = ?");
  $stmt->execute([$id]);

  if ($stmt->rowCount() > 0) {
    $_SESSION['msg_success'] = "MikroTik eliminado correctamente.";
  } else {
    $_SESSION['msg_error'] = "El MikroTik no existe o ya fue eliminado.";
  }

} catch (Exception $e) {
  $_SESSION['msg_error'] = "Error al eliminar: " . $e->getMessage();
}

header("Location: ../mikrotik.php");
exit;

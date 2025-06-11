<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);

  try {
    $stmt = $conn->prepare("DELETE FROM paquetes WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['msg_success'] = "Paquete eliminado correctamente.";
    header("Location: ../paquetes.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al eliminar el paquete: " . $e->getMessage();
    header("Location: ../paquetes.php");
    exit;
  }
} else {
  $_SESSION['msg_error'] = "ID no especificado.";
  header("Location: ../paquetes.php");
  exit;
}

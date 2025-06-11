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
    $stmt = $conn->prepare("DELETE FROM equipos WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['msg_success'] = "Equipo eliminado correctamente.";
    header("Location: ../equipos.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al eliminar equipo: " . $e->getMessage();
    header("Location: ../equipos.php");
    exit;
  }
} else {
  $_SESSION['msg_error'] = "ID de equipo no especificado.";
  header("Location: ../equipos.php");
  exit;
}

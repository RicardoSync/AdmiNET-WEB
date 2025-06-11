<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

if (!isset($_GET['id'])) {
  $_SESSION['msg_error'] = "ID del servicio no especificado.";
  header("Location: ../servicios.php");
  exit;
}

$id = intval($_GET['id']);

try {
  $stmt = $conn->prepare("DELETE FROM serviciosplataforma WHERE idPlataformas = ?");
  $stmt->execute([$id]);

  $_SESSION['msg_success'] = "Servicio eliminado correctamente.";
  header("Location: ../servicios.php");
  exit;

} catch (Exception $e) {
  $_SESSION['msg_error'] = "Error al eliminar el servicio: " . $e->getMessage();
  header("Location: ../servicios.php");
  exit;
}

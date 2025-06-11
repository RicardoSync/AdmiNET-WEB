<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once '../ded/includes/db.php';


if (!isset($_GET['id'])) {
  $_SESSION['msg_error'] = "ID de localidad no especificado.";
  header("Location: ../localidades.php");
  exit;
}

$id = intval($_GET['id']);

try {
  $stmt = $conn->prepare("DELETE FROM antenasap WHERE idantenasAp = ?");
  $stmt->execute([$id]);

  $_SESSION['msg_success'] = "Localidad eliminada correctamente.";
  header("Location: /ded/localidades.php");
  exit;

} catch (Exception $e) {
  $_SESSION['msg_error'] = "Error al eliminar: " . $e->getMessage();
   header("Location: /ded/localidades.php");

  exit;
}

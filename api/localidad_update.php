<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $nombre = trim($_POST['nombre']);
  $modelo = trim($_POST['modelo']);
  $usuario = trim($_POST['usuario']);
  $ip = trim($_POST['ip']); // Notas

  try {
    $stmt = $conn->prepare("UPDATE antenasap SET nombre = ?, modelo = ?, usuario = ?, ip = ? WHERE idantenasAp = ?");
    $stmt->execute([$nombre, $modelo, $usuario, $ip, $id]);

    $_SESSION['msg_success'] = "Localidad actualizada correctamente.";
    header("Location: ../localidades.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al actualizar: " . $e->getMessage();
    header("Location: ../localidad_editar.php?id=" . $id);
    exit;
  }
} else {
  header("Location: ../localidades.php");
  exit;
}

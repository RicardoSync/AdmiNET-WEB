<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id         = intval($_POST['id']);
  $nombreWisp = trim($_POST['nombreWisp']);
  $rfc        = trim($_POST['rfc']);
  $cp         = trim($_POST['cp']);
  $telefono   = trim($_POST['telefono']);
  $direccion  = trim($_POST['direccion']);

  try {
    $stmt = $conn->prepare("UPDATE datosEmpresa SET nombreWisp = ?, rfc = ?, cp = ?, telefono = ?, direccion = ? WHERE id = ?");
    $stmt->execute([$nombreWisp, $rfc, $cp, $telefono, $direccion, $id]);

    $_SESSION['msg_success'] = "Datos de empresa actualizados correctamente.";
    header("Location: ../empresa.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al actualizar datos: " . $e->getMessage();
    header("Location: ../empresa.php");
    exit;
  }
} else {
  header("Location: ../empresa.php");
  exit;
}

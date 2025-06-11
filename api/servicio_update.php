<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $nombre = trim($_POST['nombre']);
  $descripcion = trim($_POST['descripcion']);
  $precio = floatval($_POST['precio']);

  try {
    $stmt = $conn->prepare("UPDATE serviciosplataforma SET nombre = :nombre, descripcion = :descripcion, precio = :precio WHERE idPlataformas = :id");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':precio', $precio);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['msg_success'] = "Servicio actualizado correctamente.";
    header("Location: ../servicios.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al actualizar el servicio: " . $e->getMessage();
    header("Location: ../servicio_editar.php?id=" . $id);
    exit;
  }
} else {
  header("Location: ../servicios.php");
  exit;
}

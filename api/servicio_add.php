<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $descripcion = trim($_POST['descripcion']);
  $precio = floatval($_POST['precio']);

  try {
    $stmt = $conn->prepare("INSERT INTO serviciosplataforma (nombre, descripcion, precio) VALUES (:nombre, :descripcion, :precio)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':precio', $precio);
    $stmt->execute();

    $_SESSION['msg_success'] = "Servicio registrado correctamente.";
    header("Location: ../servicios.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al guardar el servicio: " . $e->getMessage();
    header("Location: ../servicio_nuevo.php");
    exit;
  }
} else {
  header("Location: ../servicios.php");
  exit;
}

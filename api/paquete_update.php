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
  $precio = floatval($_POST['precio']);
  $vel_subida = trim($_POST['vel_subida']);
  $vel_bajada = trim($_POST['vel_bajada']);
  $velocidad = $vel_subida . '/' . $vel_bajada;

  try {
    $stmt = $conn->prepare("UPDATE paquetes SET nombre = :nombre, velocidad = :velocidad, precio = :precio WHERE id = :id");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':velocidad', $velocidad);
    $stmt->bindParam(':precio', $precio);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['msg_success'] = "Paquete actualizado correctamente.";
    header("Location: ../paquetes.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al actualizar el paquete: " . $e->getMessage();
    header("Location: ../paquete_editar.php?id=" . $id);
    exit;
  }
} else {
  header("Location: ../paquetes.php");
  exit;
}

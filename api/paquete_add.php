<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $precio = floatval($_POST['precio']);
  $velSubida = trim($_POST['vel_subida']);
  $velBajada = trim($_POST['vel_bajada']);

  // Formato final para MikroTik: subida/bajada
  $velocidad = $velSubida . '/' . $velBajada;

  try {
    $stmt = $conn->prepare("INSERT INTO paquetes (nombre, velocidad, precio) VALUES (:nombre, :velocidad, :precio)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':velocidad', $velocidad);
    $stmt->bindParam(':precio', $precio);
    $stmt->execute();

    $_SESSION['msg_success'] = "Paquete registrado correctamente.";
    header("Location: ../paquetes.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al guardar el paquete: " . $e->getMessage();
    header("Location: ../paquete_nuevo.php");
    exit;
  }
} else {
  header("Location: ../paquetes.php");
  exit;
}

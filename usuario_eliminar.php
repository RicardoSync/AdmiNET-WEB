<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once 'includes/db.php';

if (!isset($_GET['id'])) {
  echo "ID no especificado.";
  exit;
}

$id = intval($_GET['id']);

try {
  $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
  $stmt->execute([$id]);

  header("Location: usuarios.php");
  exit;
} catch (PDOException $e) {
  echo "Error al eliminar: " . $e->getMessage();
}

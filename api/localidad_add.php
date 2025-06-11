<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $modelo = trim($_POST['modelo']);
  $usuario = trim($_POST['usuario']);
  $ip = trim($_POST['ip']); // Aquí usamos ip como notas

  try {
    $stmt = $conn->prepare("INSERT INTO antenasap (nombre, modelo, usuario, ip) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $modelo, $usuario, $ip]);

    $_SESSION['msg_success'] = "Localidad registrada correctamente.";
    header("Location: ../localidades.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al registrar la localidad: " . $e->getMessage();
    header("Location: ../localidad_nueva.php");
    exit;
  }
} else {
  header("Location: ../localidades.php");
  exit;
}

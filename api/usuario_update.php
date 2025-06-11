<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id       = $_POST['id'];
  $nombre   = trim($_POST['nombre']);
  $usuario  = trim($_POST['usuario']);
  $rol      = intval($_POST['rol']);
  $password = trim($_POST['password']);

  // Validación básica
  if (empty($nombre) || empty($usuario) || !in_array($rol, [0, 1, 2])) {
    echo "Datos inválidos.";
    exit;
  }

  try {
    if (!empty($password)) {
      // Si se ingresó nueva contraseña
      $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, password = ?, rol = ? WHERE id = ?");
      $stmt->execute([$nombre, $usuario, $password, $rol, $id]);
    } else {
      // Si no se cambió la contraseña
      $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, rol = ? WHERE id = ?");
      $stmt->execute([$nombre, $usuario, $rol, $id]);
    }

    header("Location: ../usuarios.php");
    exit;

  } catch (PDOException $e) {
    echo "Error al actualizar: " . $e->getMessage();
  }
} else {
  echo "Acceso denegado.";
}

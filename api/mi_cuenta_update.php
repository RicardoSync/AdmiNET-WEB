<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once '../includes/db_global.php';

$usuarioNuevo = trim($_POST['usuario']);
$password     = trim($_POST['password']);
$usuarioActual = $_SESSION['usuario'];

try {
  if (!empty($password)) {
    // Guardar con hash si quieres mayor seguridad:
    // $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios_empresas SET usuario = ?, password = ? WHERE usuario = ?");
    $stmt->execute([$usuarioNuevo, $password, $usuarioActual]);
  } else {
    $stmt = $conn->prepare("UPDATE usuarios_empresas SET usuario = ? WHERE usuario = ?");
    $stmt->execute([$usuarioNuevo, $usuarioActual]);
  }

  $_SESSION['usuario'] = $usuarioNuevo;
  header("Location: ../mi_cuenta.php");
  exit;
} catch (PDOException $e) {
  echo "Error al actualizar: " . $e->getMessage();
}

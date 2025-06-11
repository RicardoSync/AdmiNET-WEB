<?php
require_once '../includes/db.php';

$nombre = $_POST['nombre'];
$usuario = $_POST['usuario'];
$password = $_POST['password'];
$rol = intval($_POST['rol']);

$stmt = $conn->prepare("INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?, ?, ?, ?)");
$stmt->execute([$nombre, $usuario, $password, $rol]);

header("Location: ../usuarios.php");
exit;

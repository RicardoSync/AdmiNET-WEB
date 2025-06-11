<?php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Credenciales de conexión
$host = "softwarescobedo.com.mx";
$user = "adminet";
$pass = "MinuzaFea265/";

// Base por defecto si no hay una definida en la sesión
$db = isset($_SESSION['base_datos']) ? $_SESSION['base_datos'] : 'adminet_global';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Es preferible no mostrar detalles sensibles en producción
    die("Error de conexión a la base de datos.");
}
?>

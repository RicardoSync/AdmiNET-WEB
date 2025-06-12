<?php
require_once __DIR__ . '/config_global.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = isset($_SESSION['base_datos']) ? $_SESSION['base_datos'] : DB_NAME_GLOBAL;

try {
    $conn = new PDO("mysql:host=" . DB_HOST_GLOBAL . ";dbname=$db;charset=utf8mb4", DB_USER_GLOBAL, DB_PASS_GLOBAL);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión a la base de datos.");
}

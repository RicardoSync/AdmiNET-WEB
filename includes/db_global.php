<?php
$host = "softwarescobedo.com.mx";
$db   = "adminet_global";
$user = "adminet";
$pass = "MinuzaFea265/";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión global: " . $e->getMessage());
}
?>
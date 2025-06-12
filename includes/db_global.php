<?php
require_once __DIR__ . '/config_global.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST_GLOBAL . ";dbname=" . DB_NAME_GLOBAL . ";charset=utf8mb4", DB_USER_GLOBAL, DB_PASS_GLOBAL);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión global: " . $e->getMessage());
}

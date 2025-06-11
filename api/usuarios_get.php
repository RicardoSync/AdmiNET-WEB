<?php
require_once '../includes/db.php';

$stmt = $conn->query("SELECT id, nombre, usuario, rol FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($usuarios);

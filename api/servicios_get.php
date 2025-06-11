<?php
require_once __DIR__ . '/../includes/db.php';

$sql = "SELECT * FROM serviciosplataforma ORDER BY idPlataformas DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();

$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($servicios);

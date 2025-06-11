<?php
require_once __DIR__ . '/../includes/db.php';

$sql = "SELECT * FROM paquetes ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();

$paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($paquetes);

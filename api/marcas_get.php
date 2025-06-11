<?php
require_once '../includes/db.php';

$stmt = $conn->query("SELECT DISTINCT marca FROM equipos WHERE marca IS NOT NULL AND marca != '' ORDER BY marca ASC");
$marcas = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($marcas);

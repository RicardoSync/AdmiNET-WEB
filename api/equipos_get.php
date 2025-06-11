<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php';

$sql = "SELECT e.*, c.nombre AS nombre_cliente
        FROM equipos e
        LEFT JOIN clientes c ON e.id_cliente = c.id
        ORDER BY e.id DESC";

$result = $conn->query($sql);
$equipos = [];

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

  $equipos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($equipos);

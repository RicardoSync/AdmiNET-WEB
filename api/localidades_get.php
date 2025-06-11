<?php
// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión
require_once '../includes/db.php';

// Consulta
$sql = "SELECT idantenasAp, nombre, modelo, usuario, ip FROM antenasap ORDER BY nombre ASC";
$result = $conn->query($sql);

$zonas = [];

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
  $zonas[] = $row;
}


// Respuesta JSON
header('Content-Type: application/json');
echo json_encode($zonas, JSON_UNESCAPED_UNICODE);

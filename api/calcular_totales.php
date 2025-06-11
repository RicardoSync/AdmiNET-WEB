<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db.php';
header('Content-Type: application/json');

$inicio = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';

if (!$inicio || !$fin) {
  echo json_encode(['ingresos' => 0, 'egresos' => 0]);
  exit;
}

// Sumar ingresos
$stmt = $conn->prepare("SELECT SUM(total_real) AS ingresos FROM pagos WHERE FechaPago BETWEEN ? AND ?");
$stmt->execute([$inicio, $fin]);
$ingresos = $stmt->fetchColumn() ?: 0;

// Sumar egresos (si tienes una tabla de gastos individuales, o usa 0 si solo calculas aquí)
$egresos = 0; // O usa una tabla adicional si ya llevas egresos individuales

echo json_encode([
  'ingresos' => floatval($ingresos),
  'egresos' => floatval($egresos)
]);

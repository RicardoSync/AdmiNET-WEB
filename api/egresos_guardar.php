<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

$descripcion = trim($input['descripcion'] ?? '');
$monto = floatval($input['monto'] ?? 0);
$metodo = $input['metodo'] ?? '';

if ($descripcion === '' || $monto <= 0 || !in_array($metodo, ['Efectivo', 'Transferencia', 'Tarjeta'])) {
  echo json_encode(["status" => "error", "message" => "Datos inválidos o incompletos."]);
  exit;
}

try {
  $stmt = $conn->prepare("INSERT INTO egresos (descripcion, monto, metodo_pago) VALUES (?, ?, ?)");
  $stmt->execute([$descripcion, $monto, $metodo]);
  echo json_encode(["status" => "success"]);
} catch (Exception $e) {
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

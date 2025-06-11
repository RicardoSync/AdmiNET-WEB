<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$id = intval($input['id'] ?? 0);

if ($id <= 0) {
  echo json_encode(["status" => "error", "message" => "ID inválido."]);
  exit;
}

try {
  $stmt = $conn->prepare("DELETE FROM egresos WHERE id = ?");
  $stmt->execute([$id]);

  if ($stmt->rowCount()) {
    echo json_encode(["status" => "success"]);
  } else {
    echo json_encode(["status" => "error", "message" => "Egreso no encontrado."]);
  }
} catch (Exception $e) {
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

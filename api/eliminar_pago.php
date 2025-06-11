<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

$datos = json_decode(file_get_contents('php://input'), true);

if (!isset($datos['id'])) {
  echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
  exit;
}

$id = intval($datos['id']);

try {
  $stmt = $conn->prepare("DELETE FROM pagos WHERE id = ?");
  $stmt->execute([$id]);

  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

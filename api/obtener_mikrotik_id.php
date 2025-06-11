<?php
require_once '../includes/db.php';

if (!isset($_GET['id_cliente'])) {
  http_response_code(400);
  echo json_encode(["error" => "Falta id_cliente"]);
  exit;
}

$id = intval($_GET['id_cliente']);
$stmt = $conn->prepare("SELECT id_mikrotik FROM clientes WHERE id = ?");
$stmt->execute([$id]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
  echo json_encode($row);
} else {
  echo json_encode(["error" => "No encontrado"]);
}

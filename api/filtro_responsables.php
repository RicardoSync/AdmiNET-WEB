<?php
// ✅ Archivo: api/filtro_responsables.php
session_start();
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  exit("No autorizado");
}

require_once '../includes/db.php';

try {
  $stmt = $conn->query("SELECT id, nombre FROM usuarios ORDER BY nombre ASC");
  $responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);
  header('Content-Type: application/json');
  echo json_encode($responsables);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => "Error al cargar responsables"]);
}

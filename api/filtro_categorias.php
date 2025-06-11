<?php
// ✅ Archivo: api/filtro_categorias.php
session_start();
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  exit("No autorizado");
}

require_once '../includes/db.php';

try {
  $stmt = $conn->query("SELECT DISTINCT categoria FROM tickets WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC");
  $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
  header('Content-Type: application/json');
  echo json_encode($categorias);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => "Error al cargar categorías"]);
}

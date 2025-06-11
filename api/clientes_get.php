<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
  $stmt = $conn->query("SELECT id, nombre, ip_cliente, estado, dia_corte, tipo_conexion FROM clientes");
  $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($clientes);
} catch (PDOException $e) {
  echo json_encode(["error" => "Error al obtener los clientes: " . $e->getMessage()]);
}

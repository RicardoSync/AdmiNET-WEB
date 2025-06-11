<?php
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$busqueda = $data['busqueda'] ?? '';
$tipo = $data['tipo'] ?? '';
$marca = $data['marca'] ?? '';
$estado = $data['estado'] ?? '';
$cantidad = intval($data['cantidad'] ?? 10);
$pagina = intval($data['pagina'] ?? 1);
$offset = ($pagina - 1) * $cantidad;

$filtros = [];
$params = [];

if (!empty($busqueda)) {
  $filtros[] = "(e.nombre LIKE ? OR e.mac LIKE ? OR c.nombre LIKE ?)";
  $params[] = "%$busqueda%";
  $params[] = "%$busqueda%";
  $params[] = "%$busqueda%";
}
if (!empty($tipo)) {
  $filtros[] = "e.tipo = ?";
  $params[] = $tipo;
}
if (!empty($marca)) {
  $filtros[] = "e.marca = ?";
  $params[] = $marca;
}
if (!empty($estado)) {
  $filtros[] = "e.estado = ?";
  $params[] = $estado;
}

$where = $filtros ? "WHERE " . implode(" AND ", $filtros) : "";

// Total
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM equipos e LEFT JOIN clientes c ON e.id_cliente = c.id $where");
$stmtTotal->execute($params);
$total = $stmtTotal->fetchColumn();
$totalPaginas = ceil($total / $cantidad);

// Datos
$sql = "SELECT e.*, c.nombre AS nombre_cliente
        FROM equipos e
        LEFT JOIN clientes c ON e.id_cliente = c.id
        $where
        ORDER BY e.id DESC
        LIMIT $cantidad OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'equipos' => $equipos,
  'total_paginas' => $totalPaginas
]);

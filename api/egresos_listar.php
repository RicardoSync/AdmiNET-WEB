<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

$page = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$perPage = isset($_GET['por_pagina']) ? (int) $_GET['por_pagina'] : 10;
$metodo = $_GET['metodo'] ?? '';
$fecha = $_GET['fecha'] ?? '';

$where = [];
$params = [];

if ($metodo !== '') {
  $where[] = "metodo_pago = ?";
  $params[] = $metodo;
}

if ($fecha !== '') {
  $where[] = "DATE(fecha_egreso) = ?";
  $params[] = $fecha;
}

$whereClause = count($where) ? "WHERE " . implode(" AND ", $where) : "";

try {
  $totalStmt = $conn->prepare("SELECT COUNT(*) FROM egresos $whereClause");
  $totalStmt->execute($params);
  $totalRegistros = $totalStmt->fetchColumn();
  $totalPaginas = ceil($totalRegistros / $perPage);

  $offset = ($page - 1) * $perPage;
  $stmt = $conn->prepare("SELECT * FROM egresos $whereClause ORDER BY fecha_egreso DESC LIMIT $perPage OFFSET $offset");
  $stmt->execute($params);
  $egresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    "status" => "success",
    "data" => [
      "egresos" => $egresos,
      "total_paginas" => $totalPaginas
    ]
  ]);
} catch (Exception $e) {
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

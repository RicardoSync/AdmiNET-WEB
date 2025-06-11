<?php
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$busqueda = $data['busqueda'] ?? '';
$dia = $data['dia'] ?? '';
$mes = $data['mes'] ?? '';
$anio = $data['anio'] ?? '';
$cantidad = intval($data['cantidad'] ?? 10);
$pagina = intval($data['pagina'] ?? 1);
$offset = ($pagina - 1) * $cantidad;

$filtros = [];
$params = [];

if (!empty($busqueda)) {
  $filtros[] = "(clientes.nombre LIKE ? OR clientes.ip_cliente LIKE ?)";
  $params[] = "%$busqueda%";
  $params[] = "%$busqueda%";
}
if ($dia !== '') {
  $filtros[] = "DAY(fecha_pago) = ?";
  $params[] = $dia;
}
if ($mes !== '') {
  $filtros[] = "MONTH(fecha_pago) = ?";
  $params[] = $mes;
}
if ($anio !== '') {
  $filtros[] = "YEAR(fecha_pago) = ?";
  $params[] = $anio;
}

$where = $filtros ? "WHERE " . implode(" AND ", $filtros) : "";

// Total de registros
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM pagos INNER JOIN clientes ON pagos.id_cliente = clientes.id $where");
$stmtTotal->execute($params);
$total = $stmtTotal->fetchColumn();
$totalPaginas = ceil($total / $cantidad);

// Pagos con límite
$sql = "SELECT pagos.*, clientes.nombre 
        FROM pagos 
        INNER JOIN clientes ON pagos.id_cliente = clientes.id 
        $where 
        ORDER BY fecha_pago DESC 
        LIMIT $cantidad OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Aquí ya no generamos HTML, se lo dejamos a JS
echo json_encode([
  'pagos' => $pagos,
  'total_paginas' => $totalPaginas
]);

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$busqueda       = $data['busqueda'] ?? '';
$orden          = $data['orden'] ?? '';
$tipo_conexion  = $data['tipo_conexion'] ?? '';
$dia_corte      = $data['dia_corte'] ?? '';
$mikrotik       = $data['mikrotik'] ?? '';
$zona           = $data['zona'] ?? '';
$cantidad       = intval($data['cantidad'] ?? 10);
$pagina         = intval($data['pagina'] ?? 1);
$offset         = ($pagina - 1) * $cantidad;

// Filtros dinámicos
$filtros = [];
$params = [];

if (!empty($busqueda)) {
  $filtros[] = "(nombre LIKE ? OR ip_cliente LIKE ?)";
  $params[] = "%$busqueda%";
  $params[] = "%$busqueda%";
}
if ($tipo_conexion !== '') {
  $filtros[] = "tipo_conexion = ?";
  $params[] = $tipo_conexion;
}
if ($dia_corte !== '') {
  $filtros[] = "dia_corte = ?";
  $params[] = $dia_corte;
}
if ($mikrotik !== '') {
  $filtros[] = "id_mikrotik = ?";
  $params[] = $mikrotik;
}
if ($zona !== '') {
  $filtros[] = "id_antena_ap = ?";
  $params[] = $zona;
}

$whereSQL = $filtros ? "WHERE " . implode(" AND ", $filtros) : "";
$orderSQL = $orden == 'asc' ? "ORDER BY nombre ASC" : ($orden == 'desc' ? "ORDER BY nombre DESC" : "");

// ========================== TOTAL REGISTROS ==========================
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM clientes $whereSQL");
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $cantidad);

// ========================== CLIENTES PÁGINA ==========================
$sql = "SELECT id, nombre, ip_cliente, estado, dia_corte, tipo_conexion 
        FROM clientes 
        $whereSQL 
        $orderSQL 
        LIMIT $cantidad OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========================== GENERAR HTML ==========================
$html = "";
foreach ($clientes as $c) {
  $color = match($c['estado']) {
    "Activo" => "success",
    "Bloqueado" => "warning",
    "Suspendido" => "danger",
    "Cancelado" => "secondary",
    default => "light"
  };

$html .= "<tr>
  <td data-label='ID'>{$c['id']}</td>
  <td data-label='Nombre'>{$c['nombre']}</td>
  <td data-label='IP'>" . ($c['ip_cliente'] ?? '-') . "</td>
  <td data-label='Estado'><span class='badge bg-{$color}'>{$c['estado']}</span></td>
  <td data-label='Día Corte'>" . ($c['dia_corte'] ?? '-') . "</td>
  <td data-label='Tipo'>" . ($c['tipo_conexion'] == 0 ? 'Fibra' : 'Antena') . "</td>
  <td data-label='Acciones'>
    <a href='editar_cliente.php?id={$c['id']}' class='btn btn-primary btn-sm me-1'>
      <i class='bi bi-pencil'></i>
    </a>
    <button class='btn btn-danger btn-sm me-1 btn-eliminar' data-id='{$c['id']}'>
      <i class='bi bi-trash'></i>
    </button>
    <button class='btn btn-warning btn-sm me-1 btn-acciones'
            data-id='{$c['id']}'
            data-ip='{$c['ip_cliente']}'
            data-nombre=\"{$c['nombre']}\">
      <i class='bi bi-sliders'></i> Red
    </button>
    <a href='pago_nuevo.php?id={$c['id']}' class='btn btn-success btn-sm' title='Registrar Pago'><i class='bi bi-cash'></i></a>
    <a href='#' class='btn btn-info btn-sm btn-detalles' data-id='{$c['id']}'><i class='bi bi-eye'></i></a>
  </td>
</tr>";


}

header('Content-Type: application/json');

echo json_encode([
  'html' => $html,
  'total_paginas' => $totalPaginas
]);

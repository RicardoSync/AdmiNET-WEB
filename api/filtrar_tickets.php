<?php
// api/filtrar_tickets.php
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$estado = $data['estado'] ?? '';
$categoria = $data['categoria'] ?? '';
$responsable = $data['responsable'] ?? '';
$busqueda = $data['busqueda'] ?? '';
$cantidad = intval($data['cantidad'] ?? 10);
$pagina = intval($data['pagina'] ?? 1);
$offset = ($pagina - 1) * $cantidad;

$filtros = [];
$params = [];

if ($estado !== '') {
  $filtros[] = "t.estado = ?";
  $params[] = $estado;
}
if ($categoria !== '') {
  $filtros[] = "t.categoria = ?";
  $params[] = $categoria;
}
if ($responsable !== '') {
  $filtros[] = "t.id_responsable = ?";
  $params[] = $responsable;
}
if (!empty($busqueda)) {
  $filtros[] = "(c.nombre LIKE ? OR t.descripcion LIKE ?)";
  $params[] = "%$busqueda%";
  $params[] = "%$busqueda%";
}

$where = $filtros ? "WHERE " . implode(" AND ", $filtros) : "";

$sqlTotal = "SELECT COUNT(*) FROM tickets t
              LEFT JOIN clientes c ON t.id_cliente = c.id
              LEFT JOIN usuarios u ON t.id_responsable = u.id
              $where";
$stmt = $conn->prepare($sqlTotal);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$totalPaginas = ceil($total / $cantidad);

$sql = "SELECT t.*, c.nombre AS cliente, u.nombre AS responsable, t.ruta_evidencia
        FROM tickets t
        LEFT JOIN clientes c ON t.id_cliente = c.id
        LEFT JOIN usuarios u ON t.id_responsable = u.id
        $where
        ORDER BY t.fecha_creacion DESC
        LIMIT $cantidad OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
$html = "";
foreach ($tickets as $t) {
  $color = match ($t['estado']) {
    'Resuelto' => 'success',
    'Cerrado' => 'secondary',
    'En proceso' => 'warning',
    default => 'danger'
  };

  $html .= "<tr>
    <td data-label='ID'>{$t['id']}</td>
    <td data-label='Cliente'>" . htmlspecialchars($t['cliente']) . "</td>
    <td data-label='Categoría'>{$t['categoria']}</td>
    <td data-label='Descripción'>" . substr(htmlspecialchars($t['descripcion']), 0, 50) . "...</td>
    <td data-label='Estado'><span class='badge bg-$color'>{$t['estado']}</span></td>
    <td data-label='Creado'>{$t['fecha_creacion']}</td>
    <td data-label='Responsable'>" . htmlspecialchars($t['responsable'] ?? 'Sin asignar') . "</td>
    <td data-label='Acciones'>
      <a href=\"ticket_editar.php?id={$t['id']}\" class=\"btn btn-sm btn-primary me-1\">
        <i class=\"bi bi-pencil-square\"></i>
      </a>
      <a href=\"api/ticket_eliminar.php?id={$t['id']}\" class=\"btn btn-sm btn-danger me-1\" onclick=\"return confirm('¿Eliminar este ticket?');\">
        <i class=\"bi bi-trash\"></i>
      </a>";

  if (!empty($t['ruta_evidencia'])) {
    $html .= "<button class='btn btn-info btn-sm' onclick=\"verEvidencia('{$t['ruta_evidencia']}')\">
                <i class='bi bi-image'></i>
              </button>";
  }

  $html .= "</td></tr>";
}


echo json_encode([
  'html' => $html,
  'total_paginas' => $totalPaginas
]);

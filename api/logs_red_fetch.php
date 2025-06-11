<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['empresa'])) {
  echo json_encode(["html" => "<tr><td colspan='7'>No autorizado</td></tr>", "paginacion" => ""]);
  exit;
}

require_once '../includes/db.php';

$empresa = $_SESSION['empresa'];
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$cliente = $_GET['cliente'] ?? '';
$resultado = $_GET['resultado'] ?? '';
$fecha = $_GET['fecha'] ?? '';
$cantidad = isset($_GET['cantidad']) ? intval($_GET['cantidad']) : 10;
$offset = ($pagina - 1) * $cantidad;

$condiciones = "WHERE c.empresa = ?";
$params = [$empresa];

if (!empty($cliente)) {
  $condiciones .= " AND c.nombre LIKE ?";
  $params[] = "%$cliente%";
}
if (!empty($resultado)) {
  $condiciones .= " AND l.resultado = ?";
  $params[] = $resultado;
}
if (!empty($fecha)) {
  $condiciones .= " AND DATE(l.fecha) = ?";
  $params[] = $fecha;
}

// Total para paginación
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM logs_acciones_red l JOIN clientes c ON l.id_cliente = c.id $condiciones");
$stmtTotal->execute($params);
$total = $stmtTotal->fetchColumn();
$totalPaginas = ceil($total / $cantidad);

// Obtener datos
$sql = "SELECT l.*, c.nombre AS nombre_cliente
        FROM logs_acciones_red l
        JOIN clientes c ON l.id_cliente = c.id
        $condiciones
        ORDER BY l.fecha DESC
        LIMIT $cantidad OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// HTML
$html = "";
if (count($logs) === 0) {
  $html = "<tr><td colspan='7'>No hay resultados.</td></tr>";
} else {
  foreach ($logs as $log) {
    $badge = $log['resultado'] === 'Éxito' ? 'success' : 'danger';
    $html .= "<tr>
      <td>{$log['id']}</td>
      <td>".htmlspecialchars($log['nombre_cliente'])."</td>
      <td>{$log['ip_mikrotik']}</td>
      <td>{$log['accion']}</td>
      <td><span class='badge bg-$badge'>{$log['resultado']}</span></td>
      <td class='text-start'>".nl2br(htmlspecialchars($log['mensaje']))."</td>
      <td>{$log['fecha']}</td>
    </tr>";
  }
}

// Paginación
$paginacion = "";
if ($totalPaginas > 1) {
  $paginacion .= '<nav><ul class="pagination justify-content-center">';
  for ($i = 1; $i <= $totalPaginas; $i++) {
    $active = $i == $pagina ? 'active' : '';
    $paginacion .= "<li class='page-item $active'><a class='page-link' href='#' onclick='cargarLogs($i)'>$i</a></li>";
  }
  $paginacion .= '</ul></nav>';
}

echo json_encode(["html" => $html, "paginacion" => $paginacion]);

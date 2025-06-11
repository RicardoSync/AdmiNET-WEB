<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['empresa'])) {
  echo json_encode(["html" => "<tr><td colspan='5'>No autorizado</td></tr>", "paginacion" => ""]);
  exit;
}

require_once '../includes/db_global.php';

$empresa = $_SESSION['empresa'];
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$usuario = $_GET['usuario'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$limit = 10;
$offset = ($pagina - 1) * $limit;

// Condiciones
$condiciones = "WHERE empresa = ?";
$params = [$empresa];

if (!empty($usuario)) {
  $condiciones .= " AND usuario LIKE ?";
  $params[] = "%$usuario%";
}
if (!empty($desde)) {
  $condiciones .= " AND fecha_hora >= ?";
  $params[] = "$desde 00:00:00";
}
if (!empty($hasta)) {
  $condiciones .= " AND fecha_hora <= ?";
  $params[] = "$hasta 23:59:59";
}

// Total de registros
$totalStmt = $conn->prepare("SELECT COUNT(*) FROM logs_sesiones $condiciones");
$totalStmt->execute($params);
$totalRegistros = $totalStmt->fetchColumn();
$totalPaginas = ceil($totalRegistros / $limit);

// Datos paginados
$logsStmt = $conn->prepare("SELECT * FROM logs_sesiones $condiciones ORDER BY fecha_hora DESC LIMIT $limit OFFSET $offset");
$logsStmt->execute($params);
$logs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);

// HTML de tabla
$html = "";
foreach ($logs as $log) {
  $html .= "<tr>";
  $html .= "<td>" . htmlspecialchars($log['usuario']) . "</td>";
  $html .= "<td>" . htmlspecialchars($log['ip_publica']) . "</td>";
  $html .= "<td>" . htmlspecialchars($log['navegador']) . "</td>";
  $html .= "<td>" . htmlspecialchars($log['fecha_hora']) . "</td>";
  $html .= "<td class='text-start small'>" . htmlspecialchars($log['user_agent']) . "</td>";
  $html .= "</tr>";
}
if (empty($html)) {
  $html = "<tr><td colspan='5'>No hay registros.</td></tr>";
}

// HTML de paginación
$paginacion = "";
for ($i = 1; $i <= $totalPaginas; $i++) {
  $active = ($i == $pagina) ? "active" : "";
  $paginacion .= "<button class='btn btn-sm btn-outline-primary m-1 $active' onclick='cargarLogs($i)'>$i</button>";
}

echo json_encode(["html" => $html, "paginacion" => $paginacion]);

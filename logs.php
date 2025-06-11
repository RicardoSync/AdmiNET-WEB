<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once 'includes/db.php';

// Filtros
$filtroCliente = $_GET['cliente'] ?? '';
$filtroResultado = $_GET['resultado'] ?? '';
$filtroFecha = $_GET['fecha'] ?? '';
$cantidad = isset($_GET['cantidad']) ? intval($_GET['cantidad']) : 30;
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina - 1) * $cantidad;

// Condiciones SQL
$params = [];
$sqlWhere = " WHERE 1=1";
if (!empty($filtroCliente)) {
  $sqlWhere .= " AND c.nombre LIKE ?";
  $params[] = "%$filtroCliente%";
}
if (!empty($filtroResultado)) {
  $sqlWhere .= " AND l.resultado = ?";
  $params[] = $filtroResultado;
}
if (!empty($filtroFecha)) {
  $sqlWhere .= " AND DATE(l.fecha) = ?";
  $params[] = $filtroFecha;
}

// Total de registros
$sqlCount = "SELECT COUNT(*) 
             FROM logs_acciones_red l
             JOIN clientes c ON l.id_cliente = c.id" . $sqlWhere;
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->execute($params);
$totalRegistros = $stmtCount->fetchColumn();
$totalPaginas = $cantidad > 0 ? ceil($totalRegistros / $cantidad) : 1;

// Consulta principal
$sql = "SELECT l.*, c.nombre AS nombre_cliente, m.nombre AS nombre_mikrotik 
        FROM logs_acciones_red l
        JOIN clientes c ON l.id_cliente = c.id
        LEFT JOIN credenciales_microtik m ON l.ip_mikrotik = m.ip"
        . $sqlWhere . 
        " ORDER BY l.fecha DESC";
if ($cantidad > 0) {
  $sql .= " LIMIT $cantidad OFFSET $offset";
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Logs de Red | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">Logs de Acciones de Red</h2>
    </div>

    <form method="GET" class="row g-2 mb-4" id="formFiltros">
      <div class="col-md-3">
        <input type="text" name="cliente" class="form-control" placeholder="Buscar por cliente" value="<?= htmlspecialchars($filtroCliente) ?>">
      </div>
      <div class="col-md-2">
        <select name="resultado" class="form-select">
          <option value="">-- Resultado --</option>
          <option value="Éxito" <?= $filtroResultado === 'Éxito' ? 'selected' : '' ?>>✅ Éxito</option>
          <option value="Error" <?= $filtroResultado === 'Error' ? 'selected' : '' ?>>❌ Error</option>
        </select>
      </div>
      <div class="col-md-2">
        <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($filtroFecha) ?>">
      </div>
      <div class="col-md-2">
        <select name="cantidad" id="selectCantidad" class="form-select">
          <option value="10" <?= $cantidad == 10 ? 'selected' : '' ?>>10</option>
          <option value="20" <?= $cantidad == 20 ? 'selected' : '' ?>>20</option>
          <option value="30" <?= $cantidad == 30 ? 'selected' : '' ?>>30</option>
          <option value="0" <?= $cantidad == 0 ? 'selected' : '' ?>>Todos (lento)</option>
        </select>
      </div>
      <div class="col-md-3 d-grid">
        <button class="btn btn-primary" type="submit"><i class="bi bi-filter"></i> Filtrar</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>MikroTik</th>
            <th>Acción</th>
            <th>Resultado</th>
            <th>Mensaje</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($logs) === 0): ?>
            <tr><td colspan="7" class="text-center">No hay registros que coincidan con los filtros.</td></tr>
          <?php else: ?>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td><?= $log['id'] ?></td>
                <td><?= htmlspecialchars($log['nombre_cliente']) ?></td>
                <td><?= htmlspecialchars($log['nombre_mikrotik'] ?? $log['ip_mikrotik']) ?></td>
                <td><?= $log['accion'] ?></td>
                <td>
                  <span class="badge bg-<?= $log['resultado'] === 'Éxito' ? 'success' : 'danger' ?>">
                    <?= $log['resultado'] ?>
                  </span>
                </td>
                <td class="text-start">
                  <?php if (strlen($log['mensaje']) > 60): ?>
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMensaje<?= $log['id'] ?>">
                      Ver mensaje
                    </button>

                    <div class="modal fade" id="modalMensaje<?= $log['id'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-scrollable">
                        <div class="modal-content">
                          <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title">Mensaje del Log #<?= $log['id'] ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <pre class="text-wrap"><?= htmlspecialchars($log['mensaje']) ?></pre>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php else: ?>
                    <?= nl2br(htmlspecialchars($log['mensaje'])) ?>
                  <?php endif; ?>
                </td>
                <td><?= $log['fecha'] ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($cantidad > 0 && $totalPaginas > 1): ?>
      <nav>
        <ul class="pagination justify-content-center">
          <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
              <a class="page-link" href="?cliente=<?= urlencode($filtroCliente) ?>&resultado=<?= urlencode($filtroResultado) ?>&fecha=<?= urlencode($filtroFecha) ?>&cantidad=<?= $cantidad ?>&pagina=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('selectCantidad').addEventListener('change', function () {
    if (this.value == '0') {
      const confirmar = confirm("⚠️ Estás a punto de cargar TODOS los registros. Esto puede demorar o colapsar el navegador. ¿Deseas continuar?");
      if (!confirmar) this.value = "30";
    }
  });
</script>
</body>
</html>

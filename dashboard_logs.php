<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once 'includes/db.php';

date_default_timezone_set('America/Mexico_City');

// Métricas generales
$totalLogs = $conn->query("SELECT COUNT(*) FROM logs_acciones_red")->fetchColumn();
$logsExito = $conn->query("SELECT COUNT(*) FROM logs_acciones_red WHERE resultado = 'Éxito'")->fetchColumn();
$logsError = $conn->query("SELECT COUNT(*) FROM logs_acciones_red WHERE resultado = 'Error'")->fetchColumn();
$logsHoy = $conn->query("SELECT COUNT(*) FROM logs_acciones_red WHERE DATE(fecha) = CURDATE()")->fetchColumn();

// Acciones más comunes
$accionesFrecuentes = $conn->query("
  SELECT accion, COUNT(*) AS total
  FROM logs_acciones_red
  GROUP BY accion
  ORDER BY total DESC
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Logs por día (últimos 7)
$logsUltimosDias = $conn->query("
  SELECT DATE(fecha) AS dia, COUNT(*) AS total
  FROM logs_acciones_red
  WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY dia
  ORDER BY dia ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Últimos 10 logs
$ultimosLogs = $conn->query("
  SELECT l.*, c.nombre AS nombre_cliente
  FROM logs_acciones_red l
  JOIN clientes c ON l.id_cliente = c.id
  ORDER BY l.fecha DESC
  LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para gráficas
$dias = array_column($logsUltimosDias, 'dia');
$valoresDias = array_column($logsUltimosDias, 'total');

$acciones = array_column($accionesFrecuentes, 'accion');
$valoresAcciones = array_column($accionesFrecuentes, 'total');
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Logs | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 p-4">
    <h2 class="text-primary fw-bold">Dashboard de Logs</h2>
    <h5 class="mb-4 text-muted">Estadísticas de registros de acciones de red</h5>

    <div class="row g-4">
      <div class="col-md-3">
        <div class="metric-box">
          <i class="bi bi-list-check"></i>
          <strong>Total Logs</strong>
          <span><?= $totalLogs ?></span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="metric-box" style="border-top-color:#198754;">
          <i class="bi bi-check-circle-fill"></i>
          <strong>Logs Éxito</strong>
          <span><?= $logsExito ?></span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="metric-box" style="border-top-color:#dc3545;">
          <i class="bi bi-x-circle-fill"></i>
          <strong>Logs Error</strong>
          <span><?= $logsError ?></span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="metric-box" style="border-top-color:#0dcaf0;">
          <i class="bi bi-calendar-day"></i>
          <strong>Hoy</strong>
          <span><?= $logsHoy ?></span>
        </div>
      </div>
    </div>

    <div class="section-title">🔁 Acciones más frecuentes</div>
    <div class="chart-container">
      <canvas id="graficoAcciones"></canvas>
    </div>

    <div class="section-title">📅 Logs por Día (Últimos 7 días)</div>
    <div class="chart-container">
      <canvas id="graficoDias"></canvas>
    </div>

    <div class="section-title">🧾 Últimos 10 Logs</div>
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Acción</th>
            <th>Resultado</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimosLogs as $log): ?>
            <tr>
              <td><?= $log['id'] ?></td>
              <td><?= htmlspecialchars($log['nombre_cliente']) ?></td>
              <td><?= htmlspecialchars($log['accion']) ?></td>
              <td>
                <span class="badge bg-<?= $log['resultado'] === 'Éxito' ? 'success' : 'danger' ?>">
                  <?= $log['resultado'] ?>
                </span>
              </td>
              <td><?= $log['fecha'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-3 text-end">
      <a href="logs.php" class="btn btn-outline-primary">
        Ver todos los logs <i class="bi bi-arrow-right-circle-fill"></i>
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico Acciones
new Chart(document.getElementById('graficoAcciones'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($acciones) ?>,
    datasets: [{
      label: 'Cantidad',
      data: <?= json_encode($valoresAcciones) ?>,
      backgroundColor: '#0d6efd'
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 }
      }
    }
  }
});

// Gráfico por Día
new Chart(document.getElementById('graficoDias'), {
  type: 'line',
  data: {
    labels: <?= json_encode($dias) ?>,
    datasets: [{
      label: 'Logs por día',
      data: <?= json_encode($valoresDias) ?>,
      borderColor: '#6610f2',
      backgroundColor: 'rgba(102,16,242,0.1)',
      fill: true,
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 }
      }
    }
  }
});
</script>
</body>
</html>

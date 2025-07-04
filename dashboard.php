<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once 'includes/db.php';
require_once 'includes/SSHHelper.php';
require_once 'includes/estilo_festivo.php'; // ✅ Agrega esto

$estiloFestivo = obtenerEstiloFestivo(); // ✅ Ya estará definida

$usuario = $_SESSION['usuario'];

// Conteos de métricas con condiciones explícitas y exactas
date_default_timezone_set('America/Mexico_City');

$totalClientes = $conn->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$clientesActivos = $conn->query("SELECT COUNT(*) FROM clientes WHERE estado = 'Activo'")->fetchColumn();
$clientesSuspendidos = $conn->query("SELECT COUNT(*) FROM clientes WHERE estado = 'Suspendido'")->fetchColumn();
$clientesBloqueados = $conn->query("SELECT COUNT(*) FROM clientes WHERE estado = 'Bloqueado'")->fetchColumn();
$clientesRetirados = $conn->query("SELECT COUNT(*) FROM clientes WHERE estado = 'Cancelado'")->fetchColumn();

$facturasPendientes = $conn->query("
  SELECT COUNT(*) FROM (
    SELECT c.id
    FROM clientes c
    LEFT JOIN (
      SELECT id_cliente, MAX(fecha_pago) AS fecha_pago, MAX(proximo_pago) AS proximo_pago
      FROM pagos
      GROUP BY id_cliente
    ) p ON c.id = p.id_cliente
    WHERE c.estado IN ('Activo','Suspendido') 
      AND p.proximo_pago IS NOT NULL 
      AND DATE(p.proximo_pago) < CURDATE()
  ) AS sub
")->fetchColumn();


$cobranzaDia = $conn->query("SELECT IFNULL(SUM(monto), 0) FROM pagos WHERE DATE(fecha_pago) = CURDATE()")->fetchColumn();
$ticketsPendientes = $conn->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Pendiente'")->fetchColumn();
$instalacionesPendientes = $conn->query("SELECT COUNT(*) FROM instalaciones WHERE estado = 'Pendiente'")->fetchColumn();
$planes = $conn->query("SELECT COUNT(*) FROM paquetes")->fetchColumn();
$usuariosInactivos = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$equipos = $conn->query("SELECT COUNT(*) FROM equipos")->fetchColumn();
$zonas = $conn->query("SELECT COUNT(*) FROM antenasap")->fetchColumn();


// ← Agrega aquí la nueva consulta
$consumoQuery = $conn->query("
  SELECT 
    DATE_FORMAT(DATE_SUB(fecha, INTERVAL MINUTE(fecha) % 5 MINUTE), '%H:%i') AS periodo,
    SUM(rx) AS total_rx,
    SUM(tx) AS total_tx
  FROM consumo_internet
  WHERE DATE(fecha) = CURDATE()
  GROUP BY periodo
  ORDER BY periodo ASC
")->fetchAll(PDO::FETCH_ASSOC);

$horas = array_column($consumoQuery, 'periodo');
$datosRx = array_map(fn($r) => round($r['total_rx'] / 1024, 2), $consumoQuery); // KB
$datosTx = array_map(fn($r) => round($r['total_tx'] / 1024, 2), $consumoQuery);



$horas = array_column($consumoQuery, 'hora');
$datosRx = array_map(fn($r) => round($r['total_rx'] / (1024*1024), 2), $consumoQuery);
$datosTx = array_map(fn($r) => round($r['total_tx'] / (1024*1024), 2), $consumoQuery);


$ingresosPorMes = [];
$egresosPorMes = [];
$meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
for ($i = 1; $i <= 12; $i++) {
  $stmt = $conn->prepare("SELECT IFNULL(SUM(monto), 0) FROM pagos WHERE MONTH(fecha_pago) = ? AND YEAR(fecha_pago) = YEAR(CURDATE())");
  $stmt->execute([$i]);
  $ingresosPorMes[] = (float)$stmt->fetchColumn();

  $stmt2 = $conn->prepare("SELECT IFNULL(SUM(monto), 0) FROM egresos WHERE MONTH(fecha_egreso) = ? AND YEAR(fecha_egreso) = YEAR(CURDATE())");
  $stmt2->execute([$i]);
  $egresosPorMes[] = (float)$stmt2->fetchColumn();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    body {
      background: linear-gradient(to bottom right, #e9f1ff, #ffffff);
      font-family: 'Segoe UI', sans-serif;
      color: #333;
    }
    .metric-box {
      background: #fff;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
      text-align: center;
      transition: 0.2s ease-in-out;
      border-top: 6px solid #0d6efd;
    }
    .metric-box span[data-count]::after {
      content: attr(data-count);
    }
    .section-title {
      font-size: 1.4rem;
      font-weight: 700;
      color: #0d6efd;
      margin-top: 40px;
      border-bottom: 3px solid #0d6efd;
      padding-bottom: 5px;
    }
    .chart-container {
      background: #fff;
      border-radius: 16px;
      padding: 20px;
      margin-top: 20px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
    }
    canvas {
      max-height: 320px;
    }

    .metric-box {
    background: #fff;
    color: #000;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    text-align: center;
    border-top: 6px solid #0d6efd;
    }
    .metric-box strong, .metric-box span {
    color: #000 !important;
    }

  </style>

  <?php if ($estiloFestivo): ?>
  <link rel="stylesheet" href="assets/css/festivos/<?= $estiloFestivo ?>.css">
  <?php endif; ?>

</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>
  <div id="page-content-wrapper" class="w-100 p-4">
    <h2 class="text-primary fw-bold">Dashboard</h2>
    <h5 class="mb-4 text-muted">Bienvenido, <?= htmlspecialchars($usuario) ?> 👋</h5>

    <div class="row g-4">
    <div class="col-md-3">
        <div class="metric-box">
        <i class="bi bi-people-fill"></i>
        <strong>Clientes</strong>
        <span><?= $totalClientes ?></span>
        </div>
    </div>
    <div class="col-md-3">
      <button type="button" class="metric-box w-100" style="border-top-color:#dc3545;" data-bs-toggle="modal" data-bs-target="#modalPagosPendientes">
        <i class="bi bi-receipt"></i>
        <strong>Pagos Pendientes</strong>
        <span><?= $facturasPendientes ?></span>
      </button>
</div>

    <div class="col-md-3">
        <div class="metric-box" style="border-top-color:#198754;">
        <i class="bi bi-cash-coin"></i>
        <strong>Cobranza del Día</strong>
        <span><?= number_format($cobranzaDia, 2) ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-box" style="border-top-color:#0dcaf0;">
        <i class="bi bi-life-preserver"></i>
        <strong>Tickets</strong>
        <span><?= $ticketsPendientes ?></span>
        </div>
    </div>
    </div>

    <div class="row g-4 mt-2">
    <div class="col-md-3">
        <div class="metric-box" style="border-top-color:#ffc107;">
        <i class="bi bi-wifi"></i>
        <strong>Instalaciones</strong>
        <span><?= $instalacionesPendientes ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-box">
        <i class="bi bi-router"></i>
        <strong>Planes</strong>
        <span><?= $planes ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-box">
        <i class="bi bi-box"></i>
        <strong>Equipos</strong>
        <span><?= $equipos ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-box">
        <i class="bi bi-person-dash"></i>
        <strong>Usuarios Internos</strong>
        <span><?= $usuariosInactivos ?></span>
        </div>
    </div>
    </div>


    <div class="section-title">📈 Ingresos por Mes</div>
    <div class="chart-container">
      <canvas id="graficoIngresos"></canvas>
    </div>

    <div class="section-title">👥 Clientes Activos vs Suspendidos vs Bloqueados</div>
    <div class="chart-container">
      <canvas id="graficoClientes"></canvas>
    </div>

    <div class="section-title">⚙️ Monitor de Recursos</div>
    <div class="chart-container">
      <div class="row text-center">
        <div class="col-md-3"><strong>CPU:</strong> <span id="cpu">Cargando...</span></div>
        <div class="col-md-3"><strong>RAM:</strong> <span id="ram">Cargando...</span></div>
        <div class="col-md-3"><strong>Disco:</strong> <span id="disk">Cargando...</span></div>
        <div class="col-md-3"><strong>Red:</strong> <span id="network">Cargando...</span></div>
      </div>
    </div>

    <div class="section-title">🌐 Consumo de Internet (Hoy)</div>
    <div class="chart-container">
      <canvas id="graficoInternet"></canvas>
    </div>


    <div class="section-title">📊 Órdenes de Instalación</div>
    <div class="chart-container">
      <canvas id="graficaInstalaciones" height="200"></canvas>
    </div>

  <div class="section-title">📜 Últimos Logs de MikroTik</div>
  <div class="chart-container">
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-sm">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>
            <th>Router</th>
            <th>Tipo</th>
            <th>Mensaje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logsMikrotik as $log): ?>
          <tr>
            <td><?= htmlspecialchars($log['fecha_hora']) ?></td>
            <td><?= htmlspecialchars($log['router'] ?? 'N/D') ?></td>
            <td><span class="badge bg-secondary"><?= htmlspecialchars($log['tipo']) ?></span></td>
            <td><?= htmlspecialchars($log['mensaje']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
  </div>


  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Contadores animados
// Contadores animados (corregido para evitar duplicaciones)


// Monitor recursos
function actualizarMonitor() {
  fetch('api/server_stats.php')
    .then(res => res.json())
    .then(data => {
      document.getElementById('cpu').textContent = data.cpu + '%';
      document.getElementById('ram').textContent = data.ram + '%';
      document.getElementById('disk').textContent = data.disk;
      document.getElementById('network').textContent = data.network;
    });
}
setInterval(actualizarMonitor, 5000);
actualizarMonitor();

// Ingresos
new Chart(document.getElementById('graficoIngresos'), {
  type: 'line',
  data: {
    labels: <?= json_encode($meses) ?>,
    datasets: [
      {
        label: 'Ingresos',
        data: <?= json_encode($ingresosPorMes) ?>,
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 4
      },
      {
        label: 'Egresos',
        data: <?= json_encode($egresosPorMes) ?>,
        borderColor: '#dc3545',
        backgroundColor: 'rgba(220,53,69,0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 4
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'top'
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

// Donut
new Chart(document.getElementById('graficoClientes'), {
  type: 'doughnut',
  data: {
    labels: ['Activos', 'Suspendidos', 'Bloqueados'],
    datasets: [{
      data: [<?= $clientesActivos ?>, <?= $clientesSuspendidos ?>, <?= $clientesBloqueados ?>],
      backgroundColor: ['#198754', '#ffc107', '#dc3545']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'bottom'
      }
    }
  }
});

// Gráfica de instalaciones tipo Wispro
new Chart(document.getElementById('graficaInstalaciones'), {
  type: 'line',
  data: {
    labels: ['Febrero - 2021', 'Marzo - 2021', 'Abril - 2021', 'Mayo - 2021', 'Junio - 2021', 'Julio - 2021', 'Agosto - 2021', 'Septiembre - 2021', 'Octubre - 2021', 'Noviembre - 2021', 'Diciembre - 2021'],
    datasets: [
      {
        label: 'Creadas',
        data: [0, 50, 90, 120, 115, 110, 160, 170, 190, 240, 210],
        borderColor: '#0dcaf0',
        backgroundColor: 'rgba(13, 202, 240, 0.2)',
        fill: true,
        tension: 0.4
      },
      {
        label: 'Aprobadas',
        data: [0, 40, 80, 115, 113, 109, 150, 165, 185, 230, 200],
        borderColor: '#6c757d',
        backgroundColor: 'rgba(108,117,125, 0.2)',
        fill: true,
        tension: 0.4
      },
      {
        label: 'Rechazadas',
        data: [0, 10, 10, 5, 2, 1, 10, 10, 15, 30, 20],
        borderColor: '#dc3545',
        backgroundColor: 'rgba(220, 53, 69, 0.2)',
        fill: true,
        tension: 0.4
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'top' }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

new Chart(document.getElementById('graficoInternet'), {
  type: 'line',
  data: {
    labels: <?= json_encode($horas) ?>,
    datasets: [
      {
        label: 'RX (MB)',
        data: <?= json_encode($datosRx) ?>,
        borderColor: 'green',
        backgroundColor: 'rgba(0,128,0,0.1)',
        fill: true
      },
      {
        label: 'TX (MB)',
        data: <?= json_encode($datosTx) ?>,
        borderColor: 'blue',
        backgroundColor: 'rgba(0,0,255,0.1)',
        fill: true
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'top' }
    },
    scales: {
      y: {
        beginAtZero: true,
        title: { display: true, text: 'MB por hora' }
      }
    }
  }
});
</script>

<?php if ($estiloFestivo === 'navidad'): ?>
<script>
function cerrarRegalo() {
  document.getElementById('popupNavidad').style.display = 'none';
}
</script>
<?php endif; ?>


<?php if ($estiloFestivo === 'navidad'): ?>
<div id="popupNavidad">
  <div class="regalo">
    <div class="caja"></div>
    <div class="tapa"></div>
    <div class="mensaje">
      <h4>🎄 ¡Feliz Navidad <?= htmlspecialchars($usuario) ?>!</h4>
      <p>Te deseamos lo mejor estas fiestas.<br>– Equipo AdmiNET</p>
      <button onclick="cerrarRegalo()">Cerrar</button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($estiloFestivo === 'halloween'): ?>
<div id="popupHalloween">
  <div class="popup-halloween">
    <h4>🎃 ¡Feliz Halloween <?= htmlspecialchars($usuario) ?>!</h4>
    <p>Que el código y los bugs estén de tu lado...</p>
    <button onclick="document.getElementById('popupHalloween').remove()">Cerrar</button>
  </div>
</div>
<?php endif; ?>

<?php if ($estiloFestivo === 'pascua'): ?>
<div id="popupPascua">
  <div class="popup-halloween" style="background:#fff3f8; color:#d12b78;">
    <h4>🐣 ¡Felices Pascuas <?= htmlspecialchars($usuario) ?>!</h4>
    <p>Que esta temporada te traiga paz, código limpio y buen café ☕</p>
    <button onclick="document.getElementById('popupPascua').remove()">Cerrar</button>
  </div>
</div>
<?php endif; ?>

<?php if ($estiloFestivo === 'mexico'): ?>
<div id="popupMexico">
  <div class="popup-halloween" style="background:#fff; color:#155724;">
    <h4>🇲🇽 ¡Viva México <?= htmlspecialchars($usuario) ?>!</h4>
    <p>Que viva la libertad, la independencia... y tu internet sin cortes 🎉</p>
    <button onclick="document.getElementById('popupMexico').remove()">Cerrar</button>
  </div>
</div>
<?php endif; ?>

<?php if ($estiloFestivo === 'cumplehades'): ?>
  <?php include 'epic_hades.php'; ?>
<?php endif; ?>

<?php
$clientesDeudores = $conn->query("
  SELECT c.id, c.nombre, c.dia_corte, p.fecha_pago, p.proximo_pago
  FROM clientes c
  LEFT JOIN (
    SELECT id_cliente, MAX(fecha_pago) AS fecha_pago, MAX(proximo_pago) AS proximo_pago
    FROM pagos
    GROUP BY id_cliente
  ) p ON c.id = p.id_cliente
  WHERE c.estado IN ('Activo','Suspendido') AND p.proximo_pago IS NOT NULL AND DATE(p.proximo_pago) < CURDATE()
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal -->
<div class="modal fade" id="modalPagosPendientes" tabindex="-1" aria-labelledby="modalPagosPendientesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalPagosPendientesLabel">Clientes con Pagos Pendientes</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?php if (count($clientesDeudores) > 0): ?>
        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Último Pago</th>
                <th>Próximo Pago</th>
                <th>Día de Corte</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($clientesDeudores as $i => $cliente): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                <td><?= $cliente['fecha_pago'] ?? 'N/A' ?></td>
                <td class="text-danger"><?= $cliente['proximo_pago'] ?></td>
                <td><?= $cliente['dia_corte'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <p class="text-center text-muted">No hay clientes con pagos vencidos.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>

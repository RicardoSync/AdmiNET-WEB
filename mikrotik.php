<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once __DIR__ . '/includes/db.php';
$stmt = $conn->query("SELECT * FROM credenciales_microtik ORDER BY id DESC");
$mikrotiks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>MikroTik | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
      @media screen and (max-width: 768px) {
        table.table thead {
          display: none;
        }

        table.table tbody tr {
          display: block;
          margin-bottom: 1rem;
          border: 1px solid #ccc;
          border-radius: 10px;
          padding: 0.5rem;
          background-color: white;
        }

        table.table tbody td {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 0.25rem 0;
          border: none;
          border-bottom: 1px solid #eee;
        }

        table.table tbody td:last-child {
          border-bottom: none;
        }

        table.table tbody td::before {
          content: attr(data-label);
          font-weight: bold;
          color: #555;
        }
      }

      @media screen and (max-width: 768px) {
      .btn {
        font-size: 0.85rem;
        padding: 0.3rem 0.6rem;
      }
    }

      @media screen and (max-width: 768px) {
      #modalMonitor .modal-content {
        border-radius: 20px;
        padding: 1rem;
      }

      #modalMonitor .modal-body {
        padding: 0.5rem 1rem;
      }

      #selectInterfaceModal {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
      }

      #graficoModal {
        width: 100% !important;
        height: 200px !important;
      }

      #modalMonitor .modal-title {
        font-size: 1.2rem;
      }

      #modalMonitor .btn-close {
        margin-top: -5px;
      }
    }

  </style>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light text-dark p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary"><i class="bi bi-hdd-network-fill me-2"></i> MikroTik Registrados</h2>
      <a href="mikrotik_nuevo.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Agregar MikroTik
      </a>
    </div>

    <?php if (isset($_SESSION['msg_success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['msg_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      <?php unset($_SESSION['msg_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['msg_error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['msg_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      <?php unset($_SESSION['msg_error']); ?>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>IP</th>
            <th>Usuario</th>
            <th>Puerto</th>
            <th>Acciones</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mikrotiks as $m): ?>
            <tr>
              <td data-label="ID"><?= $m['id'] ?></td>
              <td data-label="Nombre"><?= htmlspecialchars($m['nombre']) ?></td>
              <td data-label="IP"><?= $m['ip'] ?></td>
              <td data-label="Usuario"><?= $m['username'] ?></td>
              <td data-label="Puerto"><?= $m['port'] ?></td>
              <td data-label="Acciones">
                <div class="d-flex flex-wrap gap-1 justify-content-center">
                  <a href="mikrotik_editar.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <a href="api/mikrotik_eliminar.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este MikroTik?');">
                    <i class="bi bi-trash"></i>
                  </a>
                  <a href="#" class="btn btn-sm btn-info" onclick="abrirModalLeases(<?= $m['id'] ?>)">
                    <i class="bi bi-list-columns-reverse"></i> DHCP
                  </a>
                  <button class="btn btn-sm btn-secondary" onclick="abrirMonitor(<?= $m['id'] ?>)">
                    <i class="bi bi-activity"></i> Tráfico
                  </button>
                  <button class="btn btn-sm btn-dark" onclick="aplicarFirewall(<?= $m['id'] ?>)">
                    <i class="bi bi-shield-lock"></i> Firewall
                  </button>
                </div>
              </td>
              <td data-label="Estado">
                <span class="estado-ssh badge rounded-pill px-3 py-2 text-white fw-semibold" data-id="<?= $m['id'] ?>" style="background: linear-gradient(90deg, #3a3a3a, #2a2a2a); font-size: 0.9rem;">
                  <i class="bi bi-hourglass-split me-1"></i> Verificando...
                </span>
              </td>
            </tr>
          <?php endforeach; ?>

        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalLeases" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="bi bi-router"></i> Leases DHCP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered text-center">
            <thead class="table-dark">
              <tr>
                <th>IP</th>
                <th>MAC</th>
                <th>Host-Name</th>
                <th>Status</th>
                <th>Última vez visto</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaLeasesBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="modalMonitor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
    <div class="modal-content bg-dark text-white shadow-lg border-0 rounded-4 p-2 p-md-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-info">
          <i class="bi bi-activity me-2"></i> Monitor de Tráfico
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <label for="selectInterfaceModal" class="form-label">Interfaz a monitorear</label>
        <select id="selectInterfaceModal" class="form-select form-select-sm mb-3 bg-secondary text-white border-0">
          <option value="">-- Interfaces disponibles --</option>
        </select>

        <div class="bg-black rounded p-2 p-md-3">
          <div style="height: 220px;">
            <canvas id="graficoModal"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('.estado-ssh').forEach(span => {
    const id = span.dataset.id;
    fetch('api/verificar_ssh_mikrotik.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id
    })
    .then(res => res.json())
    .then(data => {
      if (data.estado === 'ok') {
        span.innerHTML = '<i class="bi bi-check-circle me-1"></i> En línea';
        span.className = 'estado-ssh badge rounded-pill bg-success-subtle text-success fw-semibold px-3 py-2';
      } else {
        span.innerHTML = '<i class="bi bi-x-circle me-1"></i> Sin conexión';
        span.className = 'estado-ssh badge rounded-pill bg-danger-subtle text-danger fw-semibold px-3 py-2';
      }
    })
    .catch(() => {
      span.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> Error';
      span.className = 'estado-ssh badge rounded-pill bg-warning-subtle text-warning fw-semibold px-3 py-2';
    });
  });
});

</script>

<script>
function abrirModalLeases(id_mikrotik) {
  const modal = new bootstrap.Modal(document.getElementById('modalLeases'));
  document.getElementById('tablaLeasesBody').innerHTML = '<tr><td colspan="6">Cargando...</td></tr>';

  fetch('api/leer_dhcp_leases.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id_mikrotik=' + id_mikrotik
  })
  .then(res => res.json())
  .then(data => {
    const tbody = document.getElementById('tablaLeasesBody');
    tbody.innerHTML = '';
    data.forEach(d => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${d.ip}</td>
        <td>${d.mac}</td>
        <td>${d.hostname}</td>
        <td>${d.status}</td>
        <td>${d.last_seen}</td>
        <td>
          <button class="btn btn-warning btn-sm" onclick="makeStatic('${d.mac}', ${id_mikrotik})">
            <i class="bi bi-pin-angle-fill"></i>
          </button>
          <button class="btn btn-success btn-sm" onclick="registrarCliente('${d.ip}', '${d.hostname}', ${id_mikrotik})">
            <i class="bi bi-person-plus-fill"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
    modal.show();
  });
}

function makeStatic(mac, id_mikrotik) {
  fetch('api/make_static.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `mac=${mac}&id_mikrotik=${id_mikrotik}`
  })
  .then(res => res.text())
  .then(alert);
}

function registrarCliente(ip, hostname, id_mikrotik) {
  if (!confirm(`¿Registrar ${hostname} con IP ${ip}?`)) return;
  fetch('api/registrar_cliente_dhcp.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `ip=${ip}&nombre=${encodeURIComponent(hostname)}&id_mikrotik=${id_mikrotik}`
  })
  .then(res => res.text())
  .then(alert);
}
</script>

<script>
Chart.register({
  id: 'ultimoPuntoResaltado',
  afterDatasetsDraw(chart) {
    const ctx = chart.ctx;
    chart.data.datasets.forEach((dataset, i) => {
      const meta = chart.getDatasetMeta(i);
      const lastPoint = meta.data[meta.data.length - 1];
      if (lastPoint) {
        ctx.save();
        ctx.beginPath();
        ctx.arc(lastPoint.x, lastPoint.y, 6, 0, 2 * Math.PI);
        ctx.fillStyle = dataset.borderColor;
        ctx.shadowColor = dataset.borderColor;
        ctx.shadowBlur = 6;
        ctx.fill();
        ctx.restore();
      }
    });
  }
});

let graficoModal = new Chart(document.getElementById('graficoModal'), {
  type: 'line',
  data: {
    labels: [],
    datasets: [
      {
        label: 'RX (Mbps)',
        data: [],
        borderColor: 'rgba(0,200,0,1)',
        backgroundColor: 'rgba(0,200,0,0.3)',
        fill: true,
        tension: 0.5,
        pointRadius: 2,
        borderWidth: 2
      },
      {
        label: 'TX (Mbps)',
        data: [],
        borderColor: 'rgba(0,100,255,1)',
        backgroundColor: 'rgba(0,100,255,0.3)',
        fill: true,
        tension: 0.5,
        pointRadius: 2,
        borderWidth: 2
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
      duration: 500,
      easing: 'easeOutQuart'
    },
    plugins: {
      legend: {
        position: 'top',
        labels: { color: '#fff' }
      }
    },
    scales: {
      x: {
        ticks: {
          color: '#ccc',
          maxRotation: 45,
          minRotation: 45
        }
      },
      y: {
        beginAtZero: true,
        ticks: {
          color: '#ccc',
          callback: valor => valor.toFixed(1) + ' Mbps'
        },
        title: {
          display: true,
          text: 'Mbps',
          color: '#ccc'
        }
      }
    },
    interaction: {
      intersect: false,
      mode: 'index'
    }
  },
  plugins: ['ultimoPuntoResaltado']
});

let intervaloMonitor = null;

function abrirMonitor(id) {
  const modal = new bootstrap.Modal(document.getElementById('modalMonitor'));
  const select = document.getElementById('selectInterfaceModal');
  select.innerHTML = '<option value="">-- Cargando --</option>';

  fetch(`api/interfaces_mikrotik.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
      select.innerHTML = '<option value="">-- Interfaces disponibles --</option>';
      data.forEach(nombre => {
        const opt = document.createElement('option');
        opt.value = nombre;
        opt.textContent = nombre;
        select.appendChild(opt);
      });
    });

  select.onchange = () => {
    const interfaz = select.value;
    graficoModal.data.labels = [];
    graficoModal.data.datasets[0].data = [];
    graficoModal.data.datasets[1].data = [];
    graficoModal.update();
    if (intervaloMonitor) clearInterval(intervaloMonitor);
    if (!interfaz) return;

    intervaloMonitor = setInterval(() => {
      fetch(`api/traer_trafico.php?id=${id}&interfaz=${encodeURIComponent(interfaz)}`)
        .then(res => res.json())
        .then(data => {
          const hora = new Date().toLocaleTimeString();
          const rx = parseFloat(data.rx_mbps || 0);
          const tx = parseFloat(data.tx_mbps || 0);

          const len = graficoModal.data.labels.length;
          if (rx === 0 && tx === 0 &&
              graficoModal.data.datasets[0].data[len - 1] === 0 &&
              graficoModal.data.datasets[1].data[len - 1] === 0) {
            return;
          }

          graficoModal.data.labels.push(hora);
          graficoModal.data.datasets[0].data.push(rx);
          graficoModal.data.datasets[1].data.push(tx);

          if (graficoModal.data.labels.length > 20) {
            graficoModal.data.labels.shift();
            graficoModal.data.datasets[0].data.shift();
            graficoModal.data.datasets[1].data.shift();
          }

          graficoModal.update();
        });
    }, 4000);
  };

  modal.show();

  document.getElementById('modalMonitor').addEventListener('hidden.bs.modal', () => {
    if (intervaloMonitor) {
      clearInterval(intervaloMonitor);
      intervaloMonitor = null;
    }
  });
}
</script>

<script>
function aplicarFirewall(id) {
  if (!confirm("¿Deseas aplicar las reglas de firewall a este MikroTik?")) return;

  fetch('api/aplicar_firewall.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id=' + id
  })
  .then(res => res.text())
  .then(resp => alert(resp))
  .catch(err => alert("Error de red o conexión: " + err));
}
</script>

</body>
</html>

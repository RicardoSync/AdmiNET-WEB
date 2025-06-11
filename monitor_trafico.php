<?php
session_start();
if (!isset($_GET['id'])) {
  die("ID de MikroTik no especificado.");
}
$id_mikrotik = intval($_GET['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Monitor de Tráfico</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
      padding: 20px;
      font-family: 'Segoe UI', sans-serif;
    }
    .container {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<div class="container">
  <h3 class="mb-4 text-primary">🌐 Monitor de Tráfico en Tiempo Real</h3>

  <label for="selectInterface" class="form-label">Selecciona una interfaz</label>
  <select id="selectInterface" class="form-select mb-4" style="max-width: 300px;">
    <option value="">-- Interfaces disponibles --</option>
  </select>

  <canvas id="grafico" height="100"></canvas>
</div>

<script>
const idMikrotik = <?= $id_mikrotik ?>;

let grafico = new Chart(document.getElementById('grafico'), {
  type: 'line',
  data: {
    labels: [],
    datasets: [
      {
        label: 'RX (Mbps)',
        data: [],
        borderColor: 'green',
        fill: false
      },
      {
        label: 'TX (Mbps)',
        data: [],
        borderColor: 'blue',
        fill: false
      }
    ]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true,
        title: { display: true, text: 'Mbps' }
      }
    }
  }
});

// Cargar interfaces desde MikroTik
fetch('api/interfaces_mikrotik.php?id=' + idMikrotik)
  .then(res => res.json())
  .then(interfaces => {
    const select = document.getElementById('selectInterface');
    interfaces.forEach(nombre => {
      const opt = document.createElement('option');
      opt.value = nombre;
      opt.textContent = nombre;
      select.appendChild(opt);
    });
  });

let intervalo = null;

document.getElementById('selectInterface').addEventListener('change', function () {
  const interfaz = this.value;

  // Limpiar gráfico e intervalo anterior
  grafico.data.labels = [];
  grafico.data.datasets[0].data = [];
  grafico.data.datasets[1].data = [];
  grafico.update();
  if (intervalo) clearInterval(intervalo);
  if (!interfaz) return;

  // Iniciar monitoreo
  intervalo = setInterval(() => {
    fetch(`api/traer_trafico.php?id=${idMikrotik}&interfaz=${encodeURIComponent(interfaz)}`)
      .then(res => res.json())
      .then(data => {
        const hora = new Date().toLocaleTimeString();
        grafico.data.labels.push(hora);
        grafico.data.datasets[0].data.push(data.rx_mbps);
        grafico.data.datasets[1].data.push(data.tx_mbps);

        if (grafico.data.labels.length > 20) {
          grafico.data.labels.shift();
          grafico.data.datasets[0].data.shift();
          grafico.data.datasets[1].data.shift();
        }

        grafico.update();
      });
  }, 4000);
});
</script>

</body>
</html>
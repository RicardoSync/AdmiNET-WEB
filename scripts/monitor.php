<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once '../includes/db.php';
require_once '../includes/estilo_festivo.php';
$estiloFestivo = obtenerEstiloFestivo();
$basedatos = $conn->query("SELECT DATABASE()")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Monitor de Clientes | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <?php if ($estiloFestivo): ?>
    <link rel="stylesheet" href="../assets/css/festivos/<?= $estiloFestivo ?>.css">
  <?php endif; ?>

  <!-- Importar reproductor .lottie -->
  <script type="module" src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs"></script>

  <style>
    body {
      background: linear-gradient(to bottom right, #e9f1ff, #ffffff);
      font-family: 'Segoe UI', sans-serif;
      color: #333;
    }

    #loadingOverlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(255, 255, 255, 0.95);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      flex-direction: column;
    }

    .metric-box {
      background: #fff;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
      text-align: center;
      border-top: 6px solid #0d6efd;
      transition: transform 0.2s ease;
      position: relative;
    }

    .metric-box:hover {
      transform: scale(1.02);
    }

    .metric-box.activo::after {
      content: "";
      position: absolute;
      top: 50%;
      left: 50%;
      width: 120px;
      height: 120px;
      background: rgba(25, 135, 84, 0.25);
      border-radius: 50%;
      transform: translate(-50%, -50%);
      animation: pulse 2s infinite;
      z-index: 0;
    }

    .metric-box.inactivo::after {
      display: none;
    }

    @keyframes pulse {
      0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.7;
      }
      100% {
        transform: translate(-50%, -50%) scale(1.5);
        opacity: 0;
      }
    }

    .antena-img {
      width: 40px;
      height: 40px;
      margin-bottom: 12px;
      position: relative;
      z-index: 1;
    }

    .metric-box h5, .metric-box .fw-bold, .metric-box span {
      position: relative;
      z-index: 1;
    }

    .metric-box span.estado-activo {
      color: #198754;
      font-weight: bold;
    }

    .metric-box span.estado-inactivo {
      color: #dc3545;
      font-weight: bold;
    }

    .titulo-seccion {
      font-size: 1.5rem;
      font-weight: bold;
      color: #0d6efd;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<div id="loadingOverlay">
  <dotlottie-player
    src="https://lottie.host/4e2a6cfe-cea5-40b0-9150-c923c7a18ffb/AFyo2dBiIY.lottie"
    background="transparent"
    speed="1"
    style="width: 200px; height: 200px;"
    loop
    autoplay>
  </dotlottie-player>
  <p class="mt-3 fw-bold text-primary fs-5">Revisando clientes en sistema<br>Por favor espera</p>
</div>

<div class="d-flex" id="wrapper">
  <?php include '../includes/sidebar.php'; ?>
  <div id="page-content-wrapper" class="w-100 p-4">
    <div class="titulo-seccion">📡 Monitor de Clientes</div>
    <div class="row g-4" id="contenedorClientes">
      <!-- Se carga dinámicamente -->
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    fetch("monitor_datos.php")
      .then(res => res.json())
      .then(data => {
        const contenedor = document.getElementById('contenedorClientes');
        data.forEach(cliente => {
          const card = `
            <div class="col-md-3">
              <div class="metric-box ${cliente.estado === 'Activo' ? 'activo' : 'inactivo'}"
                style="border-top-color: ${cliente.estado === 'Activo' ? '#198754' : '#dc3545'};">
                
                <img src="../assets/img/${cliente.estado === 'Activo' ? 'antena_activa.png' : 'antena_inactiva.png'}" alt="Antena" class="antena-img">
                
                <h5 class="mb-1">${cliente.ip}</h5>
                <div class="fw-bold mb-1 text-primary">${cliente.nombre}</div>

                <span class="${cliente.estado === 'Activo' ? 'estado-activo' : 'estado-inactivo'}">
                  ${cliente.estado}
                </span>

                <div class="d-flex justify-content-center gap-2 mt-3">
                  <button class="btn btn-outline-danger btn-sm btn-suspender" data-id="${cliente.id}">
                    <i class="bi bi-slash-circle"></i> Suspender
                  </button>
                  <button class="btn btn-outline-success btn-sm btn-activar" data-id="${cliente.id}">
                    <i class="bi bi-check-circle"></i> Activar
                  </button>
                </div>
              </div>
            </div>
          `;
          contenedor.innerHTML += card;
        });
        document.getElementById("loadingOverlay").style.display = "none";
      })
      .catch(err => {
        document.getElementById("contenedorClientes").innerHTML = `<div class="alert alert-danger">Error: ${err}</div>`;
        document.getElementById("loadingOverlay").style.display = "none";
      });
  });
</script>

</body>
</html>

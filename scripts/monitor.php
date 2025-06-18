<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once '../includes/db.php'; 
require_once '../includes/estilo_festivo.php';
$estiloFestivo = obtenerEstiloFestivo();
$python = __DIR__ . "/.venv/bin/python3";
$script = __DIR__ . "/monitor_antenas.py";
$salida = shell_exec("$python $script 2>&1");

$datos = json_decode($salida, true);
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
<div class="d-flex" id="wrapper">
  <?php include '../includes/sidebar.php'; ?>
  <div id="page-content-wrapper" class="w-100 p-4">
    <div class="titulo-seccion">📡 Monitor de Clientes</div>

    <div class="row g-4">
      <?php if ($datos): ?>
        <?php foreach ($datos as $fila): ?>
          <div class="col-md-3">
            <div class="metric-box <?= $fila['estado'] === 'Activo' ? 'activo' : 'inactivo' ?>" style="border-top-color: <?= $fila['estado'] === 'Activo' ? '#198754' : '#dc3545' ?>;">
              <img src="../assets/img/<?= $fila['estado'] === 'Activo' ? 'antena_activa.png' : 'antena_inactiva.png' ?>" alt="Antena" class="antena-img">
              
              <h5 class="mb-1"><?= htmlspecialchars($fila['ip']) ?></h5>
              <div class="fw-bold mb-1 text-primary"><?= htmlspecialchars($fila['nombre']) ?></div>
              
              <span class="<?= $fila['estado'] === 'Activo' ? 'estado-activo' : 'estado-inactivo' ?>">
                <?= $fila['estado'] ?>
              </span>

              <div class="d-flex justify-content-center gap-2 mt-3">
                <button class="btn btn-outline-danger btn-sm btn-suspender" data-id="<?= $fila['id'] ?>">
                  <i class="bi bi-slash-circle"></i> Suspender
                </button>
                <button class="btn btn-outline-success btn-sm btn-activar" data-id="<?= $fila['id'] ?>">
                  <i class="bi bi-check-circle"></i> Activar
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-warning">
            No se pudo obtener información de las antenas.<br>
            <pre><?= htmlspecialchars($salida) ?></pre>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('click', e => {
  const suspender = e.target.closest('.btn-suspender');
  const activar = e.target.closest('.btn-activar');

  if (suspender) {
    const id = suspender.dataset.id;
    fetch("/ded/api/suspender_cliente.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id=" + id
    })
    .then(res => res.text())
    .then(msg => {
      alert(msg);
      location.reload();
    });
  }

  if (activar) {
    const id = activar.dataset.id;
    fetch("/ded/api/activar_cliente.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id=" + id
    })
    .then(res => res.text())
    .then(msg => {
      alert(msg);
      location.reload();
    });
  }
});
</script>
</body>
</html>

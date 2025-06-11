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


<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once 'includes/db.php';

$zonas = $conn->query("SELECT idantenasAp, nombre FROM antenasap")->fetchAll(PDO::FETCH_ASSOC);
$mikrotiks = $conn->query("SELECT id, nombre FROM credenciales_microtik")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Clientes | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>


<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <!-- Main Content -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">Clientes</h2>
      <a href="cliente_nuevo.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Agregar Cliente
      </a>
    </div>

    <div class="mb-3">
      <input type="text" id="busquedaCliente" class="form-control" placeholder="Buscar cliente por nombre o IP">
    </div>

    <div class="row g-2 mb-3">
    <div class="col-md-2">
      <select id="filtroOrden" class="form-select">
        <option value="">Orden</option>
        <option value="asc">A-Z</option>
        <option value="desc">Z-A</option>
      </select>
    </div>
    <div class="col-md-2">
    <select id="filtroTipoConexion" class="form-select">
      <option value="">Tipo Conexión</option>
      <option value="1">Antena</option>
      <option value="0">Fibra</option>
    </select>
    </div>
    <div class="col-md-2">
      <select id="filtroDiaCorte" class="form-select">
        <option value="">Día Corte</option>
        <?php for ($i = 1; $i <= 31; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select id="filtroMikrotik" class="form-select">
        <option value="">MikroTik</option>
        <?php foreach ($mikrotiks as $mk): ?>
          <option value="<?= $mk['id'] ?>"><?= $mk['nombre'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select id="filtroZona" class="form-select">
        <option value="">Zona</option>
        <?php foreach ($zonas as $z): ?>
          <option value="<?= $z['idantenasAp'] ?>"><?= $z['nombre'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

<div class="row g-2 mb-3">
  <div class="col-md-2">
    <select id="cantidadPorPagina" class="form-select">
      <option value="10">10 por página</option>
      <option value="20">20 por página</option>
      <option value="50">50 por página</option>
    </select>
  </div>
</div>


    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaClientes">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>IP</th>
            <th>Estado</th>
            <th>Día Corte</th>
            <th>Tipo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Cargado por JS -->
        </tbody>
      </table>
      <div id="paginacionClientes" class="mt-3 text-center"></div>
    </div>
  </div>
</div>

<!-- Modal de Acciones de Red -->
<div class="modal fade" id="modalAccionesCliente" tabindex="-1" aria-labelledby="modalAccionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
    <div class="modal-content bg-dark text-white shadow-lg rounded-4 p-2 p-md-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title text-info fw-bold" id="modalAccionesLabel">
          <i class="bi bi-wrench-adjustable-circle me-2"></i> Acciones de Red para <span id="modalClienteNombre"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center pt-3">
        <input type="hidden" id="modalClienteId">
        <input type="hidden" id="modalClienteIP">

        <div class="d-grid gap-2">
          <button class="btn btn-danger" id="btnSuspender">
            <i class="bi bi-slash-circle"></i> Suspender Cliente
          </button>
          <button class="btn btn-success" id="btnActivar">
            <i class="bi bi-check-circle"></i> Activar Cliente
          </button>
          <button class="btn btn-info" id="btnMonitor">
            <i class="bi bi-bar-chart"></i> Monitor de Tráfico
          </button>
          <button class="btn btn-primary" id="btnSubir">
            <i class="bi bi-cloud-upload"></i> Subir a MikroTik
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para monitor de tráfico -->
<!-- Modal para monitor de tráfico -->
<div class="modal fade" id="modalMonitor" tabindex="-1" aria-labelledby="modalMonitorLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down">
    <div class="modal-content bg-dark text-white border-0 rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold text-info d-flex align-items-center">
          <i class="bi bi-activity me-2 fs-4"></i> Monitor de Tráfico
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body py-3">
        <div class="bg-black rounded-4 p-3 position-relative overflow-hidden shadow">
          <canvas id="graficaTráfico" style="max-height: 300px; width: 100%;"></canvas>
        </div>
        <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.9rem;">
          Los valores se actualizan en tiempo real. Escala dinámica en Kbps/Mbps.
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detalles Cliente -->
<div class="modal fade" id="modalDetallesCliente" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
    <div class="modal-content bg-dark text-white rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title text-info"><i class="bi bi-person-lines-fill me-2"></i> Detalles del Cliente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="contenidoDetallesCliente">
          <p class="text-center text-muted">Cargando información...</p>
        </div>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/clientes.js"></script>
</body>
</html>

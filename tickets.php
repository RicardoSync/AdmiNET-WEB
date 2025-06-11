<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once __DIR__ . '/includes/db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Tickets | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/ticket.css">

</head>
<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <!-- Contenido Principal -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary"><i class="bi bi-ticket-detailed-fill me-2"></i> Tickets</h2>
      <a href="ticket_nuevo.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Nuevo Ticket
      </a>
    </div>

  <div class="row g-2 mb-3">
    <div class="col-md-3">
      <input type="text" id="busquedaTicket" class="form-control" placeholder="Buscar por cliente o descripción">
    </div>
    <div class="col-md-2">
      <select id="filtroEstado" class="form-select">
        <option value="">Estado</option>
        <option value="Pendiente">Pendiente</option>
        <option value="En proceso">En proceso</option>
        <option value="Resuelto">Resuelto</option>
        <option value="Cerrado">Cerrado</option>
      </select>
    </div>
    <div class="col-md-2">
      <select id="filtroCategoria" class="form-select">
        <option value="">Categoría</option>
        <!-- Opciones dinámicas desde JS -->
      </select>
    </div>
    <div class="col-md-2">
      <select id="filtroResponsable" class="form-select">
        <option value="">Responsable</option>
        <!-- Opciones dinámicas desde JS -->
      </select>
    </div>
    <div class="col-md-2">
      <select id="cantidadPorPagina" class="form-select">
        <option value="10">10 por página</option>
        <option value="20">20 por página</option>
        <option value="50">50 por página</option>
      </select>
    </div>
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

    <?php
    $stmt = $conn->query("SELECT t.*, c.nombre AS cliente, u.nombre AS responsable
                          FROM tickets t
                          LEFT JOIN clientes c ON t.id_cliente = c.id
                          LEFT JOIN usuarios u ON t.id_responsable = u.id
                          ORDER BY t.fecha_creacion DESC");
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaTickets">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Creado</th>
            <th>Responsable</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Llenado por JS -->
        </tbody>
      </table>
      <div id="paginacionTickets" class="mt-3 text-center"></div>
    </div>

  </div>
</div>

<!-- Modal para ver evidencia -->
<div class="modal fade" id="modalEvidencia" tabindex="-1" aria-labelledby="modalEvidenciaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEvidenciaLabel">📸 Evidencia del Ticket</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <img id="imagenEvidencia" src="" alt="Evidencia del ticket" class="img-fluid rounded shadow" style="max-height: 500px;">
      </div>
    </div>
  </div>
</div>


<script src="assets/js/tickets.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

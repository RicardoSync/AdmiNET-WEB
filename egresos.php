<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Egresos | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/egresos.css">
</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">Egresos</h2>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarEgreso">
        <i class="bi bi-plus-circle"></i> Agregar Egreso
      </button>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <select id="filtroMetodo" class="form-select">
          <option value="">Método de Pago</option>
          <option value="Efectivo">Efectivo</option>
          <option value="Transferencia">Transferencia</option>
          <option value="Tarjeta">Tarjeta</option>
        </select>
      </div>
      <div class="col-md-4">
        <input type="date" id="filtroFecha" class="form-control" placeholder="Filtrar por fecha">
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaEgresos">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Descripción</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th>Método</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Cargado por JS -->
        </tbody>
      </table>
      <div id="paginacionEgresos" class="text-center mt-3"></div>
    </div>
  </div>
</div>

<!-- Modal Agregar Egreso -->
<div class="modal fade" id="modalAgregarEgreso" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title">Agregar Egreso</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAgregarEgreso">
          <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <input type="text" class="form-control" id="descripcion" required>
          </div>
          <div class="mb-3">
            <label for="monto" class="form-label">Monto</label>
            <input type="number" class="form-control" id="monto" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="metodo" class="form-label">Método de Pago</label>
            <select class="form-select" id="metodo" required>
              <option value="">Seleccione</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Transferencia">Transferencia</option>
              <option value="Tarjeta">Tarjeta</option>
            </select>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/egresos.js"></script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Equipos | AdmiNET</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/equipos.css">

  <style>
    @media (max-width: 768px) {
      .p-4 {
        padding: 1rem !important;
      }

      .btn {
        font-size: 0.9rem;
        padding: 0.4rem 0.75rem;
      }

      .form-select,
      .form-control {
        font-size: 14px;
      }

      .table-responsive {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h2 class="text-primary m-0"><i class="bi bi-hdd-network me-2"></i>Equipos</h2>
      <a href="equipo_nuevo.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Agregar Equipo
      </a>
    </div>

    <div class="mb-3">
      <input type="text" id="busquedaEquipo" class="form-control" placeholder="Buscar por nombre, MAC, cliente...">
    </div>

    <div class="row g-2 mb-3">
      <div class="col-6 col-md-3 col-lg-2">
        <select id="filtroTipo" class="form-select">
          <option value="">Tipo</option>
          <option value="Router">Router</option>
          <option value="Antena">Antena</option>
          <option value="Switch">Switch</option>
        </select>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <select id="filtroMarca" class="form-select">
          <option value="">Cargando marcas...</option>
        </select>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <select id="filtroEstado" class="form-select">
          <option value="">Estado</option>
          <option value="Propio">Propio</option>
          <option value="Rentado">Rentado</option>
          <option value="Vendido">Vendido</option>
          <option value="Almacenado">Almacenado</option>
        </select>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <select id="cantidadPorPagina" class="form-select">
          <option value="10">10 por página</option>
          <option value="20">20 por página</option>
          <option value="50">50 por página</option>
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaEquipos">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Marca</th>
            <th>MAC</th>
            <th>Cliente</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Cargado por JS -->
        </tbody>
      </table>
      <div id="paginacionEquipos" class="text-center mt-3"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/equipos.js"></script>
</body>
</html>

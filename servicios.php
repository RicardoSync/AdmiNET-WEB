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
  <title>Servicios | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/servicios.css">

</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">Servicios Plataforma</h2>
      <a href="servicio_nuevo.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Agregar Servicio
      </a>
    </div>

    <div class="mb-3">
      <input type="text" id="busquedaServicio" class="form-control" placeholder="Buscar por nombre o descripción...">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaServicios">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Cargado por JS -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/servicios.js"></script>
</body>
</html>

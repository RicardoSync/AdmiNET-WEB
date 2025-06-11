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
  <title>Clientes Suspendidos | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">

  <style>
    @media screen and (max-width: 768px) {
      #tablaClientesSuspendidos thead {
        display: none;
      }

      #tablaClientesSuspendidos tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #ddd;
        border-radius: 0.75rem;
        background-color: white;
        padding: 0.75rem;
      }

      #tablaClientesSuspendidos tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        border-bottom: 1px solid #eee;
        padding: 0.4rem 0;
        font-size: 0.95rem;
      }

      #tablaClientesSuspendidos tbody td:last-child {
        border-bottom: none;
      }

      #tablaClientesSuspendidos tbody td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #555;
      }
    }
   
  </style>
</head>
<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <!-- Main Content -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-warning">Clientes Suspendidos</h2>
    </div>

    <div class="mb-3">
      <input type="text" id="busquedaCliente" class="form-control" placeholder="Buscar cliente por nombre o IP">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaClientesSuspendidos">
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
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/clientes_suspendidos.js"></script>
</body>
</html>

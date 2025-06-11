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
  <title>Paquetes | AdmiNET</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/paquetes.css">

  <style>
    @media (max-width: 768px) {
      .p-4 {
        padding: 1rem !important;
      }

      .btn {
        font-size: 0.9rem;
        padding: 0.4rem 0.6rem;
      }

      .table-responsive {
        font-size: 14px;
      }

      #busquedaPaquete {
        font-size: 14px;
      }
    }
  </style>
</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 p-4 bg-light">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h2 class="text-primary m-0"><i class="bi bi-box-seam me-2"></i>Paquetes</h2>
      <a href="paquete_nuevo.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Agregar Paquete
      </a>
    </div>

    <div class="mb-3">
      <input type="text" id="busquedaPaquete" class="form-control" placeholder="Buscar por nombre o velocidad...">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaPaquetes">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Velocidad</th>
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
<script src="assets/js/paquetes.js"></script>
</body>
</html>

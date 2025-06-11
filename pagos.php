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
  <title>Pagos | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">

  <style>

  @media screen and (max-width: 768px) {
    #tablaPagos thead {
      display: none;
    }

    #tablaPagos tbody tr {
      display: block;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 10px;
      background-color: white;
      padding: 0.75rem;
    }

    #tablaPagos tbody td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: none;
      border-bottom: 1px solid #eee;
      padding: 0.4rem 0;
      font-size: 0.95rem;
    }

    #tablaPagos tbody td:last-child {
      border-bottom: none;
    }

    #tablaPagos tbody td::before {
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
      <h2 class="text-primary">Pagos</h2>
      <a href="pago_nuevo.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Registrar Pago
      </a>
    </div>



    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-4">
        <input type="text" id="busquedaPago" class="form-control" placeholder="Buscar por nombre o IP">
      </div>
    </div>

    <div class="row g-2 mb-3">
      <div class="col-md-2">
        <select id="filtroDia" class="form-select">
          <option value="">Día</option>
          <?php for ($i = 1; $i <= 31; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select id="filtroMes" class="form-select">
          <option value="">Mes</option>
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>"><?= $m ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select id="filtroAnio" class="form-select">
          <option value="">Año</option>
          <?php for ($a = date('Y'); $a >= 2023; $a--): ?>
            <option value="<?= $a ?>"><?= $a ?></option>
          <?php endfor; ?>
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

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaPagos">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Fecha Pago</th>
            <th>Método</th>
            <th>Próximo Pago</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Cargado por JS -->
        </tbody>
      </table>
      <div id="paginacionPagos" class="mt-3 text-center"></div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/pagos.js"></script>
</body>
</html>

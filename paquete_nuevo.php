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
  <title>Registrar Paquete</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .card-header {
      border-radius: 12px 12px 0 0;
      font-weight: bold;
    }

    .btn-primary {
      background-color: #0d6efd;
      border: none;
    }

    .btn-secondary {
      background-color: #6c757d;
      border: none;
    }

    @media (max-width: 768px) {
      .p-4 {
        padding: 1rem !important;
      }

      .btn {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
      }
    }
  </style>
</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">
  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Contenido principal -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="card mx-auto" style="max-width: 700px;">
      <div class="card-header bg-primary text-white d-flex align-items-center">
        <i class="bi bi-speedometer2 me-2"></i>
        <h5 class="mb-0">Registrar Paquete</h5>
      </div>

      <div class="card-body">
        <form action="api/paquete_add.php" method="POST">
          <div class="mb-3">
            <label>Nombre del Paquete</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Velocidad de Subida (ej: 5M)</label>
              <input type="text" name="vel_subida" class="form-control" placeholder="ej: 5M" required>
            </div>
            <div class="col-md-6">
              <label>Velocidad de Bajada (ej: 10M)</label>
              <input type="text" name="vel_bajada" class="form-control" placeholder="ej: 10M" required>
            </div>
          </div>

          <div class="mb-4">
            <label>Precio mensual ($ MXN)</label>
            <input type="number" step="0.01" name="precio" class="form-control" required>
          </div>

          <div class="d-flex justify-content-end">
            <a href="paquetes.php" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Paquete</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

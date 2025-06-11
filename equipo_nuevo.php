<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once 'includes/db.php';

// Obtener lista de clientes
$stmt = $conn->prepare("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrar Nuevo Equipo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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

      .form-control, .form-select {
        font-size: 14px;
      }
    }
  </style>
</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">
  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Contenido -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="card mx-auto" style="max-width: 800px;">
      <div class="card-header bg-primary text-white d-flex align-items-center">
        <i class="bi bi-hdd-network me-2"></i>
        <h5 class="mb-0">Registrar Nuevo Equipo</h5>
      </div>

      <div class="card-body">
        <form action="api/equipo_add.php" method="POST">
          <div class="row mb-3">
            <div class="col-md-6">
              <label>Nombre del Equipo</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Tipo</label>
              <select name="tipo" class="form-control" required>
                <option value="Router">Router</option>
                <option value="Antena">Antena</option>
                <option value="ONU">ONU</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label>Marca</label>
              <input type="text" name="marca" class="form-control">
            </div>
            <div class="col-md-4">
              <label>Modelo</label>
              <input type="text" name="modelo" class="form-control">
            </div>
            <div class="col-md-4">
              <label>MAC</label>
              <input type="text" name="mac" class="form-control">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Serial</label>
              <input type="text" name="serial" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Estado</label>
              <select name="estado" class="form-control" required>
                <option value="Propio">Propio</option>
                <option value="Rentado">Rentado</option>
                <option value="Vendido">Vendido</option>
                <option value="Almacenado">Almacenado</option>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label>Asignar a Cliente (opcional)</label>
            <select name="id_cliente" class="form-control">
              <option value="">-- Sin asignar --</option>
              <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="d-flex justify-content-end">
            <a href="equipos.php" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Equipo</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div><!-- /.wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

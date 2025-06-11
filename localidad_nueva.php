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
  <title>Registrar Nueva Localidad</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    .form-box {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      padding: 30px;
      margin-top: 20px;
    }

    .form-box h3 {
      background-color: #0d6efd;
      color: #fff;
      padding: 10px 20px;
      border-radius: 12px 12px 0 0;
      margin: -30px -30px 30px -30px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    @media (max-width: 768px) {
      .form-box {
        padding: 20px 15px;
        margin-top: 10px;
      }

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
<body>

<div class="d-flex" id="wrapper">
  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Contenido principal -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="form-box">
      <h3><i class="bi bi-plus-circle"></i> Registrar Nueva Localidad</h3>

      <form action="api/localidad_add.php" method="POST">

        <div class="mb-3">
          <label>Nombre de la Localidad</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Tipo de Localidad</label>
            <input type="text" name="modelo" class="form-control" placeholder="Ej: Urbana, Rural...">
          </div>
          <div class="col-md-6 mb-3">
            <label>Responsable</label>
            <input type="text" name="usuario" class="form-control" placeholder="Ej: Ricardo Escobedo">
          </div>
        </div>

        <div class="mb-4">
          <label>Notas (opcional)</label>
          <textarea name="ip" class="form-control" placeholder="Ej: Cobertura inestable, zona en expansión..." rows="2"></textarea>
        </div>

        <div class="d-flex justify-content-end">
          <a href="localidades.php" class="btn btn-secondary me-2">Cancelar</a>
          <button type="submit" class="btn btn-primary">Guardar Localidad</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

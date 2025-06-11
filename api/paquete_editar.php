<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once __DIR__ . '/../includes/db.php';

if (!isset($_GET['id'])) {
  $_SESSION['msg_error'] = "ID de paquete no especificado.";
  header("Location: paquetes.php");
  exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM paquetes WHERE id = ?");
$stmt->execute([$id]);
$paquete = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paquete) {
  $_SESSION['msg_error'] = "Paquete no encontrado.";
  header("Location: paquetes.php");
  exit;
}

[$subida, $bajada] = explode('/', $paquete['velocidad']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Paquete</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="/ded/assets/css/dashboard.css">
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
  <?php include '../includes/sidebar.php'; ?>

  <!-- Contenido -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="card mx-auto" style="max-width: 700px;">
      <div class="card-header bg-primary text-white d-flex align-items-center">
        <i class="bi bi-pencil me-2"></i>
        <h5 class="mb-0">Editar Paquete</h5>
      </div>

      <div class="card-body">
        <form action="/ded/api/paquete_update.php" method="POST">
          <input type="hidden" name="id" value="<?= $paquete['id'] ?>">

          <div class="mb-3">
            <label>Nombre del Paquete</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($paquete['nombre']) ?>" required>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Velocidad de Subida</label>
              <input type="text" name="vel_subida" class="form-control" value="<?= htmlspecialchars($subida) ?>" required>
            </div>
            <div class="col-md-6">
              <label>Velocidad de Bajada</label>
              <input type="text" name="vel_bajada" class="form-control" value="<?= htmlspecialchars($bajada) ?>" required>
            </div>
          </div>

          <div class="mb-4">
            <label>Precio mensual ($ MXN)</label>
            <input type="number" step="0.01" name="precio" class="form-control" value="<?= $paquete['precio'] ?>" required>
          </div>

          <div class="d-flex justify-content-end">
            <a href="/ded/paquetes.php" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div><!-- /.wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

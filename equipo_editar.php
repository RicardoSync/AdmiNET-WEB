<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once 'includes/db.php';

if (!isset($_GET['id'])) {
  $_SESSION['msg_error'] = "ID de equipo no especificado.";
  header("Location: equipos.php");
  exit;
}

$id = intval($_GET['id']);

// Obtener datos del equipo
$stmt = $conn->prepare("SELECT * FROM equipos WHERE id = ?");
$stmt->execute([$id]);
$equipo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equipo) {
  $_SESSION['msg_error'] = "Equipo no encontrado.";
  header("Location: equipos.php");
  exit;
}

// Obtener lista de clientes
$stmtClientes = $conn->prepare("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Equipo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <i class="bi bi-pencil-square me-2"></i>
        <h5 class="mb-0">Editar Equipo</h5>
      </div>

      <div class="card-body">
        <form action="api/equipo_update.php" method="POST">
          <input type="hidden" name="id" value="<?= $equipo['id'] ?>">

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Nombre del Equipo</label>
              <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($equipo['nombre']) ?>" required>
            </div>
            <div class="col-md-6">
              <label>Tipo</label>
              <select name="tipo" class="form-control" required>
                <?php foreach (['Router','Antena','ONU','Otro'] as $tipo): ?>
                  <option value="<?= $tipo ?>" <?= $equipo['tipo'] === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label>Marca</label>
              <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($equipo['marca']) ?>">
            </div>
            <div class="col-md-4">
              <label>Modelo</label>
              <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($equipo['modelo']) ?>">
            </div>
            <div class="col-md-4">
              <label>MAC</label>
              <input type="text" name="mac" class="form-control" value="<?= htmlspecialchars($equipo['mac']) ?>">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label>Serial</label>
              <input type="text" name="serial" class="form-control" value="<?= htmlspecialchars($equipo['serial']) ?>">
            </div>
            <div class="col-md-6">
              <label>Estado</label>
              <select name="estado" class="form-control" required>
                <?php foreach (['Propio','Rentado','Vendido','Almacenado'] as $estado): ?>
                  <option value="<?= $estado ?>" <?= $equipo['estado'] === $estado ? 'selected' : '' ?>><?= $estado ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label>Asignar a Cliente (opcional)</label>
            <select name="id_cliente" class="form-control">
              <option value="">-- Sin asignar --</option>
              <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id'] ?>" <?= $equipo['id_cliente'] == $cliente['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cliente['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="d-flex justify-content-end">
            <a href="equipos.php" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar Equipo</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div><!-- /.wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

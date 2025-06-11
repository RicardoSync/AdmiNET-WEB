<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once 'includes/db.php';

$mikrotiks = $conn->query("SELECT id, nombre FROM credenciales_microtik")->fetchAll(PDO::FETCH_ASSOC);
$queues = $conn->query("
  SELECT qp.id, qp.nombre, qp.subred, qp.max_limit, cm.nombre AS mikrotik_nombre
  FROM queue_parent qp
  JOIN credenciales_microtik cm ON qp.id_mikrotik = cm.id
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Queue Parent | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/parent.css">

</head>
<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <!-- Main Content -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-info">Queue Parents</h2>
    </div>

    <!-- Formulario de registro -->
    <form action="api/queue_parent_add.php" method="POST" class="row g-3 bg-white p-4 rounded shadow-sm mb-4">
      <div class="col-md-3">
        <label class="form-label text-dark">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label text-dark">Subred</label>
        <input type="text" name="subred" class="form-control" placeholder="192.168.10.0/24" required>
      </div>
      <div class="col-md-2">
        <label class="form-label text-dark">Max Limit</label>
        <input type="text" name="max_limit" class="form-control" placeholder="10M/10M" required>
      </div>
      <div class="col-md-3">
        <label class="form-label text-dark">MikroTik</label>
        <select name="id_mikrotik" class="form-select" required>
          <option value="">Seleccione...</option>
          <?php foreach ($mikrotiks as $mk): ?>
            <option value="<?= $mk['id'] ?>"><?= $mk['nombre'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-no aun 1 d-flex align-items-end">
        <button type="submit" class="btn btn-success w-100">
          <i class="bi bi-plus-circle"></i> Agregar Parent
        </button>
      </div>
    </form>

    <!-- Tabla -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Subred</th>
            <th>Max Limit</th>
            <th>MikroTik</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($queues as $q): ?>
            <tr>
              <td data-label="ID"><?= $q['id'] ?></td>
              <td data-label="Nombre"><?= $q['nombre'] ?></td>
              <td data-label="Subred"><?= $q['subred'] ?></td>
              <td data-label="Max Limit"><?= $q['max_limit'] ?></td>
              <td data-label="MikroTik"><?= $q['mikrotik_nombre'] ?></td>
              <td data-label="Acciones">
                <a href="api/queue_parent_edit.php?id=<?= $q['id'] ?>" class="btn btn-primary btn-sm">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="api/queue_parent_delete.php?id=<?= $q['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar también en MikroTik?')">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>

      </table>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

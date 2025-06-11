<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';


if (!isset($_GET['id'])) {
  echo "<p style='color:red'>ID de queue parent no especificado.</p>";
  exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM queue_parent WHERE id = ?");
$stmt->execute([$id]);
$qp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$qp) {
  echo "<p style='color:red'>Queue parent no encontrado.</p>";
  exit;
}

$mikrotiks = $conn->query("SELECT id, nombre FROM credenciales_microtik")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Queue Parent</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/editar_cliente.css">
</head>
<body class="bg-light">

<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-0">
      <?php include '../includes/sidebar.php'; ?>
    </div>

    <!-- Contenido -->
    <div class="col-md-9 col-lg-10 px-4 py-4">
      <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0">Editar Queue Parent</h4>
          <a href="../queue_parent.php" class="btn btn-light btn-sm">Volver</a>
        </div>
        <div class="card-body p-4">
          <form action="queue_parent_update.php" method="POST">
            <input type="hidden" name="id" value="<?= $qp['id'] ?>">

            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" value="<?= $qp['nombre'] ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Subred</label>
              <input type="text" name="subred" class="form-control" value="<?= $qp['subred'] ?>" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Límite de Velocidad</label>
              <input type="text" name="max_limit" class="form-control" value="<?= $qp['max_limit'] ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">MikroTik</label>
              <select name="id_mikrotik" class="form-select">
                <?php foreach ($mikrotiks as $mk): ?>
                  <option value="<?= $mk['id'] ?>" <?= $qp['id_mikrotik'] == $mk['id'] ? 'selected' : '' ?>><?= $mk['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="text-end">
              <button type="submit" class="btn btn-primary">Guardar Cambios</button>
              <a href="../queue_parent.php" class="btn btn-secondary">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

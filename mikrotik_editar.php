<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once __DIR__ . '/includes/db.php';

if (!isset($_GET['id'])) {
  $_SESSION['msg_error'] = "ID no especificado.";
  header("Location: mikrotik.php");
  exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
$stmt->execute([$id]);
$mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mikrotik) {
  $_SESSION['msg_error'] = "MikroTik no encontrado.";
  header("Location: mikrotik.php");
  exit;
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $ip = $_POST['ip'];
  $username = $_POST['username'];
  $password = $_POST['password'];
  $port = $_POST['port'];

  if (empty($nombre) || empty($username)) {
    $_SESSION['msg_error'] = "Los campos nombre y usuario son obligatorios.";
    header("Location: mikrotik_editar.php?id=" . $id);
    exit;
  }

  $stmt = $conn->prepare("UPDATE credenciales_microtik SET nombre = ?, ip = ?, username = ?, password = ?, port = ? WHERE id = ?");
  $stmt->execute([$nombre, $ip, $username, $password, $port, $id]);

  $_SESSION['msg_success'] = "MikroTik actualizado correctamente.";
  header("Location: mikrotik.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar MikroTik | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light text-dark p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary"><i class="bi bi-pencil-square me-2"></i>Editar MikroTik</h2>
      <a href="mikrotik.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>

    <?php if (isset($_SESSION['msg_error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['msg_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      <?php unset($_SESSION['msg_error']); ?>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre *</label>
        <input type="text" name="nombre" id="nombre" class="form-control text-dark" value="<?= htmlspecialchars($mikrotik['nombre']) ?>" required>
      </div>

      <div class="mb-3">
        <label for="ip" class="form-label">IP</label>
        <input type="text" name="ip" id="ip" class="form-control text-dark" value="<?= htmlspecialchars($mikrotik['ip']) ?>">
      </div>

      <div class="mb-3">
        <label for="username" class="form-label">Usuario *</label>
        <input type="text" name="username" id="username" class="form-control text-dark" value="<?= htmlspecialchars($mikrotik['username']) ?>" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="text" name="password" id="password" class="form-control text-dark" value="<?= htmlspecialchars($mikrotik['password']) ?>">
      </div>

      <div class="mb-3">
        <label for="port" class="form-label">Puerto</label>
        <input type="text" name="port" id="port" class="form-control text-dark" value="<?= htmlspecialchars($mikrotik['port']) ?>">
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> Guardar Cambios
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

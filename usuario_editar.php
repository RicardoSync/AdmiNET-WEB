<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_GET['id'])) {
  echo "<p style='color:red'>ID de usuario no especificado.</p>";
  exit;
}

require_once 'includes/db.php';

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  echo "<p style='color:red'>Usuario no encontrado.</p>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">

  <style>
    /* Solución específica para este módulo */
    #page-content-wrapper,
    #page-content-wrapper label,
    #page-content-wrapper input,
    #page-content-wrapper select,
    #page-content-wrapper textarea,
    #page-content-wrapper table,
    #page-content-wrapper td,
    #page-content-wrapper th {
      color: #000 !important;
    }
  </style>



</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-warning">Editar Usuario</h2>
    </div>

    <form action="api/usuario_update.php" method="POST">
      <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

      <div class="mb-3">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Usuario</label>
        <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Nueva Contraseña (opcional)</label>
        <input type="password" name="password" class="form-control">
      </div>

      <div class="mb-3">
        <label>Rol</label>
        <select name="rol" class="form-control" required>
          <option value="0" <?= $usuario['rol'] == 0 ? 'selected' : '' ?>>Administrador</option>
          <option value="1" <?= $usuario['rol'] == 1 ? 'selected' : '' ?>>Técnico</option>
          <option value="2" <?= $usuario['rol'] == 2 ? 'selected' : '' ?>>Cliente</option>
        </select>
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</html>

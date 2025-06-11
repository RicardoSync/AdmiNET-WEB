<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once 'includes/db_global.php';

$stmt = $conn->prepare("SELECT * FROM usuarios_empresas WHERE usuario = ?");
$stmt->execute([$_SESSION['usuario']]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos) {
  echo "<p style='color:red'>Usuario no encontrado.</p>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi Cuenta | AdmiNET</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    .card {
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
    }
    .card-header {
      font-weight: bold;
    }
    @media (max-width: 768px) {
      .card {
        margin: 10px;
      }
      .p-4 {
        padding: 1rem !important;
      }
    }
  </style>
</head>
<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary"><i class="bi bi-person-circle me-2"></i> Mi Cuenta</h2>
    </div>

    <div class="card mx-auto" style="max-width: 600px;">
      <div class="card-header bg-info text-white">
        <i class="bi bi-person-lock"></i> Editar Credenciales de Acceso
      </div>
      <div class="card-body">
        <form action="api/mi_cuenta_update.php" method="POST">
          <div class="mb-3">
            <label>Usuario (correo o nombre)</label>
            <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($datos['usuario']) ?>" required>
          </div>
          <div class="mb-3">
            <label>Nueva Contraseña (opcional)</label>
            <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success">
              <i class="bi bi-save"></i> Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

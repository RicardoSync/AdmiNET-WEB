<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once __DIR__ . '/includes/db.php';

// Verificar si ya hay un registro
$stmtCheck = $conn->query("SELECT COUNT(*) FROM datosEmpresa");
$total = $stmtCheck->fetchColumn();

// Si no existe ningún registro, creamos uno por defecto
if ($total == 0) {
  $conn->query("INSERT INTO datosEmpresa (nombreWisp, rfc, cp, telefono, direccion) VALUES ('', '', '', '', '')");
}

$stmt = $conn->query("SELECT * FROM datosEmpresa LIMIT 1");
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Datos de la Empresa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css"> <!-- Usa el mismo CSS de tickets.php -->
  <style>
    .form-box {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
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

    @media (max-width: 767.98px) {
      .form-box {
        padding: 20px 15px;
      }
    }

      body {
    color: #000 !important;
  }
  
  </style>
</head>
<body>
<div class="d-flex" id="wrapper">

  <!-- Sidebar -->
  <?php include("includes/sidebar.php"); ?>

  <!-- Contenido principal -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">

    <div class="form-box">
      <h3><i class="bi bi-building"></i> Datos de la Empresa</h3>

      <form action="api/empresa_update.php" method="POST">
        <input type="hidden" name="id" value="<?= $empresa['id'] ?>">

        <div class="mb-3">
          <label>Nombre del WISP</label>
          <input type="text" name="nombreWisp" class="form-control" value="<?= htmlspecialchars($empresa['nombreWisp']) ?>" required>
        </div>

        <div class="mb-3">
          <label>RFC</label>
          <input type="text" name="rfc" class="form-control" value="<?= htmlspecialchars($empresa['rfc']) ?>">
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Código Postal</label>
            <input type="text" name="cp" class="form-control" value="<?= htmlspecialchars($empresa['cp']) ?>">
          </div>

          <div class="col-md-6 mb-3">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($empresa['telefono']) ?>">
          </div>
        </div>

        <div class="mb-4">
          <label>Dirección</label>
          <textarea name="direccion" class="form-control" rows="3"><?= htmlspecialchars($empresa['direccion']) ?></textarea>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
      </form>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

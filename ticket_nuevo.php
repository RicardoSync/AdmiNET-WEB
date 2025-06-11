<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once __DIR__ . '/includes/db.php';

// Obtener clientes y usuarios responsables
$clientes = $conn->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $conn->query("SELECT id, nombre FROM usuarios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_cliente = $_POST['id_cliente'];
  $categoria = $_POST['categoria'];
  $descripcion = $_POST['descripcion'];
  $id_responsable = !empty($_POST['id_responsable']) ? $_POST['id_responsable'] : null;

  if (empty($id_cliente) || empty($categoria) || empty($descripcion)) {
    $_SESSION['msg_error'] = "Todos los campos obligatorios deben completarse.";
  } else {
    $stmt = $conn->prepare("INSERT INTO tickets (id_cliente, categoria, descripcion, id_responsable) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_cliente, $categoria, $descripcion, $id_responsable]);

    $_SESSION['msg_success'] = "Ticket registrado exitosamente.";
    header("Location: tickets.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Ticket | AdmiNET</title>
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
      <h2 class="text-primary"><i class="bi bi-plus-circle me-2"></i>Nuevo Ticket</h2>
      <a href="tickets.php" class="btn btn-secondary">
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
        <label for="id_cliente" class="form-label">Cliente *</label>
        <select name="id_cliente" id="id_cliente" class="form-select" required>
          <option value="">Selecciona un cliente</option>
          <?php foreach ($clientes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="categoria" class="form-label">Categoría *</label>
        <select name="categoria" id="categoria" class="form-select" required>
          <option value="">Selecciona una categoría</option>
          <option value="Soporte técnico">Soporte técnico</option>
          <option value="Facturación">Facturación</option>
          <option value="Instalación">Instalación</option>
          <option value="Otro">Otro</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción *</label>
        <textarea name="descripcion" id="descripcion" class="form-control" rows="4" required></textarea>
      </div>

      <div class="mb-3">
        <label for="id_responsable" class="form-label">Responsable (opcional)</label>
        <select name="id_responsable" id="id_responsable" class="form-select">
          <option value="">Sin asignar</option>
          <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
          <?php endforeach ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> Guardar Ticket
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

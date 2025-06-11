<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once __DIR__ . '/includes/db.php';

if (!isset($_GET['id'])) {
  $_SESSION['msg_error'] = "ID no especificado.";
  header("Location: tickets.php");
  exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
  $_SESSION['msg_error'] = "Ticket no encontrado.";
  header("Location: tickets.php");
  exit;
}

$clientes = $conn->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $conn->query("SELECT id, nombre FROM usuarios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Ticket | AdmiNET</title>
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
      <h2 class="text-primary"><i class="bi bi-pencil-square me-2"></i>Editar Ticket</h2>
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

    <form method="POST" action="api/editar_ticket.php" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $ticket['id'] ?>">

      <div class="mb-3">
        <label for="id_cliente" class="form-label">Cliente *</label>
        <select name="id_cliente" id="id_cliente" class="form-select text-dark" required>
          <option value="">Selecciona un cliente</option>
          <?php foreach ($clientes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id'] == $ticket['id_cliente'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['nombre']) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="categoria" class="form-label">Categoría *</label>
        <select name="categoria" id="categoria" class="form-select text-dark" required>
          <option value="">Selecciona una categoría</option>
          <?php foreach (['Soporte técnico', 'Facturación', 'Instalación', 'Otro'] as $cat): ?>
            <option value="<?= $cat ?>" <?= $ticket['categoria'] == $cat ? 'selected' : '' ?>>
              <?= $cat ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción *</label>
        <textarea name="descripcion" id="descripcion" class="form-control text-dark" rows="4" required><?= htmlspecialchars($ticket['descripcion']) ?></textarea>
      </div>

      <div class="mb-3">
        <label for="estado" class="form-label">Estado *</label>
        <select name="estado" id="estado" class="form-select text-dark" required>
          <?php foreach (['Pendiente', 'En proceso', 'Resuelto', 'Cerrado'] as $estado): ?>
            <option value="<?= $estado ?>" <?= $ticket['estado'] == $estado ? 'selected' : '' ?>>
              <?= $estado ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="mb-3" id="evidenciaContainer" style="display: none;">
        <label for="evidencia" class="form-label">Evidencia (imagen)</label>
        <input type="file" name="evidencia" id="evidencia" class="form-control" accept=".png,.jpg,.jpeg">
      </div>

      <?php if (!empty($ticket['ruta_evidencia'])): ?>
        <div class="mb-3">
          <label class="form-label">Evidencia actual</label><br>
          <img src="<?= htmlspecialchars($ticket['ruta_evidencia']) ?>" alt="Evidencia del ticket" class="img-fluid rounded shadow" style="max-height: 300px;">
        </div>
      <?php else: ?>
        <div class="mb-3">
          <label class="form-label">Evidencia actual</label><br>
          <p class="text-muted fst-italic">Sin evidencia aún.</p>
        </div>
      <?php endif; ?>



      <div class="mb-3">
        <label for="id_responsable" class="form-label">Responsable (opcional)</label>
        <select name="id_responsable" id="id_responsable" class="form-select text-dark">
          <option value="">Sin asignar</option>
          <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $ticket['id_responsable'] == $u['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['nombre']) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> Guardar Cambios
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const estado = document.getElementById("estado");
    const evidenciaContainer = document.getElementById("evidenciaContainer");

    function toggleEvidencia() {
      const valor = estado.value;
      evidenciaContainer.style.display = (valor === "Resuelto" || valor === "Cerrado") ? "block" : "none";
    }

    estado.addEventListener("change", toggleEvidencia);
    toggleEvidencia(); // ejecutar al cargar si el ticket ya está en Resuelto/Cerrado
  });
</script>

</body>
</html>

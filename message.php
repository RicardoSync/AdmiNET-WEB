<?php
session_start();

$tipo = $_GET['tipo'] ?? 'info';
$mensaje = $_GET['mensaje'] ?? 'Mensaje no definido';
$redirigir = $_GET['redirigir'] ?? '../clientes.php';

$alerta = match($tipo) {
  'success' => 'alert-success',
  'error' => 'alert-danger',
  'warning' => 'alert-warning',
  default => 'alert-info',
};

$titulo = match($tipo) {
  'success' => '✅ Éxito',
  'error' => '❌ Error',
  'warning' => '⚠️ Advertencia',
  default => 'ℹ️ Información',
};
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mensaje | AdmiNET</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center" style="height: 100vh;">
  <div class="alert <?= $alerta ?> text-center p-5" style="max-width: 500px;">
    <h4><?= $titulo ?></h4>
    <p><?= htmlspecialchars($mensaje) ?></p>
    <a href="<?= $redirigir ?>" class="btn btn-outline-light mt-3">Volver</a>
  </div>
</body>
</html>

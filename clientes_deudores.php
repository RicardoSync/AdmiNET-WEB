<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once 'includes/db.php';
require_once 'includes/sidebar.php';

$clientesDeudores = $conn->query("
  SELECT c.id, c.nombre, c.dia_corte, p.fecha_pago, p.proximo_pago
  FROM clientes c
  LEFT JOIN (
    SELECT id_cliente, MAX(fecha_pago) AS fecha_pago, MAX(proximo_pago) AS proximo_pago
    FROM pagos
    GROUP BY id_cliente
  ) p ON c.id = p.id_cliente
  WHERE c.estado IN ('Activo','Suspendido') AND p.proximo_pago IS NOT NULL AND DATE(p.proximo_pago) < CURDATE()
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Clientes Deudores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>
  <div id="page-content-wrapper" class="container mt-5">
    <h2 class="text-danger">📄 Clientes con Pagos Pendientes</h2>
    <table class="table table-bordered table-hover mt-4">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Nombre</th>
          <th>Último Pago</th>
          <th>Próximo Pago</th>
          <th>Día de Corte</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clientesDeudores as $i => $cliente): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= htmlspecialchars($cliente['nombre']) ?></td>
          <td><?= htmlspecialchars($cliente['fecha_pago'] ?? 'N/A') ?></td>
          <td class="text-danger"><?= htmlspecialchars($cliente['proximo_pago']) ?></td>
          <td><?= htmlspecialchars($cliente['dia_corte']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (count($clientesDeudores) === 0): ?>
        <tr><td colspan="5" class="text-center text-muted">No hay clientes con pagos vencidos</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>

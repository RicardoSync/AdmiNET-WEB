<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once '../includes/db.php';
require_once '../vendor/fpdf/fpdf.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<p style='color:red'>ID inválido.</p>";
  exit;
}

$id = intval($_GET['id']);
$cliente = $conn->prepare("SELECT nombre FROM clientes WHERE id = ?");
$cliente->execute([$id]);
$info = $cliente->fetch(PDO::FETCH_ASSOC);

if (!$info) {
  echo "<p style='color:red'>Cliente no encontrado.</p>";
  exit;
}

$pagos = $conn->prepare("SELECT * FROM pagos WHERE id_cliente = ? ORDER BY fecha_pago DESC");
$pagos->execute([$id]);
$resultado = $pagos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Pagos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Historial de pagos de <strong><?= htmlspecialchars($info['nombre']) ?></strong></h5>
        <a href="pagos.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
      </div>
      <div class="card-body">
        <?php if (count($resultado) > 0): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-hover text-center">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Fecha</th>
                  <th>Monto</th>
                  <th>Pagado</th>
                  <th>Cambio</th>
                  <th>Método</th>
                  <th>Próximo</th>
                  <th>Recibo</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($resultado as $p): ?>
                  <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= $p['fecha_pago'] ?></td>
                    <td>$<?= number_format($p['monto'], 2) ?></td>
                    <td>$<?= number_format($p['cantidad'], 2) ?></td>
                    <td>$<?= number_format($p['cambio'], 2) ?></td>
                    <td><?= $p['metodo_pago'] ?></td>
                    <td><?= $p['proximo_pago'] ?></td>
                    <td>
                      <a href="api/reimprimir_pdf.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-info">
                        <i class="bi bi-receipt"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted">Este cliente no tiene pagos registrados.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>

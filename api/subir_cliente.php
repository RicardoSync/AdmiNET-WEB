<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if (!isset($_GET['id'])) {
  $_SESSION['msg_error'] = "ID de cliente no especificado.";
  header("Location: ../clientes.php");
  exit;
}

$idCliente = intval($_GET['id']);
$stmt = $conn->prepare("SELECT c.*, p.velocidad, p.nombre AS nombre_paquete, mk.nombre AS mikrotik_nombre, mk.ip, mk.username, mk.password, mk.port 
                        FROM clientes c
                        JOIN paquetes p ON c.id_paquete = p.id
                        JOIN credenciales_microtik mk ON c.id_microtik = mk.id
                        WHERE c.id = ?");
$stmt->execute([$idCliente]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
  $_SESSION['msg_error'] = "Cliente no encontrado.";
  header("Location: ../clientes.php");
  exit;
}

$stmtQP = $conn->prepare("SELECT * FROM queue_parent WHERE id_mikrotik = ?");
$stmtQP->execute([$cliente['id_microtik']]);
$queueParents = $stmtQP->fetchAll(PDO::FETCH_ASSOC);

// Inicializar mensaje local
$error_local = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $queue_parent_id = $_POST['queue_parent'] ?? null;
  $parentName = "none";

  if (!empty($queue_parent_id)) {
    $stmtSel = $conn->prepare("SELECT nombre FROM queue_parent WHERE id = ?");
    $stmtSel->execute([$queue_parent_id]);
    $qp = $stmtSel->fetch(PDO::FETCH_ASSOC);
    if ($qp) {
      $parentName = $qp['nombre'];
    }
  }

  if (empty($cliente['ip_cliente']) || empty($cliente['velocidad'])) {
    $error_local = "❌ El cliente no tiene IP o velocidad definida.";
  } elseif (strpos($cliente['velocidad'], "/") === false) {
    $error_local = "⚠️ Velocidad mal formateada. Debe tener formato '5M/2M'.";
  } else {
    // Verificar si ya existe una simple queue con esa IP
    $target = $cliente['ip_cliente'] . "/32";
    $verificacion = ejecutarComandoSSHRetorno(
      $cliente['ip'],
      $cliente['username'],
      $cliente['password'],
      $cliente['port'] ?: 22,
      '/queue simple print where target="' . $target . '"'
    );

    if ($verificacion && str_contains($verificacion, 'name=')) {
      preg_match('/name="?(.*?)"?\s/', $verificacion, $match);
      $nombreExistente = $match[1] ?? 'desconocido';
      $error_local = "⚠️ Ya existe una simple queue con esa IP asignada. Nombre actual: <strong>$nombreExistente</strong>";

      // Registrar en logs
      $conn->prepare("INSERT INTO logs_acciones_red (id_cliente, ip_mikrotik, accion, resultado, mensaje) VALUES (?, ?, 'verificar_simple_queue', 'Detectado', ?)")
           ->execute([$cliente['id'], $cliente['ip'], $verificacion]);
    } else {
      // Separar velocidad
      [$down, $up] = explode("/", $cliente['velocidad']);

      function toKbps($val) {
        $val = strtoupper(trim($val));
        if (str_ends_with($val, "M")) return intval(floatval($val) * 1000);
        if (str_ends_with($val, "K")) return intval(floatval($val));
        return intval($val);
      }

      $downNum = toKbps($down);
      $upNum = toKbps($up);

      $burstDown = intval($downNum * 1.5) . "K";
      $burstUp   = intval($upNum * 1.5) . "K";
      $thresholdDown = intval($downNum * 0.9) . "K";
      $thresholdUp   = intval($upNum * 0.9) . "K";

      $maxLimit = "{$downNum}K/{$upNum}K";
      $burstLimit = "$burstDown/$burstUp";
      $burstThreshold = "$thresholdDown/$thresholdUp";
      $burstTime = "20s/20s";

      $cmd = '/queue simple add name="' . $cliente['nombre'] . '" ';
      $cmd .= 'target=' . $target . ' ';
      $cmd .= 'max-limit=' . $maxLimit . ' ';
      $cmd .= 'burst-limit=' . $burstLimit . ' ';
      $cmd .= 'burst-threshold=' . $burstThreshold . ' ';
      $cmd .= 'burst-time=' . $burstTime . ' ';
      $cmd .= 'comment="Subido desde AdmiNET" ';
      if ($parentName !== "none") {
        $cmd .= 'parent="' . $parentName . '"';
      }

      try {
        $ok = ejecutarComandoSSH($cliente['ip'], $cliente['username'], $cliente['password'], $cliente['port'] ?: 22, $cmd);

        $conn->prepare("INSERT INTO logs_acciones_red (id_cliente, ip_mikrotik, accion, resultado, mensaje) VALUES (?, ?, ?, ?, ?)")
             ->execute([$cliente['id'], $cliente['ip'], 'subir_simple_queue', $ok ? 'Éxito' : 'Error', $cmd]);

        $_SESSION['msg_success'] = "✅ Cliente subido correctamente.";
        header("Location: ../clientes.php");
        exit;
      } catch (Exception $e) {
        $conn->prepare("INSERT INTO logs_acciones_red (id_cliente, ip_mikrotik, accion, resultado, mensaje) VALUES (?, ?, ?, ?, ?)")
             ->execute([$cliente['id'], $cliente['ip'], 'subir_simple_queue', 'Error', $e->getMessage()]);
        $error_local = "❌ Error al subir cliente: " . $e->getMessage();
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Subir Cliente | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("../includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">Subir Cliente a MikroTik</h2>
      <a href="../clientes.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Clientes
      </a>
    </div>

    <?php if (isset($_SESSION['msg_success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['msg_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
      <?php unset($_SESSION['msg_success']); ?>
    <?php endif; ?>

    <?php if (!empty($error_local)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error_local ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <div class="card shadow rounded p-4">
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Nombre del Cliente:</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($cliente['nombre']) ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">IP Asignada:</label>
          <input type="text" class="form-control" value="<?= $cliente['ip_cliente'] ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Velocidad:</label>
          <input type="text" class="form-control" value="<?= $cliente['velocidad'] ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">MikroTik Asociado:</label>
          <input type="text" class="form-control" value="<?= $cliente['mikrotik_nombre'] ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Seleccionar Queue Parent (opcional):</label>
          <select name="queue_parent" class="form-select">
            <option value="">-- Sin cola padre --</option>
            <?php foreach ($queueParents as $qp): ?>
              <option value="<?= $qp['id'] ?>"><?= htmlspecialchars($qp['nombre']) ?> (<?= $qp['subred'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="d-flex justify-content-between">
          <button type="submit" class="btn btn-success"><i class="bi bi-cloud-upload"></i> Subir Cliente</button>
          <a href="../clientes.php" class="btn btn-outline-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

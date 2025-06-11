<?php
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mac = $_POST['mac'] ?? '';
  $id = intval($_POST['id_mikrotik']);

  $stmt = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
  $stmt->execute([$id]);
  $mk = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$mk) {
    echo "MikroTik no encontrado.";
    exit;
  }

  // Buscar lease por MAC y hacerlo estático
  $comando = "/ip/dhcp-server/lease/make-static [find where mac-address=$mac]";
  $ok = ejecutarComandoSSH($mk['ip'], $mk['username'], $mk['password'], intval($mk['port']), $comando);

  echo $ok ? "✔️ Lease convertido en estático." : "❌ Error al convertir en estático.";
}

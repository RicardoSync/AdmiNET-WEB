<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $ip = $_POST['ip'] ?? '';
  $nombre = $_POST['nombre'] ?? '';
  $id_mk = intval($_POST['id_mikrotik']);

  if (!$ip || !$nombre || !$id_mk) {
    echo "Datos incompletos.";
    exit;
  }

  // Registrar cliente con datos mínimos
  $stmt = $conn->prepare("INSERT INTO clientes (nombre, ip_cliente, estado, id_microtik) VALUES (?, ?, 'Activo', ?)");
  $ok = $stmt->execute([$nombre, $ip, $id_mk]);

  echo $ok ? "✅ Cliente registrado." : "❌ Error al registrar.";
}

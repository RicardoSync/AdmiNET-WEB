<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/SSHHelper.php';

if (!isset($_POST['id'])) {
  http_response_code(400);
  echo "ID no proporcionado.";
  exit;
}

$id = intval($_POST['id']);
$nombre = trim($_POST['nombre']);
$subred = trim($_POST['subred']);
$max_limit = trim($_POST['max_limit']);
$id_mikrotik = intval($_POST['id_mikrotik']);

// Obtener credenciales MikroTik
$stmt_mk = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
$stmt_mk->execute([$id_mikrotik]);
$mk = $stmt_mk->fetch(PDO::FETCH_ASSOC);

if (!$mk) {
  http_response_code(404);
  echo "❌ MikroTik no encontrado.";
  exit;
}

if (empty($subred)) {
  $_SESSION['msg_error'] = "❌ La subred no puede estar vacía.";
  header("Location: ../queue_parent.php");
  exit;
}

try {
  // Comando para editar el queue parent (sin eliminarlo)
  $comandoEditar = '/queue/simple/set [find where target="' . $subred . '"] name="' . $nombre . '" max-limit=' . $max_limit . ' comment="Editado desde AdmiNET"';

  // Ejecutar
  ejecutarComandoSSH($mk['ip'], $mk['username'], $mk['password'], $mk['port'] ?: 22, $comandoEditar);

  // Actualizar en base de datos
  $stmt = $conn->prepare("UPDATE queue_parent SET nombre=?, subred=?, max_limit=?, id_mikrotik=? WHERE id=?");
  $stmt->execute([$nombre, $subred, $max_limit, $id_mikrotik, $id]);

  $_SESSION['msg_success'] = "✅ Queue Parent actualizado correctamente.";
  header("Location: ../queue_parent.php");

} catch (Exception $e) {
  $_SESSION['msg_error'] = "❌ Error al editar Queue Parent: " . $e->getMessage();
  header("Location: ../queue_parent.php");
}

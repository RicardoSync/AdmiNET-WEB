<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['msg_error'] = "Método no permitido.";
  header("Location: ../tickets.php");
  exit;
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
  $_SESSION['msg_error'] = "ID de ticket no especificado.";
  header("Location: ../tickets.php");
  exit;
}

$id = intval($_POST['id']);
$id_cliente = $_POST['id_cliente'];
$categoria = $_POST['categoria'];
$descripcion = $_POST['descripcion'];
$estado = $_POST['estado'];
$id_responsable = !empty($_POST['id_responsable']) ? $_POST['id_responsable'] : null;

// Validación básica
if (empty($id_cliente) || empty($categoria) || empty($descripcion) || empty($estado)) {
  $_SESSION['msg_error'] = "Todos los campos obligatorios deben completarse.";
  header("Location: ../ticket_editar.php?id=" . $id);
  exit;
}

// ✅ Manejo de evidencia (antes del UPDATE)
$rutaEvidencia = null;
if (in_array($estado, ['Resuelto', 'Cerrado']) && isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] === UPLOAD_ERR_OK) {
  $nombre = basename($_FILES['evidencia']['name']);
  $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
  $permitidas = ['png', 'jpg', 'jpeg'];

  if (in_array($ext, $permitidas)) {
    $nombreSeguro = 'ticket_' . $id . '_' . time() . '.' . $ext;
    $rutaFinal = '../evidencias/' . $nombreSeguro;

    if (move_uploaded_file($_FILES['evidencia']['tmp_name'], $rutaFinal)) {
      $rutaEvidencia = 'evidencias/' . $nombreSeguro;
    }
  }
}

try {
  $sql = "UPDATE tickets SET id_cliente = ?, categoria = ?, descripcion = ?, estado = ?, id_responsable = ?";
  $params = [$id_cliente, $categoria, $descripcion, $estado, $id_responsable];

  if ($rutaEvidencia !== null) {
    $sql .= ", ruta_evidencia = ?";
    $params[] = $rutaEvidencia;
  }

  $sql .= " WHERE id = ?";
  $params[] = $id;

  $stmt = $conn->prepare($sql);
  $stmt->execute($params);

  $_SESSION['msg_success'] = "Ticket actualizado correctamente.";
  header("Location: ../tickets.php");
  exit;

} catch (Exception $e) {
  $_SESSION['msg_error'] = "Error al actualizar: " . $e->getMessage();
  header("Location: ../ticket_editar.php?id=" . $id);
  exit;
}

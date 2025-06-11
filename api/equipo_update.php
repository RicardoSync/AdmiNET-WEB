<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $nombre = trim($_POST['nombre']);
  $tipo = $_POST['tipo'];
  $marca = trim($_POST['marca']);
  $modelo = trim($_POST['modelo']);
  $mac = trim($_POST['mac']);
  $serial = trim($_POST['serial']);
  $estado = $_POST['estado'];
  $id_cliente = !empty($_POST['id_cliente']) ? intval($_POST['id_cliente']) : null;

  try {
    $sql = "UPDATE equipos SET 
              nombre = :nombre,
              tipo = :tipo,
              marca = :marca,
              modelo = :modelo,
              mac = :mac,
              serial = :serial,
              estado = :estado,
              id_cliente = :id_cliente
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':marca', $marca);
    $stmt->bindParam(':modelo', $modelo);
    $stmt->bindParam(':mac', $mac);
    $stmt->bindParam(':serial', $serial);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    $stmt->execute();

    $_SESSION['msg_success'] = "Equipo actualizado correctamente.";
    header("Location: ../equipos.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al actualizar el equipo: " . $e->getMessage();
    header("Location: ../equipo_editar.php?id=" . $id);
    exit;
  }
} else {
  header("Location: ../equipos.php");
  exit;
}

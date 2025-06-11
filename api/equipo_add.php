<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Obtener y limpiar valores
  $nombre = trim($_POST['nombre']);
  $tipo = $_POST['tipo'];
  $marca = trim($_POST['marca']);
  $modelo = trim($_POST['modelo']);
  $mac = trim($_POST['mac']);
  $serial = trim($_POST['serial']);
  $estado = $_POST['estado'];
  $id_cliente = !empty($_POST['id_cliente']) ? intval($_POST['id_cliente']) : null;

  try {
    $sql = "INSERT INTO equipos (nombre, tipo, marca, modelo, mac, serial, estado, id_cliente)
            VALUES (:nombre, :tipo, :marca, :modelo, :mac, :serial, :estado, :id_cliente)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':marca', $marca);
    $stmt->bindParam(':modelo', $modelo);
    $stmt->bindParam(':mac', $mac);
    $stmt->bindParam(':serial', $serial);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);

    $stmt->execute();

    $_SESSION['msg_success'] = "Equipo registrado correctamente.";
    header("Location: ../equipos.php");
    exit;

  } catch (Exception $e) {
    $_SESSION['msg_error'] = "Error al registrar el equipo: " . $e->getMessage();
    header("Location: ../equipo_nuevo.php");
    exit;
  }
} else {
  header("Location: ../equipos.php");
  exit;
}

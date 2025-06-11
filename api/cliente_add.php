<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo "Método no permitido";
  exit;
}

$nombre = $_POST['nombre'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];
$direccion = $_POST['direccion'];
$ip_cliente = $_POST['ip_cliente'];
$dia_corte = $_POST['dia_corte'];
$estado = $_POST['estado'];
$id_antena_ap = $_POST['id_antena_ap'] ?: null;
$id_servicio_plataforma = $_POST['id_servicio_plataforma'] ?: null;
$id_paquete = $_POST['id_paquete'];
$id_microtik = $_POST['id_microtik'];
$ubicacion_maps = $_POST['ubicacion_maps'];
$tipo_conexion = $_POST['tipo_conexion'];

try {
  $sql = "INSERT INTO clientes (nombre, telefono, email, direccion, ip_cliente, dia_corte, estado, id_antena_ap, id_servicio_plataforma, id_paquete, id_microtik, ubicacion_maps, tipo_conexion)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->execute([
    $nombre, $telefono, $email, $direccion, $ip_cliente, $dia_corte, $estado,
    $id_antena_ap, $id_servicio_plataforma, $id_paquete, $id_microtik,
    $ubicacion_maps, $tipo_conexion
  ]);

  $_SESSION['msg_success'] = "Cliente registrado exitosamente.";
  header("Location: ../clientes.php");
  exit;
} catch (PDOException $e) {
  $_SESSION['msg_error'] = "Error al registrar cliente: " . $e->getMessage();
  header("Location: ../clientes.php");
  exit;
}
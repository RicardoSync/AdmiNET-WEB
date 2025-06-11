<?php
session_start();
require_once '../includes/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fecha_inicio = $_POST['fecha_inicio'];
  $fecha_fin = $_POST['fecha_fin'];
  $total_ingresos = floatval($_POST['total_ingresos']);
  $total_egresos = floatval($_POST['total_egresos']);
  $balance = $total_ingresos - $total_egresos;

  if (!$fecha_inicio || !$fecha_fin) {
    $_SESSION['msg_error'] = "Fechas requeridas.";
    header("Location: ../egresos.php");
    exit;
  }

  $stmt = $conn->prepare("INSERT INTO cortes_caja (fecha_inicio, fecha_fin, total_ingresos, total_egresos, balance)
                          VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$fecha_inicio, $fecha_fin, $total_ingresos, $total_egresos, $balance]);

  $_SESSION['msg_success'] = "Corte registrado correctamente.";
  header("Location: ../egresos.php");
  exit;
} else {
  $_SESSION['msg_error'] = "Método no permitido.";
  header("Location: ../egresos.php");
  exit;
}

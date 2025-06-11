<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../login.php");
  exit;
}

require_once '../includes/db.php';
require_once '../vendor/fpdf/fpdf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo "Método no permitido";
  exit;
}

$id_cliente = $_POST['id_cliente'];
$metodo_pago = $_POST['metodo_pago'];
$cantidad = $_POST['cantidad'];
$descuento = isset($_POST['descuento']) ? floatval($_POST['descuento']) : 0;
$meses_pagados = isset($_POST['meses_pagados']) ? intval($_POST['meses_pagados']) : 1;
if (isset($_POST['fecha_base']) && !empty($_POST['fecha_base'])) {
  $fecha_base_input = $_POST['fecha_base'];
} else {
  // Por seguridad, puedes registrar un error o usar la fecha actual si realmente es necesario
  $fecha_base_input = date('Y-m-d'); // o mejor: abortar si no hay fecha base válida
}

$usuario = $_SESSION['usuario'];

$stmt = $conn->prepare("SELECT c.nombre, c.id_servicio_plataforma, p.nombre AS paquete, p.precio, c.dia_corte
                        FROM clientes c
                        JOIN paquetes p ON c.id_paquete = p.id
                        WHERE c.id = ?");
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

$empresa = $conn->query("SELECT * FROM datosEmpresa LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if (!$cliente || !$empresa) {
  echo "Datos insuficientes para generar recibo.";
  exit;
}

$nombre = $cliente['nombre'];
$paquete = $cliente['paquete'];
$precio_paquete = floatval($cliente['precio']);
$nombre_servicio = 'Sin servicio';
$precio_servicio_unitario = 0;

if (!empty($cliente['id_servicio_plataforma'])) {
  $stmt2 = $conn->prepare("SELECT nombre, precio FROM serviciosplataforma WHERE idPlataformas = ?");
  $stmt2->execute([$cliente['id_servicio_plataforma']]);
  $serv = $stmt2->fetch(PDO::FETCH_ASSOC);
  if ($serv) {
    $nombre_servicio = $serv['nombre'];
    $precio_servicio_unitario = floatval($serv['precio']);
  }
}

$subtotal_paquete = $precio_paquete * $meses_pagados;
$subtotal_servicio = $precio_servicio_unitario * $meses_pagados;
$total = $subtotal_paquete + $subtotal_servicio;
$total_final = $total - $descuento;
$cambio = floatval($cantidad) - $total_final;

$fecha_pago = date('Y-m-d H:i:s');
$hora = date('H:i:s');
$diaCorte = str_pad($cliente['dia_corte'], 2, "0", STR_PAD_LEFT);

// Usamos la fecha base como punto de referencia (inicio de deuda)
function calcularProximoPago($fecha_base, $meses, $dia_corte) {
  $base = new DateTime($fecha_base);
  $base->modify("first day of this month");
  $base->modify("+$meses month");

  $anio = (int)$base->format('Y');
  $mes = (int)$base->format('m');

  // Calcular el día válido del mes
  $ultimoDiaMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
  $dia_final = min($dia_corte, $ultimoDiaMes);

  return sprintf('%04d-%02d-%02d 00:00:00', $anio, $mes, $dia_final);
}


$proximo_pago = calcularProximoPago($fecha_base_input, $meses_pagados, $cliente['dia_corte']);



$codigo = "PAY-" . $id_cliente . "-" . time();

$insert = $conn->prepare("INSERT INTO pagos (id_cliente, nombre, monto, fecha_pago, metodo_pago, cantidad, cambio, codigo_barras, proximo_pago)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$insert->execute([$id_cliente, $nombre, $total_final, $fecha_pago, $metodo_pago, $cantidad, $cambio, $codigo, $proximo_pago]);

function fechaLarga($fecha)
{
  $formatter = new IntlDateFormatter(
    'es_MX',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'America/Mexico_City',
    IntlDateFormatter::GREGORIAN,
    "d 'de' MMMM 'del' y"
  );

  $timestamp = strtotime(substr($fecha, 0, 10)); // Asegura que solo tome la fecha
  return ucfirst($formatter->format($timestamp));
}



$pdf = new FPDF('P', 'mm', array(80, 190));
$pdf->AddPage();
$pdf->SetMargins(4, 4, 4);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 5, strtoupper($empresa['nombreWisp']), 0, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 4, 'Direccion: ' . $empresa['direccion'], 0, 1, 'C');
$pdf->Cell(0, 4, 'C.P: ' . $empresa['cp'] . ' Tel: ' . $empresa['telefono'], 0, 1, 'C');
$pdf->Cell(0, 4, 'RFC: ' . ($empresa['rfc'] ?: 'N/A'), 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, 'RECIBO DE PAGO', 0, 1, 'C');
$pdf->Ln(2);
$pdf->Cell(0, 5, "Cliente: $nombre", 0, 1);
$pdf->Cell(0, 5, "Plan: $paquete ($$precio_paquete)", 0, 1);
$pdf->Cell(0, 5, "Servicio: $nombre_servicio ($$precio_servicio_unitario)", 0, 1);
$pdf->Cell(0, 5, "Meses pagados: $meses_pagados", 0, 1);
$pdf->Ln(1);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, 'Concepto', 1);
$pdf->Cell(25, 5, 'Detalle', 1);
$pdf->Cell(25, 5, 'Monto', 1);
$pdf->Ln();
$pdf->Cell(25, 5, 'Mensualidad', 1);
$pdf->Cell(25, 5, "$meses_pagados mes" . ($meses_pagados > 1 ? 'es' : ''), 1);
$pdf->Cell(25, 5, '$' . number_format($subtotal_paquete, 2), 1);
$pdf->Ln();
$pdf->Cell(25, 5, 'Servicio', 1);
$pdf->Cell(25, 5, $nombre_servicio, 1);
$pdf->Cell(25, 5, '$' . number_format($subtotal_servicio, 2), 1);
$pdf->Ln();
$pdf->Cell(25, 5, 'Descuento', 1);
$pdf->Cell(25, 5, '', 1);
$pdf->Cell(25, 5, '-$' . number_format($descuento, 2), 1);
$pdf->Ln();
$pdf->Cell(50, 5, 'TOTAL A PAGAR', 1);
$pdf->Cell(25, 5, '$' . number_format($total_final, 2), 1);
$pdf->Ln();
$pdf->Cell(50, 5, 'Cambio', 1);
$pdf->Cell(25, 5, '$' . number_format($cambio, 2), 1);
$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, 'Dia de Pago: ' . date('d/m/Y'), 0, 1);
$pdf->Cell(0, 5, 'VENCIMIENTO: ' . date('d/m/Y', strtotime($proximo_pago)), 0, 1);
$pdf->Ln(3);
$pdf->Cell(0, 5, $codigo, 0, 1, 'C');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(0, 4, 'Codigo de Verificacion:', 0, 1, 'C');
$pdf->Cell(0, 4, $codigo, 0, 1, 'C');
$pdf->Ln(2);
$pdf->Cell(0, 4, 'Cajero: ' . $usuario, 0, 1, 'C');
$pdf->Cell(0, 4, 'Fecha: ' . date('d/m/Y') . '  Hora: ' . $hora, 0, 1, 'C');
$pdf->Cell(0, 4, chr(169) . ' AdmiNET 2025', 0, 1, 'C');

$pdf->Output("I", "Recibo_$codigo.pdf");
exit;
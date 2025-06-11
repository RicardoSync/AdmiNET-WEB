<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  exit("No autorizado");
}

require_once '../includes/db.php';
require_once '../vendor/fpdf/fpdf.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  http_response_code(400);
  exit("ID inválido");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT p.*, c.id_servicio_plataforma, c.id_paquete, pk.nombre AS paquete, pk.precio AS precio_paquete
                        FROM pagos p
                        JOIN clientes c ON c.id = p.id_cliente
                        JOIN paquetes pk ON pk.id = c.id_paquete
                        WHERE p.id = ?");
$stmt->execute([$id]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pago) {
  exit("Pago no encontrado");
}

$empresa = $conn->query("SELECT * FROM datosEmpresa LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$empresa) {
  exit("Datos de empresa no disponibles");
}

$precio_servicio = 0;
$nombre_servicio = 'Sin servicio';
if (!empty($pago['id_servicio_plataforma'])) {
  $stmt2 = $conn->prepare("SELECT nombre, precio FROM serviciosplataforma WHERE idPlataformas = ?");
  $stmt2->execute([$pago['id_servicio_plataforma']]);
  $serv = $stmt2->fetch(PDO::FETCH_ASSOC);
  if ($serv) {
    $nombre_servicio = $serv['nombre'];
    $precio_servicio = floatval($serv['precio']);
  }
}

$precio_paquete = floatval($pago['precio_paquete']);
$descuento = ($precio_paquete + $precio_servicio) - floatval($pago['monto']);
$cambio = floatval($pago['cambio']);

$pdf = new FPDF('P', 'mm', array(80, 180));
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
$pdf->Cell(0, 5, "Cliente: {$pago['nombre']}", 0, 1);
$pdf->Cell(0, 5, "Plan: {$pago['paquete']} ($$precio_paquete)", 0, 1);
$pdf->Cell(0, 5, "Servicio: $nombre_servicio ($$precio_servicio)", 0, 1);
$pdf->Cell(0, 5, "Fecha: {$pago['fecha_pago']}", 0, 1);
$pdf->Ln(1);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, 'Concepto', 1);
$pdf->Cell(25, 5, 'Detalle', 1);
$pdf->Cell(25, 5, 'Monto', 1);
$pdf->Ln();
$pdf->Cell(25, 5, 'Mensualidad', 1);
$pdf->Cell(25, 5, '1 mes', 1);
$pdf->Cell(25, 5, '$' . number_format($precio_paquete, 2), 1);
$pdf->Ln();
$pdf->Cell(25, 5, 'Servicio', 1);
$pdf->Cell(25, 5, $nombre_servicio, 1);
$pdf->Cell(25, 5, '$' . number_format($precio_servicio, 2), 1);
$pdf->Ln();
$pdf->Cell(25, 5, 'Descuento', 1);
$pdf->Cell(25, 5, '', 1);
$pdf->Cell(25, 5, '-$' . number_format($descuento, 2), 1);
$pdf->Ln();
$pdf->Cell(50, 5, 'TOTAL A PAGAR', 1);
$pdf->Cell(25, 5, '$' . number_format($pago['monto'], 2), 1);
$pdf->Ln();
$pdf->Cell(50, 5, 'Cambio', 1);
$pdf->Cell(25, 5, '$' . number_format($cambio, 2), 1);
$pdf->Ln(6);
$pdf->Cell(0, 5, 'VENCIMIENTO: ' . date('d/m/Y', strtotime($pago['proximo_pago'])), 0, 1);
$pdf->Ln(3);
$pdf->Cell(0, 5, $pago['codigo_barras'], 0, 1, 'C');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(0, 4, 'Código de Verificación:', 0, 1, 'C');
$pdf->Cell(0, 4, $pago['codigo_barras'], 0, 1, 'C');
$pdf->Ln(2);
$pdf->Cell(0, 4, 'Cajero: ' . $_SESSION['usuario'], 0, 1, 'C');
$pdf->Cell(0, 4, 'Reimpreso: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
$pdf->Cell(0, 4, chr(169) . ' AdmiNET 2025', 0, 1, 'C');

$pdf->Output("I", "Recibo_{$pago['codigo_barras']}.pdf");
exit;

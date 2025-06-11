<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo json_encode(["error" => "No autorizado"]);
  exit;
}

require_once '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  http_response_code(400);
  echo json_encode(["error" => "ID inválido"]);
  exit;
}

$id = intval($_GET['id']);

try {
  $stmt = $conn->prepare("SELECT c.nombre, c.serviciosTV, c.id_servicio_plataforma, p.nombre AS paquete, p.precio, c.dia_corte
                           FROM clientes c
                           JOIN paquetes p ON c.id_paquete = p.id
                           WHERE c.id = ?");
  $stmt->execute([$id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$data) {
    echo json_encode(["error" => "Cliente no encontrado"]);
    exit;
  }

  $precio_servicio = 0;
  $nombre_servicio = 'Sin servicio';

  if (!empty($data['id_servicio_plataforma'])) {
    $stmt2 = $conn->prepare("SELECT nombre, precio FROM serviciosplataforma WHERE idPlataformas = ?");
    $stmt2->execute([$data['id_servicio_plataforma']]);
    $serv = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($serv) {
      $nombre_servicio = $serv['nombre'];
      $precio_servicio = floatval($serv['precio']);
    }
  }

  $hoy = new DateTime();
  $diaCorte = str_pad($data['dia_corte'], 2, "0", STR_PAD_LEFT);

  // Construir la fecha del siguiente mes con el mismo día de corte
  $fechaCorte = new DateTime($hoy->format('Y-m') . '-' . $diaCorte);
  $fechaCorte->modify('+1 month');

  $fechaProxima = $fechaCorte->format('Y-m-d');

  echo json_encode([
    "paquete" => $data['paquete'],
    "precio" => $data['precio'],
    "servicio" => $nombre_servicio,
    "precio_servicio" => $precio_servicio,
    "fecha_pago" => $hoy->format('Y-m-d'),
    "proximo_pago" => $fechaProxima,
    "dia_corte" => intval($data['dia_corte']) // ✅ ESTO ES LO QUE FALTABA
  ]);


} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo json_encode(["error" => "No autorizado"]);
  exit;
}

require_once '../includes/db.php';

try {
  $sql = "SELECT c.id, c.nombre, c.estado, c.id_servicio_plataforma, c.id_paquete, c.dia_corte,
                 p1.fecha_pago AS ultimo_pago, p1.proximo_pago
          FROM clientes c
          LEFT JOIN (
              SELECT id_cliente, MAX(fecha_pago) AS fecha_pago, MAX(proximo_pago) AS proximo_pago
              FROM pagos
              GROUP BY id_cliente
          ) p1 ON p1.id_cliente = c.id";
  $stmt = $conn->query($sql);
  $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $data = [];

  foreach ($clientes as $c) {
    // Cálculo de adeudo
    $proximo_pago = $c['proximo_pago'];
    $hoy = date('Y-m-d');

    $meses_adeudo = 0;
    if ($proximo_pago && $proximo_pago < $hoy) {
      $inicio = new DateTime($proximo_pago);
      $fin = new DateTime($hoy);
      $meses_adeudo = ($fin->format('Y') - $inicio->format('Y')) * 12 + ($fin->format('m') - $inicio->format('m'));

      // Si el día de corte ya pasó este mes, se debe sumar 1
      if ($fin->format('d') >= $inicio->format('d')) {
        $meses_adeudo += 1;
      }
    }

    // Obtener precios
    $precio_paquete = 0;
    $precio_servicio = 0;

    if ($c['id_paquete']) {
      $stmt2 = $conn->prepare("SELECT precio FROM paquetes WHERE id = ?");
      $stmt2->execute([$c['id_paquete']]);
      $precio_paquete = floatval($stmt2->fetchColumn());
    }

    if ($c['id_servicio_plataforma']) {
      $stmt3 = $conn->prepare("SELECT precio FROM serviciosplataforma WHERE idPlataformas = ?");
      $stmt3->execute([$c['id_servicio_plataforma']]);
      $precio_servicio = floatval($stmt3->fetchColumn());
    }

    $total = $meses_adeudo * ($precio_paquete + $precio_servicio);

    $data[] = [
      "nombre" => $c['nombre'],
      "ultimo_pago" => $c['ultimo_pago'] ?: "Sin registro",
      "proximo_pago" => $c['proximo_pago'] ?: "N/A",
      "meses_adeudo" => $meses_adeudo,
      "total_adeudo" => $total,
      "estado" => $c['estado']
    ];
  }

  header('Content-Type: application/json');
  echo json_encode($data);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}

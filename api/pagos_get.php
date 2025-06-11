<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo json_encode(["error" => "No autorizado"]);
  exit;
}

require_once '../includes/db.php';

try {
  $sql = "SELECT p.id, c.nombre, p.monto, p.fecha_pago, p.metodo_pago, p.proximo_pago
          FROM pagos p
          JOIN clientes c ON p.id_cliente = c.id
          ORDER BY p.fecha_pago DESC";
  $stmt = $conn->query($sql);
  $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  header('Content-Type: application/json');
  echo json_encode($pagos);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}

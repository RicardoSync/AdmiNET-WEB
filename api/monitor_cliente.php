<?php
require_once '../includes/db.php';
require_once __DIR__ . '/../includes/SSHHelper.php';

if (!isset($_POST['id'])) {
  http_response_code(400);
  echo "ID no proporcionado.";
  exit;
}

$id_cliente = intval($_POST['id']);

try {
  $stmt = $conn->prepare("SELECT c.ip_cliente, m.ip AS mk_ip, m.username, m.password, m.port
                          FROM clientes c
                          INNER JOIN credenciales_microtik m ON c.id_microtik = m.id
                          WHERE c.id = ?");
  $stmt->execute([$id_cliente]);
  $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$cliente || !$cliente['ip_cliente']) {
    http_response_code(404);
    echo "Cliente o IP no encontrado.";
    exit;
  }

  $ip_cliente = $cliente['ip_cliente'];
  $comando = '/queue/simple/print stats without-paging where target="' . $ip_cliente . '/32"';


  $salida = ejecutarComandoSSHRetorno(
    $cliente['mk_ip'],
    $cliente['username'],
    $cliente['password'],
    $cliente['port'] ?: 22,
    $comando
  );

  echo json_encode(["raw" => $salida]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
?>


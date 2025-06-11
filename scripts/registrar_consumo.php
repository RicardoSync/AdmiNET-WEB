<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/SSHHelper.php';

// Configura estos valores según tu MikroTik WAN
$nombreInterfaz = 'ether23';

// Consulta la IP, usuario, contraseña y puerto del MikroTik principal
$stmt = $conn->prepare("SELECT ip, username, password, port FROM credenciales_microtik WHERE nombre = 'Principal' LIMIT 1");
$stmt->execute();
$mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mikrotik) {
    die("No se encontró la credencial del MikroTik principal.");
}

$comando = "/interface/ethernet/print stats where name=\"$nombreInterfaz\"";
$salida = ejecutarComandoSSHRetorno($mikrotik['ip'], $mikrotik['username'], $mikrotik['password'], intval($mikrotik['port']), $comando);

if (!$salida) {
    die("No se pudo obtener tráfico.");
}

// Extraer rx-bytes y tx-bytes
preg_match('/rx-bytes:\s*(\d+)/', $salida, $matchRx);
preg_match('/tx-bytes:\s*(\d+)/', $salida, $matchTx);

$rx = isset($matchRx[1]) ? (int)$matchRx[1] : 0;
$tx = isset($matchTx[1]) ? (int)$matchTx[1] : 0;
$total = $rx + $tx;

// Guardar en DB
$stmt = $conn->prepare("INSERT INTO consumo_internet (fecha, rx, tx, total) VALUES (NOW(), ?, ?, ?)");
$stmt->execute([$rx, $tx, $total]);

echo "Guardado correctamente: RX=$rx, TX=$tx";

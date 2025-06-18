<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db.php';
require_once '../vendor/autoload.php'; // Asegúrate que phpseclib esté instalado con Composer

use phpseclib3\Net\SSH2;

$id = $_GET['id'] ?? null;
if (!$id) {
  echo json_encode(["error" => "ID no válido"]);
  exit;
}

$stmt = $conn->prepare("SELECT * FROM credenciales_microtik WHERE id = ?");
$stmt->execute([$id]);
$mikrotik = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mikrotik) {
  echo json_encode(["error" => "No encontrado"]);
  exit;
}

$ssh = new SSH2($mikrotik['ip'], (int)$mikrotik['port']);
if (!$ssh->login($mikrotik['username'], $mikrotik['password'])) {
  echo json_encode(["error" => "SSH fallido"]);
  exit;
}

$cpu = trim($ssh->exec('/system/resource/print value-name=cpu-load'));
$mem_total = trim($ssh->exec('/system/resource/print value-name=total-memory'));
$mem_used = trim($ssh->exec('/system/resource/print value-name=used-memory'));

$mem_percent = ($mem_total != 0) ? round(((float)$mem_used / (float)$mem_total) * 100, 2) : 0;

echo json_encode([
  "cpu" => $cpu,
  "mem" => $mem_percent
]);

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  http_response_code(403);
  exit;
}
require_once '../includes/db.php';

// Detectar sistema operativo
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$python = __DIR__ . ($isWindows ? '/.venv/Scripts/python' : '/.venv/bin/python');

// Script y base de datos
$script = __DIR__ . "/monitor_antenas.py";
$basedatos = $conn->query("SELECT DATABASE()")->fetchColumn();

// Ejecutar script con seguridad
$salida = shell_exec(escapeshellcmd("$python $script") . ' ' . escapeshellarg($basedatos) . " 2>&1");

// Responder con JSON
header("Content-Type: application/json");
echo $salida;

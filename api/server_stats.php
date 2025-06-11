<?php
header('Content-Type: application/json');

// CPU
$cpu = shell_exec("top -bn1 | grep 'Cpu(s)' | awk '{print 100 - $8}'");
$cpu = round(floatval($cpu), 1);

// RAM
$ram = shell_exec("free | grep Mem | awk '{print $3/$2 * 100.0}'");
$ram = round(floatval($ram), 1);

// Disco
$diskUsed = shell_exec("df -h / | awk 'NR==2 {print $5}'");
$diskUsed = trim($diskUsed); // ejemplo: "45%"

// Red (solo primera interfaz activa que no sea loopback)
$iface = shell_exec("ip route get 1.1.1.1 | awk '{print $5}'");
$iface = trim($iface);
$rx = shell_exec("cat /sys/class/net/{$iface}/statistics/rx_bytes");
$tx = shell_exec("cat /sys/class/net/{$iface}/statistics/tx_bytes");

function formatBytes($bytes) {
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($sizes) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $sizes[$i];
}

$rxFormatted = formatBytes(floatval($rx));
$txFormatted = formatBytes(floatval($tx));
$network = "RX: $rxFormatted / TX: $txFormatted";

echo json_encode([
    'cpu' => $cpu,
    'ram' => $ram,
    'disk' => $diskUsed,
    'network' => $network
]);

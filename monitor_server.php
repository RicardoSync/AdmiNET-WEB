<?php
function getLinuxStats() {
    $cpu = sys_getloadavg()[0] * 100 / shell_exec("nproc");
    $ramTotal = (int) shell_exec("grep MemTotal /proc/meminfo | awk '{print $2}'");
    $ramLibre = (int) shell_exec("grep MemAvailable /proc/meminfo | awk '{print $2}'");
    $ramUso = 100 - (($ramLibre * 100) / $ramTotal);

    $disk = shell_exec("df / | awk 'NR==2 {print $5}'");

    $netRecv = shell_exec("cat /sys/class/net/$(ip route show default | awk '/default/ {print $5}')/statistics/rx_bytes");
    $netSent = shell_exec("cat /sys/class/net/$(ip route show default | awk '/default/ {print $5}')/statistics/tx_bytes");

    return [
        "cpu" => round($cpu, 2) . "%",
        "ram" => round($ramUso, 2) . "%",
        "disk" => trim($disk),
        "net_recv" => round($netRecv / 1024 / 1024, 2) . " MB",
        "net_sent" => round($netSent / 1024 / 1024, 2) . " MB"
    ];
}

header('Content-Type: application/json');
echo json_encode(getLinuxStats());

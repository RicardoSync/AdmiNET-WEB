<?php
require 'vendor/autoload.php';

use phpseclib3\Net\SSH2;

$ip = '192.168.1.64';
$usuario = 'admin';
$contrasena = 'admin';

$ssh = new SSH2($ip);
if (!$ssh->login($usuario, $contrasena)) {
    exit('❌ Error de autenticación con MikroTik');
}

echo "✅ Conexión exitosa.<br>";
echo "<pre>" . $ssh->exec('/ip address print') . "</pre>";
?>

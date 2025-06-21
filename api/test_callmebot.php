<?php
if (!isset($_GET['phone']) || !isset($_GET['apikey'])) {
  http_response_code(400);
  echo "Faltan parámetros";
  exit;
}

$phone = urlencode($_GET['phone']);
$apikey = urlencode($_GET['apikey']);
$text = urlencode("¡Hola! Esta es una prueba desde AdmiNET.");

$url = "https://api.callmebot.com/whatsapp.php?phone=$phone&text=$text&apikey=$apikey";

$response = file_get_contents($url);
echo $response;

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Información del Sistema | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap y estilos -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background-color: #121212;
      color: #fff;
      font-family: 'Segoe UI', sans-serif;
    }

    .info-card {
      background: #1e1e2f;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 0 15px rgba(0, 123, 255, 0.2);
    }

    .info-card h4 {
      color: #0dcaf0;
    }

    h2.text-primary {
      animation: fadeInDown 0.8s ease-out;
    }

    @keyframes fadeInDown {
      0% { opacity: 0; transform: translateY(-20px); }
      100% { opacity: 1; transform: translateY(0); }
    }

    img {
      filter: drop-shadow(0 0 5px #0dcaf0);
      transition: transform 0.3s ease;
    }

    img:hover {
      transform: scale(1.05);
    }

    .boton-regreso {
      background-color: #0dcaf0;
      color: #000;
      font-weight: bold;
      padding: 10px 30px;
      border-radius: 8px;
      border: none;
      transition: background-color 0.3s ease;
    }

    .boton-regreso:hover {
      background-color: #0bb2d4;
    }
  </style>
</head>
<body>

<div class="container mt-4 mb-5">
  <h2 class="mb-4 text-primary text-center">Información del Sistema</h2>

  <div class="row">
    <div class="col-md-6">
      <div class="info-card">
        <h4><i class="bi bi-code-slash me-2"></i>Lenguajes y tecnologías</h4>
        <ul>
          <li>PHP 8.1</li>
          <li>Python 3.11</li>
          <li>HTML5 / CSS3</li>
          <li>Bootstrap 5.3</li>
          <li>JavaScript ES6</li>
        </ul>
      </div>
    </div>

    <div class="col-md-6">
      <div class="info-card">
        <h4><i class="bi bi-hdd-network me-2"></i>Plataformas y herramientas</h4>
        <ul>
          <li>WireGuard VPN</li>
          <li>Ubuntu Server 22.04</li>
          <li>MikroTik RouterOS</li>
          <li>SystemD Services</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="info-card">
    <h4><i class="bi bi-shield-lock me-2"></i>Legal</h4>
    <p>
      © 2025 Software Escobedo. Desarrollado por Ricardo Escobedo.<br>
      Prohibida su descarga, modificación y uso del sistema para fines personales, comerciales o instalación en servidores propios sin autorización explícita del desarrollador.<br>
      Para contacto y licencias: <strong>richardobedoesc@gmail.com</strong><br>
      WhatsApp: <strong>4981442266</strong>
    </p>
  </div>

  <div class="text-center mt-5 d-flex justify-content-center align-items-center gap-4 flex-wrap">
    <img src="assets/img/logo1.png" alt="Logo Software Escobedo" height="100">
    <img src="assets/img/logo2.png" alt="Logo Alterno" height="100">
  </div>

  <div class="text-center mt-5">
    <a href="dashboard.php" class="btn boton-regreso">Acepto Términos y Regresar</a>
  </div>
</div>

</body>
</html>

<?php 
session_start();

if (!isset($_SESSION['usuario']) && isset($_COOKIE['usuario']) && isset($_COOKIE['empresa']) && isset($_COOKIE['base_datos'])) {
    $_SESSION['usuario'] = $_COOKIE['usuario'];
    $_SESSION['empresa'] = $_COOKIE['empresa'];
    $_SESSION['base_datos'] = $_COOKIE['base_datos'];
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #000;
      color: #fff;
      overflow: hidden;
    }

    #fondo-particulas {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 0;
      width: 100%;
      height: 100%;
    }

    .login-card {
      background: rgba(0, 10, 40, 0.85);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 0 15px rgba(0, 170, 255, 0.3);
      z-index: 1;
      width: 100%;
      max-width: 400px;
    }

    .text-glow {
      color: #00ccff;
      text-shadow: 0 0 5px #00ccff, 0 0 10px #00aaff;
    }

    .btn-glow {
      background-color: #00aaff;
      color: #fff;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      border-radius: 8px;
    }

    .btn-glow:hover {
      background-color: #0088cc;
    }

    .form-floating input:focus {
      border-color: #00aaff;
      box-shadow: 0 0 5px rgba(0, 170, 255, 0.6);
    }
  </style>
</head>
<body>

<canvas id="fondo-particulas"></canvas>

<div class="d-flex justify-content-center align-items-center vh-100 position-relative">
  <div class="login-card text-white">
    <div class="text-center mb-3">
      <img src="assets/img/logo.png" alt="Logo AdmiNET" style="height: 90px;">
    </div>
    <h2 class="text-center mb-4 text-glow">AdmiNET</h2>

    <?php if(isset($_SESSION['error'])): ?>
      <div class="alert alert-danger text-center"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="validar_login.php" method="POST">
      <div class="form-floating mb-3">
        <input type="text" name="usuario" class="form-control bg-dark text-white" id="floatingUser" placeholder="Usuario" required>
        <label for="floatingUser">Usuario</label>
      </div>
      <div class="form-floating mb-4">
        <input type="password" name="password" class="form-control bg-dark text-white" id="floatingPass" placeholder="Contraseña" required>
        <label for="floatingPass">Contraseña</label>
      </div>

      <!-- ✅ Checkbox dentro del formulario -->
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="recordar" id="recordarSesion">
        <label class="form-check-label" for="recordarSesion">
          Recordar sesión
        </label>
      </div>

      <button class="btn btn-glow w-100">Ingresar</button>
    </form>

  </div>
</div>

<div class="position-absolute bottom-0 w-100 text-center text-secondary pb-2 small z-1">
  &copy; <?= date('Y') ?> Software Escobedo. Desarrollado por Ricardo Escobedo.
</div>

<script src="assets/js/particles.js"></script>
</body>
</html>

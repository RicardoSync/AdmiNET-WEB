<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['empresa'])) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Logs de Sesión | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <!-- Contenido principal -->
  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary"><i class="bi bi-shield-lock-fill"></i> Logs de Sesión</h2>
    </div>

    <!-- Filtros -->
    <div class="row g-2 mb-3">
      <div class="col-md-3">
        <input type="text" id="filtroUsuario" class="form-control" placeholder="Buscar por usuario">
      </div>
      <div class="col-md-3">
        <input type="date" id="filtroDesde" class="form-control">
      </div>
      <div class="col-md-3">
        <input type="date" id="filtroHasta" class="form-control">
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary w-100" onclick="cargarLogs()">Buscar</button>
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>Usuario</th>
            <th>IP Pública</th>
            <th>Navegador</th>
            <th>Fecha y Hora</th>
            <th>User-Agent</th>
            
          </tr>
        </thead>
        <tbody id="tbodyLogs">
          <tr><td colspan="5">Cargando...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div id="paginacionLogs" class="mt-3 text-center"></div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let paginaActual = 1;

function cargarLogs(pagina = 1) {
  paginaActual = pagina;
  const usuario = document.getElementById("filtroUsuario").value;
  const desde = document.getElementById("filtroDesde").value;
  const hasta = document.getElementById("filtroHasta").value;

  fetch(`api/logs_fetch.php?pagina=${pagina}&usuario=${usuario}&desde=${desde}&hasta=${hasta}`)
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById("tbodyLogs");
      const paginacion = document.getElementById("paginacionLogs");

      tbody.innerHTML = data.html;
      paginacion.innerHTML = data.paginacion;
    })
    .catch(err => {
      console.error("Error al cargar logs:", err);
      document.getElementById("tbodyLogs").innerHTML = "<tr><td colspan='5'>Error al cargar datos.</td></tr>";
    });
}

document.addEventListener("DOMContentLoaded", () => {
  cargarLogs();
});
</script>
</body>
</html>

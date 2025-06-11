<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Localidades | AdmiNET</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/localidades.css">

  <style>
    @media (max-width: 768px) {
      .p-4 {
        padding: 1rem !important;
      }
      .table-responsive {
        font-size: 14px;
      }
      .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.5rem;
      }
    }
  </style>
</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h2 class="text-primary m-0"><i class="bi bi-geo-alt-fill me-2"></i>Localidades</h2>
      <a href="localidad_nueva.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Agregar Localidad
      </a>
    </div>

    <div class="mb-3">
      <input type="text" id="busquedaLocalidad" class="form-control" placeholder="Buscar por nombre, tipo o notas...">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaLocalidades">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Tipo de Zona</th>
            <th>Responsable de zona</th>
            <th>Notas</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Cargado por JS -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  fetch("api/localidades_get.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#tablaLocalidades tbody");
      const buscador = document.getElementById("busquedaLocalidad");

      function render(filtradas) {
        tbody.innerHTML = "";
        filtradas.forEach(loc => {
          const tr = document.createElement("tr");
            tr.innerHTML = `
              <td data-label="ID">${loc.idantenasAp}</td>
              <td data-label="Nombre">${loc.nombre}</td>
              <td data-label="Tipo de Zona">${loc.modelo || '-'}</td>
              <td data-label="Responsable de zona">${loc.usuario || '-'}</td>
              <td data-label="Notas">${loc.ip || '-'}</td>
              <td data-label="Acciones">
                <a href="localidad_editar.php?id=${loc.idantenasAp}" class="btn btn-warning btn-sm me-1">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <a href="localidad_eliminar.php?id=${loc.idantenasAp}" class="btn btn-danger btn-sm">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            `;

          tbody.appendChild(tr);
        });
      }

      function filtrar() {
        const texto = buscador.value.toLowerCase();
        const filtradas = data.filter(loc =>
          loc.nombre.toLowerCase().includes(texto) ||
          (loc.modelo && loc.modelo.toLowerCase().includes(texto)) ||
          (loc.ip && loc.ip.toLowerCase().includes(texto))
        );
        render(filtradas);
      }

      buscador.addEventListener("input", filtrar);
      render(data);
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

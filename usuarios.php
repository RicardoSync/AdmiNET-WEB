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
  <title>Usuarios | AdmiNET</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">


  
</head>
<body>
<div class="d-flex" id="wrapper">
  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">Usuarios</h2>
      <a href="usuario_nuevo.php" class="btn btn-success">
        <i class="bi bi-person-plus-fill"></i> Agregar Usuario
      </a>
    </div>

    <div class="mb-3">
      <input type="text" id="busquedaUsuario" class="form-control" placeholder="Buscar por nombre o usuario...">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaUsuarios">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Rol</th>
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
  fetch("api/usuarios_get.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#tablaUsuarios tbody");
      const buscador = document.getElementById("busquedaUsuario");

      function getRolTexto(rol) {
        rol = parseInt(rol);  // 🔧 Solución clave
        switch (rol) {
          case 0: return "Administrador";
          case 1: return "Técnico";
          case 2: return "Cliente";
          default: return "Desconocido";
        }
      }


      function render(filtrados) {
        tbody.innerHTML = "";
        filtrados.forEach(usuario => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${usuario.id}</td>
            <td>${usuario.nombre}</td>
            <td>${usuario.usuario}</td>
            <td>${getRolTexto(usuario.rol)}</td>
            <td>
              <a href="usuario_editar.php?id=${usuario.id}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil-square"></i>
              </a>
              <a href="usuario_eliminar.php?id=${usuario.id}" class="btn btn-danger btn-sm">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }

      function filtrar() {
        const texto = buscador.value.toLowerCase();
        const filtrados = data.filter(u =>
          u.nombre.toLowerCase().includes(texto) ||
          u.usuario.toLowerCase().includes(texto)
        );
        render(filtrados);
      }

      buscador.addEventListener("input", filtrar);
      render(data);
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

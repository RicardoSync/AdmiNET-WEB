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
  <title>Adeudos | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

</head>
<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-danger">Clientes con Adeudos</h2>
    </div>

    <!-- Filtros -->
    <div class="row g-2 mb-3">
      <div class="col-md-3">
        <input type="text" id="filtroNombre" class="form-control" placeholder="Buscar nombre">
      </div>
      <div class="col-md-3">
        <input type="number" id="filtroAdeudo" class="form-control" placeholder="Adeudo > $">
      </div>
      <div class="col-md-3">
        <select id="filtroEstado" class="form-select">
          <option value="">Todos</option>
          <option value="Activo">Activo</option>
          <option value="Bloqueado">Bloqueado</option>
        </select>
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center" id="tablaAdeudos">
        <thead class="table-dark">
          <tr>
            <th>Cliente</th>
            <th>Último Pago</th>
            <th>Próximo Pago</th>
            <th>Meses Adeudados</th>
            <th>Total Adeudado</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <!-- Se llena con JS -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>


  <style>

  @media screen and (max-width: 768px) {
    #tablaAdeudos thead {
      display: none;
    }

    #tablaAdeudos tbody tr {
      display: block;
      margin-bottom: 1rem;
      border: 1px solid #ddd;
      border-radius: 10px;
      background-color: white;
      padding: 0.75rem;
    }

    #tablaAdeudos tbody td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: none;
      border-bottom: 1px solid #eee;
      padding: 0.4rem 0;
      font-size: 0.95rem;
    }

    #tablaAdeudos tbody td:last-child {
      border-bottom: none;
    }

    #tablaAdeudos tbody td::before {
      content: attr(data-label);
      font-weight: bold;
      color: #555;
    }
  }


  </style>
<script>
let tabla, datos = [];

function renderTabla(filtrados) {
  if (tabla) tabla.clear().destroy();

  const tbody = document.querySelector('#tablaAdeudos tbody');
  tbody.innerHTML = '';

  filtrados.forEach(c => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td data-label="Cliente">${c.nombre}</td>
      <td data-label="Último Pago">${c.ultimo_pago}</td>
      <td data-label="Próximo Pago">${c.proximo_pago}</td>
      <td data-label="Meses Adeudados">${c.meses_adeudo}</td>
      <td data-label="Total Adeudado">$${parseFloat(c.total_adeudo).toFixed(2)}</td>
      <td data-label="Estado"><span class="badge bg-${c.estado === 'Bloqueado' ? 'danger' : 'success'}">${c.estado}</span></td>
    `;

    tbody.appendChild(tr);
  });

  tabla = $('#tablaAdeudos').DataTable({
    order: [[3, 'desc']],
     dom: 'lrtip',
    language: {
      url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
    }
  });
}

function aplicarFiltros() {
  const nombre = document.getElementById('filtroNombre').value.toLowerCase();
  const adeudo = parseFloat(document.getElementById('filtroAdeudo').value) || 0;
  const estado = document.getElementById('filtroEstado').value;

  const filtrados = datos.filter(c => {
    return (
      (!nombre || c.nombre.toLowerCase().includes(nombre)) &&
      (parseFloat(c.total_adeudo) >= adeudo) &&
      (!estado || c.estado === estado)
    );
  });

  renderTabla(filtrados);
}

fetch('/ded/api/adeudos_get.php')
  .then(res => res.json())
  .then(json => {
    datos = json;
    renderTabla(datos);
  });

['filtroNombre', 'filtroAdeudo', 'filtroEstado'].forEach(id => {
  document.getElementById(id).addEventListener('input', aplicarFiltros);
});
</script>
</body>
</html>

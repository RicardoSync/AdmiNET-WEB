<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}


require_once 'includes/db.php';

$stmt = $conn->query("
SELECT 
  c.id,  -- 👈 necesario
  c.nombre,
  c.ubicacion_maps,
  c.direccion,
  c.telefono,
  c.tipo_conexion,
  c.ip_cliente AS ip,
  c.dia_corte,
  c.estado,
  z.nombre AS zona,
  m.nombre AS mikrotik
FROM clientes c
LEFT JOIN antenasap z ON c.id_antena_ap = z.idantenasAp
LEFT JOIN credenciales_microtik m ON c.id_microtik = m.id
WHERE c.ubicacion_maps IS NOT NULL AND c.ubicacion_maps <> ''
");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mapa de Clientes | AdmiNET</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Estilos base -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <style>
    #map {
      height: 85vh;
      width: 100%;
      border-radius: 10px;
    }
  </style>
</head>
<body>
<div class="d-flex" id="wrapper">

  <?php include("includes/sidebar.php"); ?>

  <!-- Contenido principal -->
  <div id="page-content-wrapper" class="w-100 bg-dark text-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">📍 Mapa de Clientes</h2>

    <!-- Agrega esto debajo de <h2 class="text-white"> -->
    <div class="d-flex gap-3 mb-2">
      <label for="filtroEstado" class="form-label text-white">Filtrar por estado:</label>
      <select id="filtroEstado" class="form-select form-select-sm w-auto">
        <option value="">Todos</option>
        <option value="Activo">Activo</option>
        <option value="Suspendido">Suspendido</option>
        <option value="Bloqueado">Bloqueado</option>
        <option value="Cancelado">Cancelado</option>
      </select>
    </div>

    </div>

    <div id="map" class="shadow-sm border border-secondary"></div>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>



<script>
const callejero = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap'
});
const satelite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
  attribution: 'Tiles &copy; Esri'
});

const map = L.map('map', { layers: [satelite] });
L.control.layers({ "Satélite": satelite, "Callejero": callejero }).addTo(map);

const iconoCliente = L.icon({
  iconUrl: 'assets/img/pin_perro.png',
  iconSize: [60, 70],
  iconAnchor: [30, 70],
  popupAnchor: [0, -70]
});

const clientes = <?= json_encode($clientes); ?>;
const bounds = L.latLngBounds();
const marcadores = [];
const lineas = [];

// Para selección manual de puntos
let seleccionados = [];
let lineasUsuario = [];

function seleccionarPunto(latlng, nombre) {
  seleccionados.push({ latlng, nombre });

  if (seleccionados.length >= 2) {
    const puntos = seleccionados.map(p => p.latlng);
    const totalDistancia = puntos.reduce((acc, cur, i, arr) => {
      if (i === 0) return 0;
      return acc + map.distance(arr[i - 1], cur);
    }, 0);

    const linea = L.polyline(puntos, { color: 'lime', weight: 3 })
      .bindTooltip(`
        📏 ${(totalDistancia / 1000).toFixed(2)} km<br>
        📐 ${totalDistancia.toFixed(2)} metros
      `, {
        permanent: true,
        direction: 'center',
        className: 'bg-dark text-white px-2 rounded text-center'
      })

      .addTo(map);

    lineasUsuario.push(linea);
  }
}

// Reset selección al hacer doble clic
map.on('dblclick', () => {
  seleccionados = [];
  lineasUsuario.forEach(l => map.removeLayer(l));
  lineasUsuario = [];
});

function agregarMarcadores(filtroEstado = "") {
  marcadores.forEach(m => map.removeLayer(m));
  lineasUsuario.forEach(l => map.removeLayer(l));
  marcadores.length = 0;
  lineasUsuario.length = 0;
  seleccionados = [];
  bounds._northEast = bounds._southWest = null;

  clientes.forEach((cliente) => {
    if (filtroEstado && cliente.estado !== filtroEstado) return;

    const coords = cliente.ubicacion_maps.split(',');
    if (coords.length !== 2) return;

    const lat = parseFloat(coords[0]);
    const lng = parseFloat(coords[1]);

    const popup = `
      <b>📛 ${cliente.nombre}</b><br>
      📍 <b>Dirección:</b> ${cliente.direccion}<br>
      ☎️ <b>Teléfono:</b> ${cliente.telefono}<br>
      🔌 <b>Conexión:</b> ${cliente.tipo_conexion}<br>
      🌐 <b>IP:</b> ${cliente.ip}<br>
      📡 <b>Zona:</b> ${cliente.zona}<br>
      🛠️ <b>MikroTik:</b> ${cliente.mikrotik}<br>
      🗓️ <b>Día de corte:</b> ${cliente.dia_corte}<br>
      📶 <b>Estado:</b> <span class="${cliente.estado === 'Activo' ? 'text-success' : 'text-warning'}">${cliente.estado}</span><br>
    `;

    const marker = L.marker([lat, lng], { icon: iconoCliente })
      .bindPopup(popup)
      .addTo(map)
      .on('click', () => seleccionarPunto([lat, lng], cliente.nombre));

    marcadores.push(marker);
    bounds.extend(marker.getLatLng());
  });

  if (marcadores.length > 0) {
    map.fitBounds(bounds);
  }
}


// Filtro por estado
document.getElementById("filtroEstado").addEventListener("change", e => {
  agregarMarcadores(e.target.value);
});

agregarMarcadores(); // inicial
</script>



</body>
</html>

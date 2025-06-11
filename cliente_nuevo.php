<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once 'includes/db.php';

$zonas = $conn->query("SELECT idantenasAp, nombre FROM antenasap")->fetchAll(PDO::FETCH_ASSOC);
$paquetes = $conn->query("SELECT id, nombre FROM paquetes")->fetchAll(PDO::FETCH_ASSOC);
$plataformas = $conn->query("SELECT idPlataformas, nombre FROM serviciosplataforma")->fetchAll(PDO::FETCH_ASSOC);
$mikrotiks = $conn->query("SELECT id, nombre FROM credenciales_microtik")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nuevo Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">
  <?php include 'includes/sidebar.php'; ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="card shadow-lg border-0 mx-auto" style="max-width: 1000px;">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <div class="avatar">NC</div>
          <h4 class="mb-0 ms-2">Registrar Nuevo Cliente</h4>
        </div>
        <a href="clientes.php" class="btn btn-light btn-sm">Volver</a>
      </div>
      <div class="card-body p-4">

        <?php if (isset($_SESSION['msg_error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['msg_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
          </div>
          <?php unset($_SESSION['msg_error']); ?>
        <?php endif; ?>

        <form action="api/cliente_add.php" method="POST">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">IP Cliente</label>
              <input type="text" name="ip_cliente" class="form-control" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control">
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Día de Corte</label>
              <input type="number" name="dia_corte" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Estado</label>
              <select name="estado" class="form-select">
                <option value="Activo">Activo</option>
                <option value="Bloqueado">Bloqueado</option>
                <option value="Suspendido">Suspendido</option>
                <option value="Cancelado">Cancelado</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tipo de Conexión</label>
              <select name="tipo_conexion" class="form-select">
                <option value="0">Fibra</option>
                <option value="1">Antena</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Zona</label>
              <select name="id_antena_ap" class="form-select">
                <?php foreach ($zonas as $zona): ?>
                  <option value="<?= $zona['idantenasAp'] ?>"><?= $zona['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Plataforma</label>
              <select name="id_servicio_plataforma" class="form-select">
                <option value="">-- Sin plataforma --</option>
                <?php foreach ($plataformas as $plataforma): ?>
                  <option value="<?= $plataforma['idPlataformas'] ?>"><?= $plataforma['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Paquete</label>
              <select name="id_paquete" class="form-select" required>
                <option value="">Selecciona un paquete</option>
                <?php foreach ($paquetes as $paquete): ?>
                  <option value="<?= $paquete['id'] ?>"><?= $paquete['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">MikroTik</label>
              <select name="id_microtik" class="form-select" required>
                <option value="">Selecciona un MikroTik</option>
                <?php foreach ($mikrotiks as $mk): ?>
                  <option value="<?= $mk['id'] ?>"><?= $mk['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Ubicación</label>
            <input type="text" id="ubicacion_maps" name="ubicacion_maps" class="form-control mb-2" readonly>
            <div id="map" style="height: 300px; border: 1px solid #ccc; border-radius: 8px;"></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Buscar Ubicación</label>
            <input type="text" id="search" class="form-control mb-2" placeholder="Buscar ubicación por nombre (Ej. Tierra Blanca, Loreto)">
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-primary">Guardar Cliente</button>
            <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  const map = L.map('map').setView([22.2689, -101.9824], 13);
  const satelite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri'
  }).addTo(map);

  let marcador = null;
  const ubicacionInput = document.getElementById('ubicacion_maps');

  map.on('click', function (e) {
    const { lat, lng } = e.latlng;
    if (marcador) {
      marcador.setLatLng(e.latlng);
    } else {
      marcador = L.marker(e.latlng).addTo(map);
    }
    ubicacionInput.value = `${lat.toFixed(6)},${lng.toFixed(6)}`;
  });

  document.getElementById('search').addEventListener('input', function () {
    const address = this.value;
    if (address.length > 3) {
      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${address}`)
        .then(response => response.json())
        .then(data => {
          if (data.length > 0) {
            const { lat, lon } = data[0];
            map.setView([lat, lon], 13);
            if (marcador) {
              marcador.setLatLng([lat, lon]);
            } else {
              marcador = L.marker([lat, lon]).addTo(map);
            }
            ubicacionInput.value = `${parseFloat(lat).toFixed(6)},${parseFloat(lon).toFixed(6)}`;
          }
        })
        .catch(error => console.error('Error al geocodificar:', error));
    }
  });


  // Solo si no hay ubicación guardada, obtener ubicación del navegador
if (!ubicacionInput.value || !ubicacionInput.value.includes(",")) {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        map.setView([lat, lng], 15);
        marcador = L.marker([lat, lng]).addTo(map);
        ubicacionInput.value = `${lat.toFixed(6)},${lng.toFixed(6)}`;
      },
      function (error) {
        console.warn("Geolocalización no permitida o falló.", error);
      }
    );
  } else {
    console.warn("Geolocalización no compatible con este navegador.");
  }
}



</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

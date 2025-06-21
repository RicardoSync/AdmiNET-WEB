<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once 'includes/db.php';

if (!isset($_GET['id'])) {
  echo "<p style='color:red'>ID de cliente no especificado.</p>";
  exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
  echo "<p style='color:red'>Cliente no encontrado.</p>";
  exit;
}

$zonas = $conn->query("SELECT idantenasAp, nombre FROM antenasap")->fetchAll(PDO::FETCH_ASSOC);
$paquetes = $conn->query("SELECT id, nombre FROM paquetes")->fetchAll(PDO::FETCH_ASSOC);
$plataformas = $conn->query("SELECT idPlataformas, nombre FROM serviciosplataforma")->fetchAll(PDO::FETCH_ASSOC);
$mikrotiks = $conn->query("SELECT id, nombre FROM credenciales_microtik")->fetchAll(PDO::FETCH_ASSOC);

$pagos = $conn->prepare("SELECT monto, fecha_pago, proximo_pago FROM pagos WHERE id_cliente = ? ORDER BY fecha_pago DESC LIMIT 1");
$pagos->execute([$id]);
$ultimoPago = $pagos->fetch(PDO::FETCH_ASSOC);

// Obtener apikey actual
$stmtKey = $conn->prepare("SELECT apikey FROM clientes_apikeys WHERE id_cliente = ?");
$stmtKey->execute([$id]);
$clienteKey = $stmtKey->fetch(PDO::FETCH_ASSOC);
$apikey = $clienteKey ? $clienteKey['apikey'] : '';


function getIniciales($nombre) {
  $partes = explode(" ", $nombre);
  $iniciales = '';
  foreach ($partes as $p) {
    if (strlen($p) > 0) $iniciales .= strtoupper($p[0]);
  }
  return substr($iniciales, 0, 2);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Cliente</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    @media (max-width: 768px) {
      .btn {
        font-size: 0.9rem;
      }
      .form-control, .form-select {
        font-size: 14px;
      }
    }
  </style>
</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">
  <?php include 'includes/sidebar.php'; ?>

  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="card shadow-lg border-0 mx-auto" style="max-width: 1200px;">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <div class="avatar"><?= getIniciales($cliente['nombre']) ?></div>
          <h4 class="mb-0 ms-2">Editar Cliente</h4>
        </div>
        <a href="clientes.php" class="btn btn-light btn-sm">Volver</a>
      </div>
      <div class="card-body p-4">
        <div class="row">
          <div class="col-md-8">
            <form action="api/cliente_update.php" method="POST">
              <input type="hidden" name="id" value="<?= $cliente['id'] ?>">

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Nombre</label>
                  <input type="text" name="nombre" class="form-control" value="<?= $cliente['nombre'] ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Teléfono</label>
                  <input type="text" name="telefono" class="form-control" value="<?= $cliente['telefono'] ?>">
                </div>

                <div class="col-md-6">
                  <label class="form-label">API Key de CallMeBot</label>
                  <div class="input-group">
                    <input type="text" name="apikey" id="apikey" class="form-control" value="<?= htmlspecialchars($apikey) ?>" placeholder="Ingresa tu API Key">
                    <button class="btn btn-outline-success" type="button" onclick="probarAPIKey()">Probar</button>
                  </div>
                  <small id="mensajeApiKey" class="text-muted mt-1 d-block"></small>
                </div>

              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" value="<?= $cliente['email'] ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">IP Cliente</label>
                  <input type="text" name="ip_cliente" class="form-control" value="<?= $cliente['ip_cliente'] ?>" readonly>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control" value="<?= $cliente['direccion'] ?>">
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="form-label">Día de Corte</label>
                  <input type="number" name="dia_corte" class="form-control" value="<?= $cliente['dia_corte'] ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Estado</label>
                  <select name="estado" class="form-select">
                    <?php foreach (["Activo", "Bloqueado", "Suspendido", "Cancelado"] as $estado): ?>
                      <option value="<?= $estado ?>" <?= $cliente['estado'] == $estado ? 'selected' : '' ?>><?= $estado ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Tipo de Conexión</label>
                  <select name="tipo_conexion" class="form-select">
                    <option value="0" <?= $cliente['tipo_conexion'] == 0 ? 'selected' : '' ?>>Fibra</option>
                    <option value="1" <?= $cliente['tipo_conexion'] == 1 ? 'selected' : '' ?>>Antena</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Zona</label>
                  <select name="id_antena_ap" class="form-select">
                    <?php foreach ($zonas as $zona): ?>
                      <option value="<?= $zona['idantenasAp'] ?>" <?= $cliente['id_antena_ap'] == $zona['idantenasAp'] ? 'selected' : '' ?>><?= $zona['nombre'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Plataforma</label>
                  <select name="id_servicio_plataforma" class="form-select">
                    <option value="">-- Sin plataforma --</option>
                    <?php foreach ($plataformas as $plataforma): ?>
                      <option value="<?= $plataforma['idPlataformas'] ?>" <?= $cliente['id_servicio_plataforma'] == $plataforma['idPlataformas'] ? 'selected' : '' ?>><?= $plataforma['nombre'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Paquete</label>
                  <select name="id_paquete" class="form-select">
                    <?php foreach ($paquetes as $paquete): ?>
                      <option value="<?= $paquete['id'] ?>" <?= $cliente['id_paquete'] == $paquete['id'] ? 'selected' : '' ?>><?= $paquete['nombre'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">MikroTik</label>
                  <select name="id_microtik" class="form-select">
                    <?php foreach ($mikrotiks as $mk): ?>
                      <option value="<?= $mk['id'] ?>" <?= $cliente['id_microtik'] == $mk['id'] ? 'selected' : '' ?>><?= $mk['nombre'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Ubicación</label>
                <input type="text" id="ubicacion_maps" name="ubicacion_maps" class="form-control mb-2" value="<?= $cliente['ubicacion_maps'] ?>">
                <div id="map" style="height: 300px; border: 1px solid #ccc; border-radius: 8px;"></div>
              </div>

              <div class="text-end">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
              </div>
            </form>
          </div>

          <div class="col-md-4">
            <div class="bg-light p-3 rounded border">
              <h6 class="text-muted mb-3">📌 Información rápida</h6>
              <p><strong>Estado:</strong> <?= $cliente['estado'] ?></p>
              <p><strong>Día de corte:</strong> <?= $cliente['dia_corte'] ?></p>
              <p><strong>Último pago:</strong><br>
                <?= $ultimoPago ? date('Y-m-d', strtotime($ultimoPago['fecha_pago'])) . " ($" . number_format($ultimoPago['monto'], 2) . ")" : 'No registrado' ?></p>
              <p><strong>Próximo pago:</strong><br>
                <?= $ultimoPago && $ultimoPago['proximo_pago'] ? date('Y-m-d', strtotime($ultimoPago['proximo_pago'])) : 'No disponible' ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const map = L.map('map').setView([22.2689, -101.9824], 13);
const tile = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
  attribution: 'Tiles © Esri'
}).addTo(map);

let marcador = null;
const ubicacionInput = document.getElementById('ubicacion_maps');

if (ubicacionInput.value && ubicacionInput.value.includes(",")) {
  const [lat, lng] = ubicacionInput.value.split(',').map(parseFloat);
  if (!isNaN(lat) && !isNaN(lng)) {
    marcador = L.marker([lat, lng]).addTo(map);
    map.setView([lat, lng], 15);
  }
}



map.on('click', function (e) {
  const { lat, lng } = e.latlng;
  if (marcador) {
    marcador.setLatLng(e.latlng);
  } else {
    marcador = L.marker(e.latlng).addTo(map);
  }
  ubicacionInput.value = `${lat.toFixed(6)},${lng.toFixed(6)}`;
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

<script>
function probarAPIKey() {
  const telefono = document.querySelector('input[name="telefono"]').value.trim();
  const apikey = document.getElementById('apikey').value.trim();
  const mensajeApiKey = document.getElementById('mensajeApiKey');

  mensajeApiKey.innerText = "Enviando prueba...";

  fetch(`/ded/api/test_callmebot.php?phone=${telefono}&apikey=${apikey}`)
    .then(res => res.text())
  .then(data => {
    if (data.toLowerCase().includes("message queued")) {
      mensajeApiKey.innerText = "✅ Mensaje enviado correctamente.";
      mensajeApiKey.classList.remove("text-danger");
      mensajeApiKey.classList.add("text-success");
    } else {
      mensajeApiKey.innerText = "❌ Error: " + data.replace(/<[^>]*>?/gm, '');
      mensajeApiKey.classList.remove("text-success");
      mensajeApiKey.classList.add("text-danger");
    }
  })

    .catch(err => {
      mensajeApiKey.innerText = "❌ Error al conectar con el servidor.";
      mensajeApiKey.classList.remove("text-success");
      mensajeApiKey.classList.add("text-danger");
    });
}

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}
require_once 'includes/db.php';
$clientes = $conn->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrar Pago</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    @media (max-width: 768px) {
      .btn { font-size: 0.9rem; padding: 0.5rem 0.75rem; }
      .form-control, .form-select { font-size: 14px; }
    }
  </style>
</head>
<body class="bg-light">
<div class="d-flex" id="wrapper">
  <?php include 'includes/sidebar.php'; ?>
  <div id="page-content-wrapper" class="w-100 bg-light p-4">
    <div class="card shadow-lg mx-auto" style="max-width: 700px;">
      <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Registrar Pago</h4>
        <a href="pagos.php" class="btn btn-light btn-sm">Volver</a>
      </div>
      <div class="card-body">
        <form action="api/pago_add.php" method="POST" target="_blank">
          <div class="mb-3">
            <label class="form-label">Seleccionar Cliente</label>
            <?php $id_cliente_preseleccionado = $_GET['id'] ?? ''; ?>
            <select name="id_cliente" id="id_cliente" class="form-select" required>
              <option value="">-- Selecciona --</option>
              <?php foreach ($clientes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $id_cliente_preseleccionado == $c['id'] ? 'selected' : '' ?>>
                  <?= $c['nombre'] ?>
                </option>
              <?php endforeach; ?>
            </select>

          </div>

          <div id="infoCliente" class="bg-light border p-3 rounded mb-3 d-none">
            <p><strong>Paquete:</strong> <span id="paquete"></span></p>
            <p><strong>Precio paquete:</strong> $<span id="precio"></span></p>
            <p><strong>Servicio adicional:</strong> <span id="servicio"></span></p>
            <p><strong>Precio servicio:</strong> $<span id="precio_servicio"></span></p>
            <p><strong>Total sin descuento:</strong> $<span id="total_sin_descuento"></span></p>
            <p><strong>Fecha actual:</strong> <span id="fechaPago"></span></p>
            <p><strong>Próximo pago:</strong> <span id="proximoPago"></span></p>

            <div id="resumenDeuda" class="alert alert-warning mt-3 d-none" role="alert" style="font-size: 14px;">
            <!-- Se llenará dinámicamente con JS -->
          </div>

          </div>

          <div class="mb-3">
            <label class="form-label">Meses Pagados</label>
            <select name="meses_pagados" id="meses_pagados" class="form-select">
              <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> mes<?= $i > 1 ? 'es' : '' ?></option>
              <?php endfor; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Fecha base del pago (inicio de deuda)</label>
            <input type="date" class="form-control" name="fecha_base" id="fecha_base" required>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Descuento</label>
              <input type="number" step="0.01" name="descuento" id="descuento" class="form-control" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Cantidad Pagada</label>
              <input type="number" step="0.01" name="cantidad" id="cantidad" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Cambio</label>
              <input type="text" id="cambio" class="form-control" disabled>
            </div>
          </div>

          <input type="hidden" name="monto_total" id="monto_total">

          <div class="mb-3">
            <label class="form-label">Método de pago</label>
            <select name="metodo_pago" class="form-select" required>
              <option value="Efectivo">Efectivo</option>
              <option value="Transferencia">Transferencia</option>
              <option value="Tarjeta">Tarjeta</option>
            </select>
          </div>

          <button type="submit" class="btn btn-success">Guardar y Generar Recibo</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function calcularProximoPagoDesdeBase(fechaBase, meses, diaCorte) {
  const base = new Date(fechaBase);
  base.setDate(1); // forzar inicio de mes
  base.setMonth(base.getMonth() + meses);

  const anio = base.getFullYear();
  const mes = base.getMonth(); // 0-indexed
  const ultimoDiaMes = new Date(anio, mes + 1, 0).getDate();
  const diaFinal = Math.min(diaCorte, ultimoDiaMes);

  const resultado = new Date(anio, mes, diaFinal);
  return resultado.toISOString().split("T")[0];
}

document.getElementById("id_cliente").addEventListener("change", function () {
  const id = this.value;
  if (!id) return;

  fetch(`api/pago_info_cliente.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
      const paquete = parseFloat(data.precio);
      const servicio = parseFloat(data.precio_servicio);
      const total = paquete + servicio;

      const fechaProximo = new Date(data.proximo_pago);
      const fechaHoy = new Date();
      const diffMeses = (fechaHoy.getFullYear() - fechaProximo.getFullYear()) * 12 + (fechaHoy.getMonth() - fechaProximo.getMonth());

      const resumenDiv = document.getElementById("resumenDeuda");
      if (diffMeses > 0) {
        const mes = fechaProximo.toLocaleString('es-MX', { month: 'long' });
        const anio = fechaProximo.getFullYear();
        resumenDiv.innerHTML = `🔎 Este cliente tiene <strong>${diffMeses}</strong> mes${diffMeses > 1 ? 'es' : ''} de atraso desde <strong>${mes} de ${anio}</strong>.`;
        resumenDiv.classList.remove("d-none");
      } else {
        resumenDiv.classList.add("d-none");
      }

      document.getElementById("fecha_base").value = data.fecha_pago;
      document.getElementById("infoCliente").classList.remove("d-none");
      document.getElementById("paquete").textContent = data.paquete;
      document.getElementById("precio").textContent = paquete.toFixed(2);
      document.getElementById("servicio").textContent = data.servicio;
      document.getElementById("precio_servicio").textContent = servicio.toFixed(2);
      document.getElementById("total_sin_descuento").textContent = total.toFixed(2);
      document.getElementById("fechaPago").textContent = data.fecha_pago;

      const calcularProximo = () => {
        const fechaBase = document.getElementById("fecha_base").value;
        const mesesPagados = parseInt(document.getElementById("meses_pagados").value) || 1;
        const diaCorte = parseInt(data.dia_corte) || 31;
        const proximo = calcularProximoPagoDesdeBase(fechaBase, mesesPagados, diaCorte);
        document.getElementById("proximoPago").textContent = proximo;
      };

      calcularProximo();

      function calcularCambio() {
        const descuento = parseFloat(document.getElementById("descuento").value) || 0;
        const pagado = parseFloat(document.getElementById("cantidad").value) || 0;
        const meses = parseInt(document.getElementById("meses_pagados").value) || 1;
        const total_con_meses = total * meses;
        const total_final = total_con_meses - descuento;

        document.getElementById("cambio").value = "$" + (pagado - total_final).toFixed(2);
        document.getElementById("monto_total").value = total_final.toFixed(2);
      }

      ["descuento", "cantidad", "meses_pagados", "fecha_base"].forEach(id => {
        document.getElementById(id).addEventListener("input", () => {
          calcularCambio();
          calcularProximo();
        });
      });


      calcularCambio();
    });
});

document.addEventListener("DOMContentLoaded", () => {
  const clienteId = "<?= $id_cliente_preseleccionado ?>";
  if (clienteId) {
    const select = document.getElementById("id_cliente");
    select.value = clienteId;
    select.dispatchEvent(new Event("change"));
  }
});
</script>



</body>
</html>

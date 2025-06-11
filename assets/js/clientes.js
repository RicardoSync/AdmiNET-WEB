let paginaActual = 1;

document.addEventListener("DOMContentLoaded", () => {
  cargarClientes();

  // Eventos para filtros
  ["busquedaCliente", "filtroOrden", "filtroTipoConexion", "filtroDiaCorte", "filtroMikrotik", "filtroZona", "cantidadPorPagina"]
    .forEach(id => {
      document.getElementById(id).addEventListener("input", () => {
        paginaActual = 1;
        cargarClientes();
      });
    });
});

function cargarClientes(pagina = 1) {
  paginaActual = pagina;

  const datos = {
    busqueda: document.getElementById("busquedaCliente").value,
    orden: document.getElementById("filtroOrden").value,
    tipo_conexion: document.getElementById("filtroTipoConexion").value,
    dia_corte: document.getElementById("filtroDiaCorte").value,
    mikrotik: document.getElementById("filtroMikrotik").value,
    zona: document.getElementById("filtroZona").value,
    cantidad: document.getElementById("cantidadPorPagina").value,
    pagina: pagina
  };

  fetch('api/filtrar_clientes.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datos)
  })
  .then(res => res.json())
  .then(data => {
    document.querySelector("#tablaClientes tbody").innerHTML = data.html;

    if (data.total_paginas) {
      renderPaginacion(data.total_paginas);
    }
  })
  .catch(err => {
    console.error("Error al cargar clientes:", err);
  });
}

function renderPaginacion(totalPaginas) {
  const pagContainer = document.getElementById("paginacionClientes");
  pagContainer.innerHTML = "";

  for (let i = 1; i <= totalPaginas; i++) {
    const btn = document.createElement("button");
    btn.className = `btn btn-sm ${i === paginaActual ? 'btn-primary' : 'btn-outline-primary'} me-1`;
    btn.textContent = i;
    btn.addEventListener("click", () => cargarClientes(i));
    pagContainer.appendChild(btn);
  }
}

function getColorEstado(estado) {
  switch (estado) {
    case "Activo": return "success";
    case "Bloqueado": return "warning";
    case "Suspendido": return "danger";
    case "Cancelado": return "secondary";
    default: return "light";
  }
}

document.addEventListener("click", e => {
  const eliminar = e.target.closest(".btn-eliminar");
  const acciones = e.target.closest(".btn-acciones");

  if (eliminar) {
    const id = eliminar.dataset.id;
    if (confirm("¿Eliminar este cliente?")) {
      fetch(`api/cliente_delete.php?id=${id}`, { method: "GET" })
        .then(res => res.text())
        .then(msg => {
          alert(msg);
          cargarClientes(paginaActual);
        })
        .catch(err => alert("Error al eliminar: " + err));
    }
  }

  if (acciones) {
    const id = acciones.dataset.id;
    const ip = acciones.dataset.ip;
    const nombre = acciones.dataset.nombre;
    document.getElementById("modalClienteId").value = id;
    document.getElementById("modalClienteIP").value = ip;
    document.getElementById("modalClienteNombre").textContent = nombre;

    const modal = new bootstrap.Modal(document.getElementById("modalAccionesCliente"));
    modal.show();
  }

  const detalles = e.target.closest(".btn-detalles");
  if (detalles) {
    const id = detalles.dataset.id;
    const modal = new bootstrap.Modal(document.getElementById("modalDetallesCliente"));
    const contenedor = document.getElementById("contenidoDetallesCliente");

    contenedor.innerHTML = `<p class="text-center text-muted">Cargando información...</p>`;
    modal.show();

    fetch(`api/cliente_detalles.php?id=${id}`)
      .then(res => res.text())
      .then(html => {
        contenedor.innerHTML = html;
      })
      .catch(err => {
        contenedor.innerHTML = `<div class="alert alert-danger">Error al cargar detalles.</div>`;
        console.error(err);
      });
  }

});

// ======================= SUSPENDER Y ACTIVAR =======================
document.getElementById("btnSuspender").addEventListener("click", () => {
  const id = document.getElementById("modalClienteId").value;
  fetch("api/suspender_cliente.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + id
  })
  .then(res => res.text())
  .then(msg => {
    alert(msg);
    bootstrap.Modal.getInstance(document.getElementById("modalAccionesCliente")).hide();
    cargarClientes(paginaActual);
  });
});

document.getElementById("btnActivar").addEventListener("click", () => {
  const id = document.getElementById("modalClienteId").value;
  fetch("api/activar_cliente.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + id
  })
  .then(res => res.text())
  .then(msg => {
    alert(msg);
    bootstrap.Modal.getInstance(document.getElementById("modalAccionesCliente")).hide();
    cargarClientes(paginaActual);
  });
});

// ======================= MONITOR =======================
let chart = null;
let intervaloMonitor = null;

document.getElementById("btnMonitor").addEventListener("click", () => {
  const id = document.getElementById("modalClienteId").value;
  const modalElement = document.getElementById("modalMonitor");
  const modal = new bootstrap.Modal(modalElement);
  modal.show();

  modalElement.addEventListener("shown.bs.modal", () => {
    const ctx = document.getElementById('graficaTráfico').getContext('2d');

    // Gradientes de color para TX y RX
    const gradTX = ctx.createLinearGradient(0, 0, 0, 200);
    gradTX.addColorStop(0, 'rgba(255,99,132,0.4)');
    gradTX.addColorStop(1, 'rgba(255,99,132,0)');

    const gradRX = ctx.createLinearGradient(0, 0, 0, 200);
    gradRX.addColorStop(0, 'rgba(54,162,235,0.4)');
    gradRX.addColorStop(1, 'rgba(54,162,235,0)');

    if (chart) chart.destroy();

    chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [],
        datasets: [
          {
            label: 'Subida (TX)',
            data: [],
            borderColor: '#ff6384',
            backgroundColor: gradTX,
            fill: true,
            tension: 0.5,
            pointRadius: 2,
            pointHoverRadius: 5
          },
          {
            label: 'Bajada (RX)',
            data: [],
            borderColor: '#36a2eb',
            backgroundColor: gradRX,
            fill: true,
            tension: 0.5,
            pointRadius: 2,
            pointHoverRadius: 5
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 500,
          easing: 'easeOutQuart'
        },
        scales: {
          x: {
            ticks: { color: 'white' },
            grid: { color: 'rgba(255,255,255,0.1)' }
          },
          y: {
            ticks: {
              color: 'white',
              callback: valor => valor >= 1024
                ? (valor / 1024).toFixed(1) + ' Mbps'
                : valor + ' Kbps'
            },
            grid: { color: 'rgba(255,255,255,0.1)' }
          }
        },
        plugins: {
          legend: { labels: { color: 'white' } },
          tooltip: {
            callbacks: {
              label: ctx => {
                const val = ctx.parsed.y;
                const label = ctx.dataset.label;
                return val >= 1024
                  ? `${label}: ${(val / 1024).toFixed(2)} Mbps`
                  : `${label}: ${val} Kbps`;
              }
            }
          }
        }
      },
      plugins: ['ultimoPuntoResaltado'] // Activamos nuestro plugin
    });

    if (intervaloMonitor) clearInterval(intervaloMonitor);

    intervaloMonitor = setInterval(() => {
      fetch("api/monitor_cliente.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
      })
      .then(res => res.json())
      .then(data => {
        const hora = new Date().toLocaleTimeString();
        let tx = 0, rx = 0;

        const match = data.raw?.match(/rate=([\d.]+)([kMG]?bps)\/([\d.]+)([kMG]?bps)/);
        if (match) {
          const txVal = parseFloat(match[1]);
          const txUnit = match[2];
          const rxVal = parseFloat(match[3]);
          const rxUnit = match[4];
          tx = convertirAKbps(txVal, txUnit);
          rx = convertirAKbps(rxVal, rxUnit);
        }

        // Evitar cortes bruscos si 0 se repite
        const len = chart.data.labels.length;
        if (tx === 0 && rx === 0 &&
            chart.data.datasets[0].data[len - 1] === 0 &&
            chart.data.datasets[1].data[len - 1] === 0) {
          return;
        }

        chart.data.labels.push(hora);
        chart.data.datasets[0].data.push(tx);
        chart.data.datasets[1].data.push(rx);

        if (chart.data.labels.length > 20) {
          chart.data.labels.shift();
          chart.data.datasets[0].data.shift();
          chart.data.datasets[1].data.shift();
        }

        chart.update();
      });
    }, 2000);


  }, { once: true });

});

function convertirAKbps(valor, unidad = "bps") {
  if (unidad === "Gbps") return valor * 1024 * 1024;
  if (unidad === "Mbps") return valor * 1024;
  if (unidad === "kbps") return valor;
  if (unidad === "bps") return valor / 1024;
  return valor;
}



document.getElementById("modalMonitor").addEventListener("hidden.bs.modal", () => {
  if (intervaloMonitor) {
    clearInterval(intervaloMonitor);
    intervaloMonitor = null;
  }
});

// ======================= SUBIR A MIKROTIK =======================
document.getElementById("btnSubir").addEventListener("click", () => {
  const id = document.getElementById("modalClienteId").value;
  window.location.href = `api/subir_cliente.php?id=${id}`;
});

Chart.register({
  id: 'ultimoPuntoResaltado',
  afterDatasetsDraw(chart) {
    const ctx = chart.ctx;
    chart.data.datasets.forEach((dataset, i) => {
      const meta = chart.getDatasetMeta(i);
      const lastPoint = meta.data[meta.data.length - 1];
      if (lastPoint) {
        ctx.save();
        ctx.beginPath();
        ctx.arc(lastPoint.x, lastPoint.y, 6, 0, 2 * Math.PI);
        ctx.fillStyle = dataset.borderColor;
        ctx.shadowColor = dataset.borderColor;
        ctx.shadowBlur = 6;
        ctx.fill();
        ctx.restore();
      }
    });
  }
});


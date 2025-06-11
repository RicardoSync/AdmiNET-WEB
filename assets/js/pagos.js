let paginaActual = 1;

document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ pagos.js cargado");
  cargarPagos();

  const filtros = ["busquedaPago", "filtroDia", "filtroMes", "filtroAnio", "cantidadPorPagina"];

  filtros.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener("input", () => {
        paginaActual = 1;
        cargarPagos();
      });
    }
  });
});

function cargarPagos(pagina = 1) {
  paginaActual = pagina;

  const datos = {
    busqueda: document.getElementById("busquedaPago")?.value || '',
    dia: document.getElementById("filtroDia")?.value || '',
    mes: document.getElementById("filtroMes")?.value || '',
    anio: document.getElementById("filtroAnio")?.value || '',
    cantidad: document.getElementById("cantidadPorPagina")?.value || 10,
    pagina: pagina
  };

  console.log("📤 Enviando filtros:", datos);

  fetch('api/filtrar_pagos.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(datos)
  })
  .then(res => {
    if (!res.ok) throw new Error("Error al consultar el servidor");
    return res.json();
  })
  .then(data => {
    renderTabla(data.pagos || []);
    renderPaginacion(data.total_paginas || 1);
  })
  .catch(err => {
    console.error("❌ Error al cargar pagos:", err);
  });
}

function renderTabla(pagos) {
  const tbody = document.querySelector("#tablaPagos tbody");
  tbody.innerHTML = "";

  pagos.forEach(pago => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td data-label="ID">${pago.id}</td>
      <td data-label="Cliente">${pago.nombre}</td>
      <td data-label="Monto">$${parseFloat(pago.monto).toFixed(2)}</td>
      <td data-label="Fecha Pago">${pago.fecha_pago}</td>
      <td data-label="Método">${pago.metodo_pago}</td>
      <td data-label="Próximo Pago">${pago.proximo_pago || '-'}</td>
      <td data-label="Acciones">
        <button class="btn btn-info btn-sm btn-reimprimir" data-id="${pago.id}">
          <i class="bi bi-printer"></i> Reimprimir
        </button>
        <button class="btn btn-danger btn-sm btn-eliminar" data-id="${pago.id}">
          <i class="bi bi-trash"></i> Eliminar
        </button>
      </td>
    `;

    tbody.appendChild(tr);
  });

  // Activar los botones de reimprimir
  document.querySelectorAll(".btn-reimprimir").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      if (id) {
        window.open(`api/reimprimir_pdf.php?id=${id}`, '_blank');
      }
    });
  });

  // ✅ Activar los botones de eliminar
  document.querySelectorAll(".btn-eliminar").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      if (confirm("¿Estás seguro de eliminar este pago? Esta acción no se puede deshacer.")) {
        fetch(`api/eliminar_pago.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("✅ Pago eliminado correctamente.");
            cargarPagos(); // Recargar
          } else {
            alert("❌ Error al eliminar: " + data.message);
          }
        })
        .catch(err => {
          console.error("Error al eliminar el pago:", err);
          alert("❌ Error de conexión al servidor.");
        });
      }
    });
  });
}

function renderPaginacion(totalPaginas) {
  const pagContainer = document.getElementById("paginacionPagos");
  pagContainer.innerHTML = "";

  for (let i = 1; i <= totalPaginas; i++) {
    const btn = document.createElement("button");
    btn.className = `btn btn-sm ${i === paginaActual ? 'btn-primary' : 'btn-outline-primary'} me-1`;
    btn.textContent = i;
    btn.addEventListener("click", () => cargarPagos(i));
    pagContainer.appendChild(btn);
  }
}



// ✅ Archivo: assets/js/
let paginaActual = 1;

document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ tickets.js cargado");
  cargarFiltrosDinamicos();
  cargarTickets();

  const filtros = ["busquedaTicket", "filtroEstado", "filtroCategoria", "filtroResponsable", "cantidadPorPagina"];
  filtros.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener("input", () => {
        paginaActual = 1;
        cargarTickets();
      });
    }
  });
});

function cargarFiltrosDinamicos() {
  fetch("api/filtro_responsables.php")
    .then(res => res.json())
    .then(data => {
      const select = document.getElementById("filtroResponsable");
      select.innerHTML = '<option value="">Responsable</option>';
      data.forEach(user => {
        const opt = document.createElement("option");
        opt.value = user.id;
        opt.textContent = user.nombre;
        select.appendChild(opt);
      });
    });

  fetch("api/filtro_categorias.php")
    .then(res => res.json())
    .then(data => {
      const select = document.getElementById("filtroCategoria");
      select.innerHTML = '<option value="">Categoría</option>';
      data.forEach(cat => {
        const opt = document.createElement("option");
        opt.value = cat;
        opt.textContent = cat;
        select.appendChild(opt);
      });
    });
}

function cargarTickets(pagina = 1) {
  paginaActual = pagina;

  const datos = {
    busqueda: document.getElementById("busquedaTicket")?.value || '',
    estado: document.getElementById("filtroEstado")?.value || '',
    categoria: document.getElementById("filtroCategoria")?.value || '',
    responsable: document.getElementById("filtroResponsable")?.value || '',
    cantidad: document.getElementById("cantidadPorPagina")?.value || 10,
    pagina: pagina
  };

  fetch("api/filtrar_tickets.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(datos)
  })
    .then(res => res.json())
    .then(data => {
      renderTablaHTML(data.html);
      renderPaginacion(data.total_paginas);
    })
    .catch(err => {
      console.error("❌ Error al cargar tickets:", err);
    });
}

function renderTablaHTML(html) {
  const tbody = document.querySelector("#tablaTickets tbody");
  tbody.innerHTML = html || '<tr><td colspan="8">No se encontraron tickets.</td></tr>';
}

function renderPaginacion(totalPaginas) {
  const contenedor = document.getElementById("paginacionTickets");
  contenedor.innerHTML = "";

  for (let i = 1; i <= totalPaginas; i++) {
    const btn = document.createElement("button");
    btn.className = `btn btn-sm ${i === paginaActual ? 'btn-primary' : 'btn-outline-primary'} me-1`;
    btn.textContent = i;
    btn.addEventListener("click", () => cargarTickets(i));
    contenedor.appendChild(btn);
  }
}

function verEvidencia(ruta) {
  const imagen = document.getElementById("imagenEvidencia");
  imagen.src = ruta;
  const modal = new bootstrap.Modal(document.getElementById("modalEvidencia"));
  modal.show();
}

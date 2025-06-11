// Cargar marcas dinámicamente
fetch("api/marcas_get.php")
  .then(res => res.json())
  .then(marcas => {
    const select = document.getElementById("filtroMarca");
    select.innerHTML = '<option value="">Marca</option>';
    marcas.forEach(marca => {
      const opt = document.createElement("option");
      opt.value = marca;
      opt.textContent = marca;
      select.appendChild(opt);
    });
  })
  .catch(err => {
    console.error("❌ Error al cargar marcas:", err);
  });

  
let paginaActual = 1;

document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ equipos.js cargado");
  cargarEquipos();

  const filtros = ["busquedaEquipo", "filtroTipo", "filtroMarca", "filtroEstado", "cantidadPorPagina"];
  filtros.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener("input", () => {
        paginaActual = 1;
        cargarEquipos();
      });
    }
  });
});

function cargarEquipos(pagina = 1) {
  paginaActual = pagina;

  const datos = {
    busqueda: document.getElementById("busquedaEquipo")?.value || '',
    tipo: document.getElementById("filtroTipo")?.value || '',
    marca: document.getElementById("filtroMarca")?.value || '',
    estado: document.getElementById("filtroEstado")?.value || '',
    cantidad: document.getElementById("cantidadPorPagina")?.value || 10,
    pagina: pagina
  };

  fetch("api/filtrar_equipos.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(datos)
  })
    .then(res => res.json())
    .then(data => {
      renderTabla(data.equipos);
      renderPaginacion(data.total_paginas);
    })
    .catch(err => {
      console.error("❌ Error al cargar equipos:", err);
    });
}

function renderTabla(equipos) {
  const tbody = document.querySelector("#tablaEquipos tbody");
  tbody.innerHTML = "";

  if (equipos.length === 0) {
    tbody.innerHTML = "<tr><td colspan='8'>No se encontraron equipos con los filtros seleccionados.</td></tr>";
    return;
  }

  equipos.forEach(eq => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td data-label="ID">${eq.id}</td>
      <td data-label="Nombre">${eq.nombre}</td>
      <td data-label="Tipo">${eq.tipo}</td>
      <td data-label="Marca">${eq.marca || '-'}</td>
      <td data-label="MAC">${eq.mac || '-'}</td>
      <td data-label="Cliente">${eq.nombre_cliente || '-'}</td>
      <td data-label="Estado"><span class="badge bg-${colorEstado(eq.estado)}">${eq.estado}</span></td>
      <td data-label="Acciones">
        <a href="equipo_editar.php?id=${eq.id}" class="btn btn-sm btn-warning me-1">
          <i class="bi bi-pencil-square"></i>
        </a>
        <a href="api/equipo_eliminar.php?id=${eq.id}" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar equipo?')">
          <i class="bi bi-trash"></i>
        </a>
      </td>
    `;

    tbody.appendChild(tr);
  });
}

function renderPaginacion(totalPaginas) {
  const contenedor = document.getElementById("paginacionEquipos");
  contenedor.innerHTML = "";

  for (let i = 1; i <= totalPaginas; i++) {
    const btn = document.createElement("button");
    btn.className = `btn btn-sm ${i === paginaActual ? 'btn-primary' : 'btn-outline-primary'} me-1`;
    btn.textContent = i;
    btn.addEventListener("click", () => cargarEquipos(i));
    contenedor.appendChild(btn);
  }
}

function colorEstado(estado) {
  switch (estado) {
    case 'Rentado': return 'info';
    case 'Vendido': return 'success';
    case 'Propio': return 'secondary';
    case 'Almacenado': return 'dark';
    default: return 'light';
  }
}

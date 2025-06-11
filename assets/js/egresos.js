document.addEventListener("DOMContentLoaded", () => {
  cargarEgresos();

  document.getElementById("formAgregarEgreso").addEventListener("submit", async (e) => {
    e.preventDefault();

    const descripcion = document.getElementById("descripcion").value.trim();
    const monto = document.getElementById("monto").value.trim();
    const metodo = document.getElementById("metodo").value;

    if (!descripcion || !monto || !metodo) return alert("Todos los campos son obligatorios.");

    try {
      const res = await fetch("api/egresos_guardar.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ descripcion, monto, metodo })
      });
      const data = await res.json();

      if (data.status === "success") {
        document.getElementById("formAgregarEgreso").reset();
        const modal = bootstrap.Modal.getInstance(document.getElementById("modalAgregarEgreso"));
        modal.hide();
        cargarEgresos();
      } else {
        alert("Error: " + data.message);
      }
    } catch (err) {
      alert("Error al guardar el egreso.");
      console.error(err);
    }
  });

  document.getElementById("filtroMetodo").addEventListener("change", () => cargarEgresos());
  document.getElementById("filtroFecha").addEventListener("change", () => cargarEgresos());
});

let paginaActual = 1;
const porPagina = 10;

async function cargarEgresos(pagina = 1) {
  paginaActual = pagina;
  const metodo = document.getElementById("filtroMetodo").value;
  const fecha = document.getElementById("filtroFecha").value;

  try {
    const res = await fetch(`api/egresos_listar.php?pagina=${pagina}&por_pagina=${porPagina}&metodo=${metodo}&fecha=${fecha}`);
    const data = await res.json();

    if (data.status === "success") {
      const tbody = document.querySelector("#tablaEgresos tbody");
      tbody.innerHTML = "";
      data.data.egresos.forEach(e => {
        tbody.innerHTML += `
          <tr>
            <td data-label="ID">${e.id}</td>
            <td data-label="Descripción">${e.descripcion}</td>
            <td data-label="Monto">$${parseFloat(e.monto).toFixed(2)}</td>
            <td data-label="Fecha">${new Date(e.fecha_egreso).toLocaleString()}</td>
            <td data-label="Método">${e.metodo_pago}</td>
            <td data-label="Acciones">
              <button class="btn btn-danger btn-sm" onclick="eliminarEgreso(${e.id})">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        `;

      });
      generarPaginacion(data.data.total_paginas);
    } else {
      alert("Error al cargar egresos: " + data.message);
    }
  } catch (err) {
    console.error(err);
    alert("Error al cargar egresos.");
  }
}

function generarPaginacion(totalPaginas) {
  const paginacion = document.getElementById("paginacionEgresos");
  paginacion.innerHTML = "";
  for (let i = 1; i <= totalPaginas; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    btn.className = "btn btn-sm " + (i === paginaActual ? "btn-primary" : "btn-outline-primary");
    btn.onclick = () => cargarEgresos(i);
    paginacion.appendChild(btn);
  }
}

async function eliminarEgreso(id) {
  if (!confirm("¿Estás seguro de eliminar este egreso?")) return;

  try {
    const res = await fetch("api/egresos_eliminar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.status === "success") {
      cargarEgresos();
    } else {
      alert("Error: " + data.message);
    }
  } catch (err) {
    alert("Error al eliminar el egreso.");
    console.error(err);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  fetch("api/clientes_get.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#tablaClientesSuspendidos tbody");
      tbody.innerHTML = "";

      const estadosPermitidos = ['Suspendido', 'Cancelado', 'Bloqueado']; // si 'Blowureadfo' fue typo

      data.forEach(cliente => {
        if (estadosPermitidos.includes(cliente.estado)) {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td data-label="ID">${cliente.id}</td>
              <td data-label="Nombre">${cliente.nombre}</td>
              <td data-label="IP">${cliente.ip_cliente}</td>
              <td data-label="Estado"><span class="badge bg-${getColorEstado(cliente.estado)}">${cliente.estado}</span></td>
              <td data-label="Día Corte">${cliente.dia_corte}</td>
              <td data-label="Tipo">${cliente.tipo_conexion == 0 ? 'Fibra' : 'Antena'}</td>
              <td data-label="Acciones">
                <a href="editar_cliente.php?id=${cliente.id}" class="btn btn-primary btn-sm me-1">
                  <i class="bi bi-pencil"></i>
                </a>
                <button class="btn btn-success btn-sm" onclick="activarCliente(${cliente.id}, this)">
                  <i class="bi bi-power"></i>
                </button>
              </td>
            `;

          tbody.appendChild(tr);
        }
      });
    });

  // Función para dar color al estado
  function getColorEstado(estado) {
    switch (estado) {
      case 'Activo': return 'success';
      case 'Suspendido': return 'warning';
      case 'Cancelado': return 'danger';
      case 'Bloqueado': return 'secondary';
      default: return 'dark';
    }
  }
});

function activarCliente(id, boton) {
  if (!confirm("¿Deseas activar este cliente?")) return;

  boton.disabled = true;
  boton.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;

  fetch("api/activar_cliente.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}`
  })
    .then(res => res.text())
    .then(msg => {
      alert(msg);
      boton.closest("tr").remove(); // Quita el cliente de la tabla si fue activado
    })
    .catch(err => {
      alert("Error al activar cliente.");
      console.error(err);
    })
    .finally(() => {
      boton.disabled = false;
      boton.innerHTML = `<i class="bi bi-power"></i>`;
    });
}

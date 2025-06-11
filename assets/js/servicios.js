document.addEventListener("DOMContentLoaded", () => {
  fetch("api/servicios_get.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#tablaServicios tbody");
      const buscador = document.getElementById("busquedaServicio");

      function render(filtrados) {
        tbody.innerHTML = "";
        filtrados.forEach(s => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td data-label="ID">${s.idPlataformas}</td>
            <td data-label="Nombre">${s.nombre}</td>
            <td data-label="Descripción">${s.descripcion || '-'}</td>
            <td data-label="Precio">$${parseFloat(s.precio).toFixed(2)}</td>
            <td data-label="Acciones">
              <a href="/ded/servicio_editar.php?id=${s.idPlataformas}" class="btn btn-sm btn-warning me-1">
                <i class="bi bi-pencil-square"></i>
              </a>
              <a href="/ded/api/servicio_eliminar.php?id=${s.idPlataformas}" class="btn btn-danger btn-sm">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          `;

          tbody.appendChild(tr);
        });
      }

      function filtrar() {
        const texto = buscador.value.toLowerCase();
        const filtrados = data.filter(s =>
          s.nombre.toLowerCase().includes(texto) ||
          (s.descripcion && s.descripcion.toLowerCase().includes(texto))
        );
        render(filtrados);
      }

      buscador.addEventListener("input", filtrar);
      render(data);
    });
});

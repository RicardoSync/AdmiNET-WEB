document.addEventListener("DOMContentLoaded", () => {
  fetch("api/paquetes_get.php")
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#tablaPaquetes tbody");
      const buscador = document.getElementById("busquedaPaquete");

      function render(filtrados) {
        tbody.innerHTML = "";
        filtrados.forEach(p => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td data-label="ID">${p.id}</td>
            <td data-label="Nombre">${p.nombre}</td>
            <td data-label="Velocidad">${p.velocidad}</td>
            <td data-label="Precio">$${parseFloat(p.precio).toFixed(2)}</td>
            <td data-label="Acciones">
              <a href="api/paquete_editar.php?id=${p.id}" class="btn btn-sm btn-warning me-1">
                <i class="bi bi-pencil-square"></i>
              </a>
              <a href="api/paquete_eliminar.php?id=${p.id}" class="btn btn-sm btn-danger"
                onclick="return confirm('¿Eliminar este paquete?');">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          `;

          tbody.appendChild(tr);
        });
      }

      function filtrar() {
        const texto = buscador.value.toLowerCase();
        const filtrados = data.filter(p =>
          p.nombre.toLowerCase().includes(texto) ||
          p.velocidad.toLowerCase().includes(texto)
        );
        render(filtrados);
      }

      buscador.addEventListener("input", filtrar);
      render(data);
    });
});

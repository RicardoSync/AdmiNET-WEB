<!-- includes/sidebar.php -->

<!-- Botón hamburguesa (solo visible en móviles) -->
<button class="btn btn-dark d-md-none position-fixed" id="openSidebarBtn" style="top:10px; left:10px; z-index:1051;">
  <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<div id="sidebar" class="bg-dark text-white p-3 shadow-lg">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-center flex-grow-1">
      <img src="/ded/assets/img/logo.png" width="100" alt="Logo AdmiNET" class="img-fluid">
    </div>
    <button class="btn btn-outline-light d-md-none" id="toggleSidebar" style="position:absolute; top:10px; right:10px;">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <ul class="nav flex-column">

    <!-- DASHBOARD -->
    <li class="nav-item">
      <a href="/ded/dashboard.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
        <i class="bi bi-graph-up-arrow me-2"></i>Dashboard
      </a>
    </li>

    <!-- CLIENTES -->
    <li class="nav-item">
      <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#submenuClientes" role="button" aria-expanded="false" aria-controls="submenuClientes">
        <span><i class="bi bi-people-fill me-2"></i>Clientes</span>
        <i class="bi bi-chevron-down"></i>
      </a>
      <div class="collapse ps-3" id="submenuClientes">
        <ul class="nav flex-column">
          <li class="nav-item"><a href="/ded/clientes.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Ver Clientes</a></li>
          <li class="nav-item"><a href="/ded/clientes_suspendidos.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'clientes_suspendidos.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Suspendidos</a></li>
          <li class="nav-item"><a href="/ded/maps.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'maps.php' ? 'active' : '' ?>"><i class="bi bi-map me-2"></i>Mapa</a></li>
        </ul>
      </div>
    </li>

    <!-- PAGOS -->
    <li class="nav-item">
      <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#submenuPagos" role="button" aria-expanded="false" aria-controls="submenuPagos">
        <span><i class="bi bi-cash-stack me-2"></i>Financiero</span>
        <i class="bi bi-chevron-down"></i>
      </a>
      <div class="collapse ps-3" id="submenuPagos">
        <ul class="nav flex-column">
          <li class="nav-item"><a href="/ded/pagos.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'pagos.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Pagos</a></li>
          <li class="nav-item"><a href="/ded/adeudos.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'adeudos.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Adeudos</a></li>
          <li class="nav-item"><a href="/ded/egresos.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'egresos.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Egresos</a></li>
        </ul>
      </div>
    </li>

    <!-- EQUIPOS -->
    <li class="nav-item"><a href="/ded/equipos.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'equipos.php' ? 'active' : '' ?>"><i class="bi bi-pc-display me-2"></i>Equipos</a></li>

    <!-- PAQUETES -->
    <li class="nav-item"><a href="/ded/paquetes.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'paquetes.php' ? 'active' : '' ?>"><i class="bi bi-speedometer me-2"></i>Paquetes</a></li>

    <!-- SERVICIOS -->
    <li class="nav-item"><a href="/ded/servicios.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'servicios.php' ? 'active' : '' ?>"><i class="bi bi-broadcast-pin me-2"></i>Servicios</a></li>

    <!-- LOCALIDADES -->
    <li class="nav-item"><a href="/ded/localidades.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'localidades.php' ? 'active' : '' ?>"><i class="bi bi-geo-fill me-2"></i>Localidades</a></li>

    <!-- EMPRESA -->
    <li class="nav-item">
      <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#submenuEmpresa" role="button" aria-expanded="false" aria-controls="submenuEmpresa">
        <span><i class="bi bi-diagram-3 me-2"></i>Empresa</span>
        <i class="bi bi-chevron-down"></i>
      </a>
      <div class="collapse ps-3" id="submenuEmpresa">
        <ul class="nav flex-column">
          <li class="nav-item"><a href="/ded/empresa.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'empresa.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Datos Empresa</a></li>
          <li class="nav-item"><a href="/ded/mi_cuenta.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'mi_cuenta.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Master Account</a></li>
        </ul>
      </div>
    </li>

    <!-- TICKETS -->
    <li class="nav-item"><a href="/ded/tickets.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'tickets.php' ? 'active' : '' ?>"><i class="bi bi-ticket-detailed me-2"></i>Tickets</a></li>

    <!-- MIKROTIK -->
    <li class="nav-item">
      <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#submenuMikrotik" role="button" aria-expanded="false" aria-controls="submenuMikrotik">
        <span><i class="bi bi-hdd-network me-2"></i>MikroTik</span>
        <i class="bi bi-chevron-down"></i>
      </a>
      <div class="collapse ps-3" id="submenuMikrotik">
        <ul class="nav flex-column">
          <li class="nav-item"><a href="/ded/mikrotik.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'mikrotik.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Configuración</a></li>
          <li class="nav-item"><a href="/ded/queue_parent.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'queue_parent.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Queue Parent</a></li>
        </ul>
      </div>
    </li>

    <!-- SISTEMA -->
    <li class="nav-item">
      <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#submenuSistema" role="button" aria-expanded="false" aria-controls="submenuSistema">
        <span><i class="bi bi-sliders me-2"></i>Sistema</span>
        <i class="bi bi-chevron-down"></i>
      </a>
      <div class="collapse ps-3" id="submenuSistema">
        <ul class="nav flex-column">
          <li class="nav-item"><a href="/ded/logs.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Logs de Red</a></li>
          <li class="nav-item"><a href="/ded/logs_seiones.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'logs_seiones.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Logs de Sesión</a></li>
          <li class="nav-item"><a href="/ded/informacion_sistema.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'informacion_sistema.php' ? 'active' : '' ?>"><i class="bi bi-circle me-2"></i>Información</a></li>
        </ul>
      </div>
    </li>
  </ul>

  <small class="text-secondary d-block text-center mt-4">
    &copy; <?= date('Y') ?> Software Escobedo. Desarrollado por Ricardo Escobedo.
  </small>

  <hr class="bg-secondary">
  <a href="/ded/logout.php" class="btn btn-outline-danger w-100 mt-2"><i class="bi bi-box-arrow-left me-2"></i>Salir</a>
</div>

<!-- Estilo responsive + visual -->
<style>
@media (max-width: 768px) {
  #sidebar {
    position: fixed;
    top: 0;
    left: -100%;
    width: 240px;
    height: 100%;
    z-index: 1040;
    background-color: #000;
    overflow-y: auto;
    transition: left 0.3s ease;
  }

  #sidebar.show {
    left: 0;
  }
}

.nav-link:hover {
  background-color: #1f1f1f;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.nav-link.active,
.nav-link.active:hover {
  background-color: #0d6efd;
  color: white;
  font-weight: bold;
  border-radius: 8px;
}

.nav-item .bi-chevron-down {
  transition: transform 0.3s ease;
}

.nav-item[aria-expanded="true"] .bi-chevron-down {
  transform: rotate(180deg);
}

.nav-item .nav-link i.bi-circle {
  font-size: 0.6rem;
  color: #aaa;
}
</style>

<!-- JS: mostrar/ocultar sidebar móvil -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.getElementById('sidebar');
  const openBtn = document.getElementById('openSidebarBtn');
  const closeBtn = document.getElementById('toggleSidebar');

  if (openBtn && sidebar) {
    openBtn.addEventListener('click', () => sidebar.classList.add('show'));
  }
  if (closeBtn && sidebar) {
    closeBtn.addEventListener('click', () => sidebar.classList.remove('show'));
  }
});
</script>

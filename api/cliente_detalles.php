<?php
require_once '../includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "<div class='alert alert-danger'>ID no proporcionado.</div>";
  exit;
}

// Consulta optimizada con JOINs
$stmt = $conn->prepare("
  SELECT 
    c.*, 
    p.nombre AS paquete_nombre,
    p.velocidad AS paquete_velocidad,
    p.precio AS paquete_precio,
    m.nombre AS mikrotik_nombre,
    a.nombre AS antena_nombre,
    z.nombre AS zona_nombre,
    sp.nombre AS plataforma_nombre,
    sp.precio AS plataforma_precio
  FROM clientes c
  LEFT JOIN paquetes p ON c.id_paquete = p.id
  LEFT JOIN credenciales_microtik m ON c.id_microtik = m.id
  LEFT JOIN antenasap a ON c.id_antena_ap = a.idantenasAp
  LEFT JOIN zonas z ON c.id_zona = z.id
  LEFT JOIN serviciosplataforma sp ON c.id_servicio_plataforma = sp.idPlataformas
  WHERE c.id = ?
");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$c) {
  echo "<div class='alert alert-warning'>Cliente no encontrado.</div>";
  exit;
}

// Prepara valores amigables
$tipoConexion = $c['tipo_conexion'] == 0 ? 'Fibra Óptica' : 'Antena';
$estado = $c['estado'] ?? 'Desconocido';
$fecha_registro = date('d/m/Y H:i', strtotime($c['fecha_registro']));

// Render de la tabla
echo "<div class='table-responsive'>
  <table class='table table-bordered table-striped text-white'>
    <tbody>
      <tr><th>Nombre</th><td>{$c['nombre']}</td></tr>
      <tr><th>Teléfono</th><td>{$c['telefono']}</td></tr>
      <tr><th>Email</th><td>{$c['email']}</td></tr>
      <tr><th>Dirección</th><td>{$c['direccion']}</td></tr>
      <tr><th>Fecha de Registro</th><td>{$fecha_registro}</td></tr>
      <tr><th>Estado</th><td><span class='badge bg-secondary'>{$estado}</span></td></tr>
      <tr><th>Tipo de Conexión</th><td>{$tipoConexion}</td></tr>
      <tr><th>IP Cliente</th><td>{$c['ip_cliente']}</td></tr>
      <tr><th>Día de Corte</th><td>{$c['dia_corte']}</td></tr>
      <tr><th>Paquete</th><td>{$c['paquete_nombre']} ({$c['paquete_velocidad']}) - \${$c['paquete_precio']}</td></tr>
      <tr><th>Servicio Adicional</th><td>{$c['plataforma_nombre']} - \${$c['plataforma_precio']}</td></tr>
      <tr><th>Antena/Zona AP</th><td>{$c['antena_nombre']}</td></tr>
      <tr><th>Zona</th><td>{$c['zona_nombre']}</td></tr>
      <tr><th>MikroTik Asignado</th><td>{$c['mikrotik_nombre']}</td></tr>
    </tbody>
  </table>
</div>";

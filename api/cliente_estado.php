<?php
require_once '../includes/db.php';
$id = $_GET['id'];
$accion = $_GET['accion'];

$estadoNuevo = ($accion === 'suspender') ? 'Suspendido' : 'Activo';
$stmt = $conn->prepare("UPDATE clientes SET estado = ? WHERE id = ?");
$stmt->execute([$estadoNuevo, $id]);

echo "Cliente $accion correctamente.";

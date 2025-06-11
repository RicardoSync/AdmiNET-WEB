
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

require_once '../includes/db.php';


// Validar que venga el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
  $_SESSION['msg_error'] = "ID no especificado.";
  header("Location: tickets.php");
  exit;
}

$id = intval($_GET['id']);

try {
  $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
  $stmt->execute([$id]);

  if ($stmt->rowCount() > 0) {
    $_SESSION['msg_success'] = "Ticket eliminado correctamente.";
  } else {
    $_SESSION['msg_error'] = "El ticket no existe o ya fue eliminado.";
  }
} catch (Exception $e) {
  $_SESSION['msg_error'] = "Error al eliminar: " . $e->getMessage();
}

header("Location: /ded/tickets.php");
exit;

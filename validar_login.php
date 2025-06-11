<?php
session_start();

// Conexión a base de datos central
try {
    $connGlobal = new PDO("mysql:host=softwarescobedo.com.mx;dbname=adminet_global;charset=utf8mb4", "adminet", "MinuzaFea265/");
    $connGlobal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión global: " . $e->getMessage());
}

// Captura de datos del formulario
$usuario = $_POST['usuario'];
$password = $_POST['password'];

// Consulta del usuario
$stmt = $connGlobal->prepare("SELECT * FROM usuarios_empresas WHERE usuario = ?");
$stmt->execute([$usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Validación de credenciales
if ($user && $password === $user['password']) { // Reemplaza esto por password_verify() si usas hash

    // Guardar datos en sesión
    $_SESSION['usuario'] = $usuario;
    $_SESSION['empresa'] = $user['nombre_empresa'];
    $_SESSION['base_datos'] = $user['base_datos'];

    // Si se marcó "recordar sesión", guardamos en cookie (por 30 días)
    if (isset($_POST['recordar'])) {
        setcookie("usuario", $usuario, time() + (86400 * 30), "/");
        setcookie("empresa", $user['nombre_empresa'], time() + (86400 * 30), "/");
        setcookie("base_datos", $user['base_datos'], time() + (86400 * 30), "/");
    }

    // === REGISTRO DE LOG DE SESIÓN ===
    $ip_publica = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $navegador = "Desconocido";
    if (strpos($user_agent, 'Firefox') !== false) $navegador = "Firefox";
    elseif (strpos($user_agent, 'Chrome') !== false && strpos($user_agent, 'Edg') === false) $navegador = "Chrome";
    elseif (strpos($user_agent, 'Edg') !== false) $navegador = "Edge";
    elseif (strpos($user_agent, 'Safari') !== false && strpos($user_agent, 'Chrome') === false) $navegador = "Safari";
    elseif (strpos($user_agent, 'Opera') !== false || strpos($user_agent, 'OPR') !== false) $navegador = "Opera";

    $stmtLog = $connGlobal->prepare("INSERT INTO logs_sesiones (usuario, empresa, ip_publica, navegador, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmtLog->execute([$usuario, $user['nombre_empresa'], $ip_publica, $navegador, $user_agent]);

    header("Location: dashboard.php");
    exit;

} else {
    $_SESSION['error'] = "Credenciales incorrectas.";
    header("Location: login.php");
    exit;
}

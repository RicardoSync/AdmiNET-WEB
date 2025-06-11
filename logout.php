<?php
session_start();
session_unset();
session_destroy();

// Eliminar cookies
setcookie("usuario", "", time() - 3600, "/");
setcookie("empresa", "", time() - 3600, "/");
setcookie("base_datos", "", time() - 3600, "/");

header("Location: login.php");
exit;

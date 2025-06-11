<?php
use phpseclib3\Net\SSH2;

require_once __DIR__ . '/../vendor/autoload.php';        // ✅ CORRECTA

function ejecutarComandoSSH($ip, $usuario, $contrasena, $puerto, $comando) {
  try {
    $ssh = new SSH2($ip, $puerto);
    if (!$ssh->login($usuario, $contrasena)) {
      error_log("Error SSH: Login fallido.");
      return false;
    }
    $salida = $ssh->exec($comando);
    return true;
  } catch (Exception $e) {
    error_log("Error SSH: " . $e->getMessage());
    return false;
  }
}

function ejecutarComandoSSHRetorno($ip, $usuario, $contrasena, $puerto, $comando) {
  try {
    $ssh = new \phpseclib3\Net\SSH2($ip, $puerto);
    if (!$ssh->login($usuario, $contrasena)) {
      error_log("Error SSH: Login fallido");
      return null;
    }
    return $ssh->exec($comando);
  } catch (Exception $e) {
    error_log("Error SSH: " . $e->getMessage());
    return null;
  }
}

function probarConexionSSH($ip, $usuario, $contrasena, $puerto = 22) {
  try {
    $ssh = new \phpseclib3\Net\SSH2($ip, $puerto);
    return $ssh->login($usuario, $contrasena);
  } catch (Exception $e) {
    return false;
  }
}

?>


<?php
session_start();

/**
 * 1. Ajuste de ruta del Autoload
 * Estás en: /aplicacion/procesos/auth/
 * Quieres ir a: /aplicacion/core/
 */
// Dentro de aplicacion/procesos/auth/procesar_login.php
require_once __DIR__ . '/../../aplicacion/core/Autoload.php'; 

use aplicacion\dao\UserDAO;

// 2. Definir la URL base (Consistente con tu index.php)
$urlBase = "/IglesiaDelNazarenoBagua/";

$usuario  = trim($_POST['usuario']  ?? '');
$password = trim($_POST['password'] ?? '');

// Validar campos vacíos
if (empty($usuario) || empty($password)) {
    header("Location: " . $urlBase . "login?error=1");
    exit;
}

// Buscar en la BD
$dao = new UserDAO();
$resultado = $dao->buscarParaLogin($usuario);

// Usuario no encontrado
if (!$resultado) {
    header("Location: " . $urlBase . "login?error=3");
    exit;
}

// Contraseña incorrecta
if (!password_verify($password, $resultado['password'])) {
    header("Location: " . $urlBase . "login?error=2");
    exit;
}

// ✅ Todo correcto — guardar sesión
session_regenerate_id(true);
$_SESSION['usuario']    = $resultado['username'];
$_SESSION['rol_id']     = $resultado['id_rol'];
$_SESSION['rol_nombre'] = $resultado['rol_nombre'];

// Redirigir según rol usando rutas amigables
switch ($resultado['id_rol']) {
    case 1: // Admin
    case 2: // Pastor
           header("Location: /IglesiaDelNazarenoBagua/dashboard");        break;
    default:
        header("Location: " . $urlBase . "login?error=3");
        break;
}
exit;
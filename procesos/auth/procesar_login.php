<?php
session_start();

// 1. Ajuste de ruta del Autoload: estamos en procesos/auth/, subimos dos niveles
require_once __DIR__ . '/../../aplicacion/core/Autoload.php';

use aplicacion\dao\UserDAO;

$usuario  = trim($_POST['usuario']  ?? '');
$password = trim($_POST['password'] ?? '');

// RUTA BASE PARA REDIRECCIONES PÚBLICAS
$urlBase = "/IglesiaDelNazarenoBagua/index.php";

// Validar campos vacíos
if (empty($usuario) || empty($password)) {
    header("Location: $urlBase?vista=login&error=1");
    exit;
}

// Buscar en la BD
$dao = new UserDAO();
$resultado = $dao->buscarParaLogin($usuario);

// Usuario no encontrado
if (!$resultado) {
    header("Location: $urlBase?vista=login&error=3");
    exit;
}

// Contraseña incorrecta
if (!password_verify($password, $resultado['password'])) {
    header("Location: $urlBase?vista=login&error=2");
    exit;
}

// ✅ Todo correcto — guardar sesión
session_regenerate_id(true);
$_SESSION['usuario']    = $resultado['username'];
$_SESSION['rol_id']     = $resultado['id_rol'];
$_SESSION['rol_nombre'] = $resultado['rol_nombre'];

// Redirigir según rol: Ahora apuntamos al dashboard a través del index público
switch ($resultado['id_rol']) {
    case 1: // Admin
    case 2: // Pastor
        header("Location: $urlBase?vista=dashboard");
        break;
    default:
        header("Location: $urlBase?vista=login&error=3");
        break;
}
exit;
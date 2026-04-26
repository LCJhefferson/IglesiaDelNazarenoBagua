<?php
session_start();
require_once __DIR__ . '/aplicacion/core/Autoload.php';

use aplicacion\dao\UserDAO;

$usuario  = trim($_POST['usuario']  ?? '');
$password = trim($_POST['password'] ?? '');

// Validar campos vacíos
if (empty($usuario) || empty($password)) {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php?error=1");
    exit;
}

// Buscar en la BD
$dao       = new UserDAO();
$resultado = $dao->buscarParaLogin($usuario);

// Usuario no encontrado
if (!$resultado) {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php?error=3");
    exit;
}

// Contraseña incorrecta
if (!password_verify($password, $resultado['password'])) {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php?error=2");
    exit;
}

// ✅ Todo correcto — guardar sesión
// ✅ Todo correcto — guardar sesión
session_regenerate_id(true);
$_SESSION['usuario']    = $resultado['username'];
$_SESSION['rol_id']     = $resultado['id_rol'];
$_SESSION['rol_nombre'] = $resultado['rol_nombre'];

// Redirigir según rol
switch ($resultado['id_rol']) {
    case 1: // Admin
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php");
        break;
    case 2: // Pastor
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php");
        break;
    default:
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php?error=3");
        break;
}
exit;
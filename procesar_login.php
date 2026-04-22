<?php
session_start();

// recibir datos
$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? '';

// validar campos vacíos
if (empty($usuario) || empty($password) || empty($rol)) {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php?error=1");
    exit;
}

// USUARIO DE PRUEBA
if ($usuario === "admin" && $password === "1234") {

    $_SESSION['usuario'] = $usuario;
    $_SESSION['rol'] = $rol;

    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php");
    exit;

} else {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php?error=2");
    exit;
}




















/*




session_start();

// conexión a la BD
require_once 'aplicacion/nucleo/conexion.php';

// recibir datos del formulario
$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? '';

// validar campos vacíos
if (empty($usuario) || empty($password) || empty($rol)) {
    header("Location: aplicacion/vistas/publico/login.php?error=1");
    exit;
}

// consulta a la base de datos
$sql = "SELECT * FROM usuarios WHERE usuario = ? AND id_rol = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("si", $usuario, $rol);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $user = $resultado->fetch_assoc();

    // verificar contraseña (simple por ahora)
    if ($password === $user['password']) {

        // guardar sesión
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol'] = $user['id_rol'];

        // redirigir según rol
        header("Location: aplicacion/vistas/admin/dashboard.php");
        exit;

    } else {
        header("Location: aplicacion/vistas/publico/login.php?error=2");
        exit;
    }

} else {
    header("Location: aplicacion/vistas/publico/login.php?error=3");
    exit;
}

*/
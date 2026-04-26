<?php
<<<<<<< HEAD
=======
// 1. EL AUTOLOAD ES PRIMERO: Esto conecta todas las clases (Controladores, DAO, Modelos)
// Subimos dos niveles para llegar desde vistas/admin/ hasta aplicacion/
require_once __DIR__ . '/../../../aplicacion/core/Autoload.php';

session_start();
>>>>>>> 929f409 (Mis cambios en noticias)

ini_set('session.cookie_lifetime', 0);
session_start();
require_once __DIR__ . '/../../core/Autoload.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php");
    exit;
}

if (!in_array($_SESSION['rol_id'], [1, 2])) {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php?error=3");
    exit;
}

$vista = $_GET['vista'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<<<<<<< HEAD
    <meta charset="UTF-8">
    <title>Panel Admin</title>
    <base href="/IglesiaDelNazarenoBagua/">
    <link rel="stylesheet" href="admin/css/dashboard.css">
    <link rel="stylesheet" href="admin/css/sidebar.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/> <!-- ← ESTO FALTA -->


    <?php
    $estilos = [
        'NewUsuarioForm'    => 'NewUsuarioForm.css',
        'noticias'          => 'noticias.css',
        'membresia'         => 'membresia.css',
        'recurso_admin'     => 'recurso_admin.css',
        'reguistro_usuario' => 'reguistro_usuario.css',
        'usuarios_admin'    => 'usuarios_admin.css',
        'visitasListar'     => 'visitasListar.css',
        'visitasMap'        => 'visitasMap.css',
        'trasmision'        => 'transmision.css',
    ];
    if ($vista && isset($estilos[$vista])) {
        echo '<link rel="stylesheet" href="admin/css/' . $estilos[$vista] . '">';
    }
    ?>
=======
<meta charset="UTF-8">
<title>Panel Admin | Iglesia del Nazareno</title>

<base href="/IglesiaDelNazarenoBagua/">

<link rel="stylesheet" href="admin/css/dashboard.css">
<link rel="stylesheet" href="admin/css/sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<?php
// Renderizado dinámico de estilos CSS
if (isset($_GET['vista'])) {
    $vista = $_GET['vista'];
    // Definimos un array para no repetir tantos "if"
    $estilos = [
        'NewUsuarioForm'    => 'admin/css/NewUsuarioForm.css',
        'noticias'          => 'admin/css/noticias.css',
        'membresia'         => 'admin/css/membresia.css',
        'recurso_admin'     => 'admin/css/recurso_admin.css',
        'reguistro_usuario' => 'admin/css/reguistro_usuario.css',
        'usuarios_admin'    => 'admin/css/usuarios_admin.css',
        'visitasListar'     => 'admin/css/visitasListar.css',
        'visitasMap'        => 'admin/css/visitasMap.css',
        'trasmision'        => 'admin/css/transmision.css'
    ];

    if (isset($estilos[$vista])) {
        echo '<link rel="stylesheet" href="' . $estilos[$vista] . '">';
    }
}
?>
>>>>>>> 929f409 (Mis cambios en noticias)
</head>
<body>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-area">
        <section class="content" id="contenedor-vista">
            <?php
<<<<<<< HEAD
            if ($vista) {
                if ($vista === 'usuarios_admin' && $_SESSION['rol_id'] !== 1) {
                    echo "<p style='color:red;'>No tienes permiso para ver esta sección.</p>";
                } else {
                    $ruta = "contenidos/" . $vista . ".php";
                    if (file_exists($ruta)) {
                        include $ruta;
                    } else {
                        echo "<p style='color:red;'>Vista no encontrada.</p>";
                    }
                }
            } else {
                echo '<div class="contenedor-tarjeta">
                        <div class="tarjeta">
                            <h3>Bienvenido, ' . htmlspecialchars($_SESSION['usuario']) . ' 👋</h3>
                            <p>Rol: ' . htmlspecialchars($_SESSION['rol_nombre']) . '</p>
                        </div>
                      </div>';
=======
            if (isset($_GET['vista'])) {
                $vista = $_GET['vista'];
                $ruta = "contenidos/" . $vista . ".php";
                
                if (file_exists($ruta)) {
                    // Al haber cargado el Autoload arriba, cualquier "use controladores\..." 
                    // que esté dentro de estos archivos ahora sí funcionará.
                    include $ruta;
                } else {
                    echo "<div class='tarjeta'><p style='color:red;'>Error 404: La vista '{$vista}' no existe en la carpeta contenidos.</p></div>";
                }
            } else {
                echo '
                <div class="contenedor-tarjeta">
                    <div class="tarjeta">
                        <h3>Bienvenido, '. htmlspecialchars($_SESSION['usuario']) .'</h3>
                        <p>Selecciona una opción del menú para comenzar.</p>
                    </div>
                </div>';
>>>>>>> 929f409 (Mis cambios en noticias)
            }
            ?>
        </section>
    </main>
</div>

<script src="admin/js/sidebar.js"></script>

<?php
<<<<<<< HEAD
$scripts = [
    'NewUsuario'    => 'NewUsuario.js',
    'noticias'          => 'noticias.js',
    'usuarios_admin'    => 'usuarios_admin.js',
    'visitasMap'        => 'visitasMap.js',
    'recurso_admin'     => 'recurso_admin.js',
    'reguistro_usuario' => 'reguistro_usuario.js',
    'membresia'         => 'membresia.js',
];
if ($vista && isset($scripts[$vista])) {
    echo '<script src="admin/js/' . $scripts[$vista] . '"></script>';
=======
// Renderizado dinámico de Scripts JS
if (isset($_GET['vista'])) {
    $vista = $_GET['vista'];
    $scripts = [
        'NewUsuarioForm'    => 'admin/js/NewUsuarioForm.js',
        'noticias'          => 'admin/js/noticias.js',
        'usuarios_admin'    => 'admin/js/usuarios_admin.js',
        'visitasMap'        => 'admin/js/visitasMap.js',
        'recurso_admin'     => 'admin/js/recurso_admin.js',
        'reguistro_usuario' => 'admin/js/reguistro_usuario.js',
        'membresia'         => 'admin/js/membresia.js'
    ];

    if (isset($scripts[$vista])) {
        echo '<script src="' . $scripts[$vista] . '"></script>';
    }
>>>>>>> 929f409 (Mis cambios en noticias)
}
?>

</body>
</html>
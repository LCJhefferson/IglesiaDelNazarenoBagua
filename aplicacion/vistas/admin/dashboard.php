<?php

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
</head>
<body>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-area">
        <section class="content" id="contenedor-vista">
            <?php
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
            }
            ?>
        </section>
    </main>
</div>

<script src="admin/js/sidebar.js"></script>

<?php
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
}
?>

</body>
</html>
<?php
ini_set('session.cookie_lifetime', 0);
session_start();

// 1. El autoload sigue igual porque el archivo físico no se ha movido de carpeta
require_once __DIR__ . '/../../core/Autoload.php';

// 2. Redirecciones: Siempre a través del index.php
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php?vista=login");
    exit;
}

if (!in_array($_SESSION['rol_id'], [1, 2])) {
    header("Location: index.php?vista=login&error=3");
    exit;
}

// 3. Capturamos 'seccion' (enviada desde el sidebar) y la guardamos en $vista 
// para no romper tu lógica de estilos y scripts de abajo.
$vista = $_GET['seccion'] ?? 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/IglesiaDelNazarenoBagua/public/">
    <meta charset="UTF-8">
    <title>Panel Admin</title>
       
    <link rel="stylesheet" href="admin/css/dashboard.css">
    <link rel="stylesheet" href="admin/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <?php
    $estilos = [
        'inicio'            => 'inicio.css',
        'NewUsuarioForm'    => 'NewUsuarioForm.css',
        'noticias'          => 'noticias.css',
        'membresia'         => 'membresia.css',
        'recurso_admin'     => 'recurso_admin.css',
        'reguistro_usuario' => 'reguistro_usuario.css',
        'usuarios_admin'    => 'usuarios_admin.css',
        'gruposDiscipulado' => 'discipulado.css',
        'listaDiscipulados' => 'discipulado.css',
        'visitasListar'     => 'visitasListar.css',
        'visitasMap'        => 'visitasMap.css',
        'transmision'       => 'transmision.css',
    ];
    if ($vista && isset($estilos[$vista])) {
        echo '<link rel="stylesheet" href="admin/css/' . $estilos[$vista] . '">';
    }
    ?>
</head>
<body>

<div class="admin-container">
    <?php 
    // PHP puede incluir archivos de la misma carpeta sin problemas
    include __DIR__ . '/includes/sidebar.php'; 
    ?>

    <main class="main-area">
    <section class="content" id="contenedor-vista">
        <?php
        // Como ahora $vista siempre tendrá al menos el valor 'inicio'
        if ($vista === 'usuarios_admin' && $_SESSION['rol_id'] !== 1) {
            echo "<p style='color:red;'>No tienes permiso para ver esta sección.</p>";
        } else {
            $ruta = __DIR__ . "/contenidos/" . $vista . ".php";
            
            if (file_exists($ruta)) {
                include $ruta;
            } else {
                // Esto solo saldrá si borras el archivo inicio.php por error
                echo "<p style='color:red;'>Vista no encontrada: " . htmlspecialchars($vista) . "</p>";
            }
        }
        ?>
    </section>
</main>
</div>

<script src="admin/js/sidebar.js?v=<?php echo time(); ?>"></script>

<?php
$scripts = [
    'NewUsuario'        => 'NewUsuario.js',
    'noticias'          => 'noticias.js',
    'usuarios_admin'    => 'usuarios_admin.js',
    'visitasMap'        => 'visitasMap.js',
    'recurso_admin'     => 'recurso_admin.js',
    'reguistro_usuario' => 'reguistro_usuario.js',
    'membresia'         => 'membresia.js',
    'transmision'       => 'transmision.js',
];
if ($vista && isset($scripts[$vista])) {
    echo '<script src="admin/js/' . $scripts[$vista] . '"></script>';
}
?>

</body>
</html>
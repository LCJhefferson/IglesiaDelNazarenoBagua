<?php
ini_set('session.cookie_lifetime', 0);
session_start();

// 1. Carga del Autoload
require_once __DIR__ . '/../../core/Autoload.php';

// 2. Control de Acceso
if (!isset($_SESSION['usuario'])) {
    header("Location: /IglesiaDelNazarenoBagua/login");
    exit;
}

if (!in_array($_SESSION['rol_id'], [1, 2])) {
    header("Location: /IglesiaDelNazarenoBagua/login?error=3");
    exit;
}

// 3. Capturamos la sección actual
$vista = $_GET['seccion'] ?? 'inicioAdmin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/IglesiaDelNazarenoBagua/public/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin | Iglesia del Nazareno</title>
       
    <link rel="stylesheet" href="admin/css/dashboard.css">
    <link rel="stylesheet" href="admin/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <?php
    // Mapeo de estilos por sección
    $estilos = [
        'inicioAdmin'            => 'inicioAdmin.css',
        'NewUsuarioForm'         => 'NewUsuarioForm.css',
        'noticias'               => 'noticias.css',
        'membresia'              => 'membresia.css',
        'recurso_admin'          => 'recurso_admin.css',
        'reguistro_usuario'      => 'reguistro_usuario.css',
        'usuarios_admin'         => 'usuarios_admin.css',
        'visitasListar'          => 'visitasListar.css',
        'visitasMap'             => 'visitasMap.css',
        'transmision'            => 'transmision.css',
        // Nuevos estilos de discipulado
        'DiscipuladoGrupos'      => 'DiscipuladoGrupos.css',
        'DiscipuladoIntegrantes' => 'DiscipuladoIntegrantes.css'
    ];

    if (isset($estilos[$vista])) {
        // Se asume que los archivos están en public/admin/css/
        echo '<link rel="stylesheet" href="admin/css/' . $estilos[$vista] . '?v=' . time() . '">';
    }
    ?>
</head>
<body>

<div class="admin-container">
    <?php 
    // Menú lateral
    include __DIR__ . '/includes/sidebar.php'; 
    ?>

    <main class="main-area">
        <section class="content" id="contenedor-vista">
            <?php
            // Limpieza de seguridad para el parámetro seccion
            $vistaInterna = str_replace(['.', '/'], '', $vista); 

            // Validación de permisos para usuarios_admin
            if ($vistaInterna === 'usuarios_admin' && $_SESSION['rol_id'] !== 1) {
                echo "<div class='error-box' style='background:#fee2e2; color:#b91c1c; padding:20px; border-radius:10px; margin:20px;'>
                        <i class='fas fa-exclamation-triangle'></i> 
                        Acceso denegado: Se requieren permisos de Súper Administrador.
                      </div>";
            } else {
                // Ruta hacia la carpeta 'contenidos' dentro de la vista admin
                $rutaContenido = __DIR__ . "/contenidos/" . $vistaInterna . ".php";
                
                // Dentro de dashboard.php
                    if (file_exists($rutaContenido)) {
                        include $rutaContenido;
                    } else {
                        // ERROR DIRECTO EN VEZ DE REDIRECCIÓN SILENCIOSA
                        echo "<div style='padding:20px; color:red; background:#fff5f5; border:1px solid red;'>";
                        echo "<strong>Error de Contenido:</strong> No se encuentra el archivo físico:<br>";
                        echo "<code style='color:black;'>$rutaContenido</code>";
                        echo "</div>";
                    }
                }
            ?> 
        </section>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="admin/js/sidebar.js?v=<?php echo time(); ?>"></script>

<?php
// Mapeo de scripts por sección
$scripts = [
    'NewUsuario'             => 'NewUsuario.js',
    'noticias'               => 'noticias.js',
    'usuarios_admin'         => 'usuarios_admin.js',
    'visitasMap'             => 'visitasMap.js',
    'recurso_admin'          => 'recurso_admin.js',
    'reguistro_usuario'      => 'reguistro_usuario.js',
    'membresia'              => 'membresia.js',
    'transmision'            => 'transmision.js',
    'DiscipuladoGrupos'      => 'DiscipuladoGrupos.js',
    'DiscipuladoIntegrantes' => 'DiscipuladoIntegrantes.js'
];

if (isset($scripts[$vista])) {
    // Se asume que los archivos están en public/admin/js/
    echo '<script src="admin/js/' . $scripts[$vista] . '?v=' . time() . '"></script>';
}
?>

</body>
</html>
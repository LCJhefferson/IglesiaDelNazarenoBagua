<?php
/**
 * ARCHIVO: dashboard.php
 * Función: Actuar como "Marco" (Shell) del Panel Administrativo.
 * Garantiza: Seguridad CSRF, Autenticación y Procesamiento de datos antes de mostrar HTML.
 */

use aplicacion\core\Middleware;
use aplicacion\controladores\DiscipuladoController;

// 1. INICIAR SEGURIDAD (Middleware ya configurado profesionalmente)
// No usamos session_start() aquí porque el Middleware lo hace con seguridad mejorada.
Middleware::auth([1, 2]); 

// 2. GENERAR TOKEN CSRF
// Se mantiene igual durante toda la sesión para evitar que el formulario expire.
$csrfToken = Middleware::csrfGenerate();

// 3. CAPTURAR LA SECCIÓN ACTUAL
$vista = $_GET['seccion'] ?? 'inicioAdmin';
$vistaInterna = str_replace(['.', '/'], '', $vista); // Seguridad: evitar Path Traversal


// 4. PROCESAMIENTO DE PETICIONES
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['eliminar_grupo']) || isset($_GET['quitar_integrante'])) {
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        Middleware::csrfVerify();
    }

    if ($vistaInterna === 'DiscipuladoGrupos' || $vistaInterna === 'DiscipuladoIntegrantes') {
        $controller = new DiscipuladoController();
        $controller->manejarPeticion(); 
    }
}

/**
 * MAPEO DE ASSETS (CSS y JS)
 * Esto mantiene el HTML limpio y carga solo lo necesario.
 */
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
    'DiscipuladoGrupos'      => 'DiscipuladoGrupos.css',
    'DiscipuladoIntegrantes' => 'DiscipuladoIntegrantes.css'
];

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
    'DiscipuladoIntegrantes' => 'DiscipuladoIntegrantes.js',
    'visitasListar'          => 'visitasListar.js',
];
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

    <?php if (isset($estilos[$vistaInterna])): ?>
        <link rel="stylesheet" href="admin/css/<?= $estilos[$vistaInterna] ?>?v=<?= time() ?>">
    <?php endif; ?>
    
    <script>
    const CSRF_TOKEN = '<?= $csrfToken ?>';
</script>
<meta name="csrf-token" content="<?= $csrfToken ?>">


</head>
<body>

<div class="admin-container">
    <?php 
    // MENU LATERAL
    include __DIR__ . '/includes/sidebar.php'; 
    ?>

    <main class="main-area">
        <section class="content" id="contenedor-vista">
            <?php
            // VALIDACIÓN DE PERMISOS EXTRA
            if ($vistaInterna === 'usuarios_admin' && $_SESSION['rol_id'] !== 1) {
                include __DIR__ . "/contenidos/error_permisos.php"; 
            } else {
                // RUTA HACIA EL ARCHIVO DE CONTENIDO
                $rutaContenido = __DIR__ . "/contenidos/" . $vistaInterna . ".php";
                
                if (file_exists($rutaContenido)) {
                    include $rutaContenido;
                } else {
                    echo "<div class='error-404'>
                            <h3>Archivo no encontrado</h3>
                            <p>La sección <b>" . htmlspecialchars($vista) . "</b> no existe físicamente en el servidor.</p>
                            <code>Ruta: $rutaContenido</code>
                          </div>";
                }
            }
            ?> 
        </section>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="admin/js/sidebar.js?v=<?= time() ?>"></script>

<?php if (isset($scripts[$vistaInterna])): ?>
    <script src="admin/js/<?= $scripts[$vistaInterna] ?>?v=<?= time() ?>"></script>
<?php endif; ?>

</body>
</html>
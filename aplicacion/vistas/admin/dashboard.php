<?php
/**
 * ARCHIVO: dashboard.php
 * Función: Actuar como "Marco" (Shell) del Panel Administrativo.
 * Garantiza: Seguridad CSRF, Autenticación y Procesamiento de datos antes de mostrar HTML.
 */

use aplicacion\core\Middleware;
use aplicacion\controladores\DiscipuladoController;

// ── ENDPOINT PARA CONSULTAR BITÁCORA VÍA AJAX (ANTES DE CUALQUIER SALIDA HTML) ──
if (isset($_GET['obtener_bitacora']) && !empty($_GET['usuario_id'])) {
    header('Content-Type: application/json');
    if (ob_get_length()) ob_clean(); 
    
    $logs = Illuminate\Database\Capsule\Manager::table('bitacora')
              ->where('usuario_id', $_GET['usuario_id'])
              ->orderBy('fecha', 'DESC')
              ->limit(10)
              ->get();
              
    echo json_encode($logs);
    exit; 
}

// 1. INICIAR SEGURIDAD (Middleware ya configurado profesionalmente)
Middleware::auth([1, 2]); 

// 2. GENERAR TOKEN CSRF
$csrfToken = Middleware::csrfGenerate();

// 3. CAPTURAR LA SECCIÓN ACTUAL
$vista = $_GET['seccion'] ?? 'inicioAdmin';
// Seguridad Avanzada: Permitir únicamente caracteres alfanuméricos y guiones bajos (Evita Path Traversal de raíz)
$vistaInterna = preg_replace('/[^a-zA-Z0-9_]/', '', $vista); 

// 4. PROCESAMIENTO DE PETICIONES POST / ACCIONES DE BORRADO
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
 */
$estilos = [
    'inicioAdmin'            => 'inicioAdmin.css',
    'NewUsuarioForm'         => 'NewUsuarioForm.css',
    'noticias'               => 'noticias.css',
    'membresia'              => 'membresia.css',
    'recurso_admin'          => 'recurso_admin.css',
    'reportes'               => 'reportes.css',
    'reguistro_usuario'      => 'reguistro_usuario.css',
    'usuarios_admin'         => 'usuarios_admin.css',
    'visitasListar'          => 'visitasListar.css',
    'visitasMap'             => 'visitasMap.css',
    'transmision'            => 'transmision.css',
    'DiscipuladoGrupos'      => 'DiscipuladoGrupos.css',
    'DiscipuladoIntegrantes' => 'DiscipuladoIntegrantes.css'
];

$scripts = [
    'inicioAdmin'            => 'inicioAdmin.js',
    'NewUsuario'             => 'NewUsuario.js',
    'noticias'               => 'noticias.js',
    'usuarios_admin'         => 'usuarios_admin.js',
    'visitasMap'             => 'visitasMap.js',
    'recurso_admin'          => 'recurso_admin.js',
    'reportes'               => 'reportes.js',
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
    <link rel="stylesheet" href="admin/css/componentes.css">
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
            $vistasAdmin = ['usuarios_admin', 'inv_reguistro_usuario'];
            if (in_array(strtolower($vistaInterna), $vistasAdmin) && $_SESSION['rol_id'] !== 1) {
                echo "<div style='padding:30px; text-align:center; background:white; border-radius:8px; border: 1px solid #ffc9c9;'>
                        <h3 style='color:#e03131;'><i class='fa-solid fa-ban'></i> Acceso Denegado</h3>
                        <p style='color:#555;'>No tienes los permisos suficientes para gestionar usuarios.</p>
                      </div>";
            } else {
                // RUTA HACIA EL ARCHIVO DE CONTENIDO
                $rutaContenido = __DIR__ . "/contenidos/" . $vistaInterna . ".php";
                
                if (file_exists($rutaContenido)) {
                    include $rutaContenido;
                } else {
                    include __DIR__ . "/../web/404.php"; 
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
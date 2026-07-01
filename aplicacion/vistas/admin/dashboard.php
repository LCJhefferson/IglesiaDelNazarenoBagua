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
Middleware::auth([1, 2, 9, 11, 12]); 

// 2. GENERAR TOKEN CSRF
$csrfToken = Middleware::csrfGenerate();

// 3. CAPTURAR LA SECCIÓN ACTUAL
$vista = $_GET['seccion'] ?? 'inicioAdmin';
// Seguridad Avanzada: Permitir únicamente caracteres alfanuméricos y guiones bajos (Evita Path Traversal de raíz)
$vistaInterna = preg_replace('/[^a-zA-Z0-9_]/', '', $vista); 

// ── RBAC: VALIDACIÓN DE ACCESO POR ROL (antes de cualquier salida HTML) ──
$rolId = (int)($_SESSION['rol_id'] ?? 0);
$vistaCheck = strtolower($vistaInterna);

// Definir vistas permitidas por cada rol restringido (lista blanca estricta)
$vistasPermitidasPorRol = [
    9  => ['inicioadmin', 'discipuladogrupos', 'discipuladointegrantes'],                                   // Discipulador
    11 => ['inicioadmin', 'recurso_admin', 'membresia', 'noticias', 'discipuladogrupos', 'discipuladointegrantes', 'reportes'],  // Secretaria
    12 => ['inicioadmin', 'visitaslistar', 'visitasmap'],                                                    // Grupo de Visitas
];
$vistasBloqueadasPorRol = [
    // Secretaria ahora usa lista blanca, ya no necesita lista negra
];

$accesoPermitido = true;

if (isset($vistasPermitidasPorRol[$rolId])) {
    if ($vistasPermitidasPorRol[$rolId] !== null) {
        // Rol con lista blanca (solo puede ver estas vistas)
        $accesoPermitido = in_array($vistaCheck, $vistasPermitidasPorRol[$rolId]);
    }
}
if (isset($vistasBloqueadasPorRol[$rolId])) {
    // Rol con lista negra (puede ver todo excepto estas)
    if (in_array($vistaCheck, $vistasBloqueadasPorRol[$rolId])) {
        $accesoPermitido = false;
    }
}

// Si no tiene permiso, redirigir silenciosamente al inicio (sin mostrar error)
if (!$accesoPermitido) {
    header('Location: /IglesiaDelNazarenoBagua/dashboard?seccion=inicioAdmin');
    exit;
}

// 4. PROCESAMIENTO DE PETICIONES POST / ACCIONES DE BORRADO
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['eliminar_grupo']) || isset($_GET['quitar_integrante'])) {

    Middleware::csrfVerify();

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
            // RUTA HACIA EL ARCHIVO DE CONTENIDO
            // (La validación RBAC ya se realizó arriba, antes del HTML. Si llegamos aquí, el acceso es válido.)
            $rutaContenido = __DIR__ . "/contenidos/" . $vistaInterna . ".php";
            
            if (file_exists($rutaContenido)) {
                include $rutaContenido;
            } else {
                include __DIR__ . "/../web/404.php"; 
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
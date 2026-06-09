<?php
/**
 * ARCHIVO: public/index.php
 * Función: Punto de entrada único (Front Controller).
 */

// 1. Configuración de errores (Solo para desarrollo)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 1);

// 2. Carga de dependencias y configuración
require_once __DIR__ . '/../aplicacion/config/config.php';

$autoloadComposer = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadComposer)) {
    require_once $autoloadComposer;
} else {
    die("Error Crítico: Ejecuta 'composer install' para continuar.");
}

// 3. Autoload propio y Base de Datos (Eloquent)
require_once __DIR__ . '/../aplicacion/core/Autoload.php';
require_once __DIR__ . '/../aplicacion/config/database.php';

// Importamos las clases necesarias para que el código sea más legible
use aplicacion\core\Middleware;
use aplicacion\controladores\AuthController;
use aplicacion\controladores\VisitaController;

// 4. Determinar la vista (Ruta)
$vista = $_GET['vista'] ?? 'dashboard'; 

// --- CORRECCIÓN CRÍTICA DE RUTEO ---
// Si el .htaccess capturó "public/dashboard" o "public/", lo limpiamos:
if (strpos($vista, 'public/') === 0) {
    $vista = substr($vista, 7); // Le quita los 7 caracteres de "public/"
}
// Si al limpiar quedó vacío, forzamos a que abra el dashboard
if (empty($vista) || $vista === 'index.php') {
    $vista = 'dashboard';
}
// -----------------------------------

$raizProyecto = __DIR__ . '/..';
/**
 * 5. ACCIONES DE SISTEMA (Peticiones rápidas o Logout)
 * Se ejecutan antes de cargar cualquier HTML.
 */

// Logout: No requiere cargar interfaces
if ($vista === 'logout') {
    (new AuthController())->logout();
    exit;
}

/**
 * 6. ENDPOINTS DE API / AJAX
 * Si la petición es para el mapa o guardar datos vía AJAX, 
 * respondemos y cortamos la ejecución (exit).
 */

// Datos JSON para el Mapa
if ($vista === 'visitasMapJSON') {
    (new VisitaController())->obtenerDatosMapaJSON();
    exit; 
}

// Procesamiento de Visitas (Guardar / Eliminar)
$accionesVisitas = [
    'admin/guardarVisita'        => 'guardarVisita',
    'admin/guardarAjustesVisita' => 'guardarAjustesVisita',
    'admin/eliminarVisita'       => 'eliminarVisita'
];

if (isset($accionesVisitas[$vista])) {
    // Verificamos seguridad antes de procesar
    Middleware::csrfVerify();
    
    $metodo = $accionesVisitas[$vista];
    (new VisitaController())->$metodo();
    
    // Asumimos que el controlador ya responde con header JSON y exit
    exit;
}

/**
 * 7. ENRUTADOR DE VISTAS (Renderizado)
 * Decide qué archivo físico cargar.
 */

if ($vista === 'procesar_login') {
    $archivoVista = $raizProyecto . '/procesos/auth/procesar_login.php';
} 
// Si la ruta empieza con 'admin/' o es el dashboard
else if (strpos($vista, 'admin/') === 0 || $vista === 'dashboard') {
    $archivoVista = $raizProyecto . '/aplicacion/vistas/admin/dashboard.php';
} 
// Vistas públicas (login, registro, etc)
else {
    $archivoVista = $raizProyecto . '/aplicacion/vistas/web/' . $vista . '.php';
}

/**
 * 8. EJECUCIÓN FINAL
 */
if (file_exists($archivoVista)) {
    include $archivoVista;
} else {
    // Error 404 Personalizado
    http_response_code(404);
    echo "
    <div style='max-width:600px; margin:50px auto; font-family:sans-serif; border:1px solid #ccc; padding:20px; border-radius:10px;'>
        <h2 style='color:#dc2626;'>Error de Enrutamiento (404)</h2>
        <p>Lo sentimos, la sección <b>" . htmlspecialchars($vista) . "</b> no está disponible.</p>
        <hr>
        <small style='color:gray;'>Ruta buscada: $archivoVista</small><br>
        <a href='dashboard' style='display:inline-block; margin-top:10px;'>Volver al Panel</a>
    </div>";
}


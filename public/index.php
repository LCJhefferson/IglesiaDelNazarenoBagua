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

use aplicacion\core\Middleware;
use aplicacion\controladores\AuthController;
use aplicacion\controladores\VisitaController;
use aplicacion\controladores\ReporteController;
use aplicacion\controladores\RecursoController;
use aplicacion\modelos\Recurso;

// 4. Determinar la vista (Ruta)
$vista = $_GET['vista'] ?? 'dashboard'; 

// --- CORRECCIÓN CRÍTICA DE RUTEO ---
if (strpos($vista, 'public/') === 0) {
    $vista = substr($vista, 7);
}
if (empty($vista) || $vista === 'index.php') {
    $vista = 'dashboard';
}

// ============================================================================
// INTERCEPCIÓN CRÍTICA 1: Descarga pública de recursos antes de cualquier renderizado
// ============================================================================
if ($vista === 'recursos' && !empty($_GET['descargar'])) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    $recursoId = (int)$_GET['descargar'];
    $recurso = Recurso::find($recursoId);
    
    if ($recurso) {
        Recurso::incrementarDescargas($recursoId);
        
        if (!empty($recurso->enlace_youtube)) {
            if (!headers_sent()) {
                header('Location: ' . $recurso->enlace_youtube);
            } else {
                echo "<script>window.location.href='" . $recurso->enlace_youtube . "';</script>";
            }
            exit;
        }
        
        if (!empty($recurso->ruta_archivo)) {
            $ruta_abs = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . $recurso->ruta_archivo;
            if (file_exists($ruta_abs)) {
                $mime = mime_content_type($ruta_abs) ?: 'application/octet-stream';
                header('Content-Type: ' . $mime);
                header('Content-Disposition: attachment; filename="' . basename($ruta_abs) . '"');
                header('Content-Length: ' . filesize($ruta_abs));
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: public');
                readfile($ruta_abs);
                exit;
            }
        }
    }
    
    $urlFallback = URL . 'recursos';
    if (!headers_sent()) {
        header('Location: ' . $urlFallback);
    } else {
        echo "<script>window.location.href='" . $urlFallback . "';</script>";
    }
    exit;
}

// ============================================================================
// INTERCEPCIÓN CRÍTICA 2: Descarga desde el Panel Administrativo (Solución al Error)
// ============================================================================
if (($vista === 'dashboard' || strpos($vista, 'admin/') === 0) 
    && ($_GET['seccion'] ?? '') === 'recurso_admin' 
    && !empty($_GET['descargar'])) {
    
    // Validamos autenticación antes de procesar la descarga privada
    Middleware::auth([1, 2, 11]);  // Admin, Pastor, Secretaria
    
    $recursoId = (int)$_GET['descargar'];
    $controller = new RecursoController();
    $controller->descargar($recursoId);
    exit; // Asegura que jamás se pinte el HTML si entra aquí
}
// ============================================================================

$raizProyecto = __DIR__ . '/..';

// Logout
if ($vista === 'logout') {
    (new AuthController())->logout();
    exit;
}

/**
 * 6. ENDPOINTS DE API / AJAX
 */

// Datos JSON para el Mapa
if ($vista === 'visitasMapJSON') {
    Middleware::auth([1, 2, 12]);  // Solo Admin, Pastor, Grupo de Visitas
    (new VisitaController())->obtenerDatosMapaJSON();
    exit; 
}

// 1. Vista previa de tablas (AJAX)
if ($vista === 'datos_reporte') {
    \aplicacion\core\Middleware::apiAuth([1, 2, 11]);
    $reporteCtrl = new ReporteController();
    $reporteCtrl->obtenerVistaPrevia();
    exit;
}

// 2. Descargas directas (Excel, CSV, PDF)
if ($vista === 'descargar_reporte') {
    \aplicacion\core\Middleware::apiAuth([1, 2, 11]);
    $reporteCtrl = new ReporteController();
    $reporteCtrl->descargarReporte();
    exit;
}

// 3. Autocompletados dinámicos (Live Search)
if ($vista === 'sugerencias_reporte') {
    \aplicacion\core\Middleware::apiAuth([1, 2, 11]);
    $reporteCtrl = new ReporteController();
    $reporteCtrl->sugerenciasAutocomplete();
    exit;
}

// 4. Carga inicial de filtros
if ($vista === 'inicializar_filtros_reporte') {
    \aplicacion\core\Middleware::apiAuth([1, 2, 11]);
    $reporteCtrl = new ReporteController();
    $reporteCtrl->inicializarFiltros();
    exit;
}

// Procesamiento de Visitas
$accionesVisitas = [
    'admin/guardarVisita'        => 'guardarVisita',
    'admin/guardarAjustesVisita' => 'guardarAjustesVisita',
    'admin/eliminarVisita'       => 'eliminarVisita'
];

if (isset($accionesVisitas[$vista])) {
    Middleware::auth([1, 2, 12]);  // Solo Admin, Pastor, Grupo de Visitas
    Middleware::csrfVerify();
    $metodo = $accionesVisitas[$vista];
    (new VisitaController())->$metodo();
    exit;
}

/**
 * 7. ENRUTADOR DE VISTAS
 */
if ($vista === 'procesar_login') {
    $archivoVista = $raizProyecto . '/procesos/auth/procesar_login.php';
} else if (strpos($vista, 'admin/') === 0 || $vista === 'dashboard') {
    $archivoVista = $raizProyecto . '/aplicacion/vistas/admin/dashboard.php';
} else {
    $archivoVista = $raizProyecto . '/aplicacion/vistas/web/' . $vista . '.php';
}

if (file_exists($archivoVista)) {
    include $archivoVista;
} else {
    include __DIR__ . "aplicacion/vistas/web/404.php"; 
}
<?php
// 1. Errores activados al máximo para ver qué falla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. URL para el navegador
define('URL', '/IglesiaDelNazarenoBagua/');

// 3. RUTA DEL SERVIDOR
$raizProyecto = realpath(__DIR__ . '/../../'); 
if (strpos($raizProyecto, 'IglesiaDelNazarenoBagua') === false) {
    $raizProyecto .= DIRECTORY_SEPARATOR . 'IglesiaDelNazarenoBagua';
}

// 4. Cargar Autoload
$autoloadPath = $raizProyecto . '/aplicacion/core/Autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die("Error Crítico: No se encontró el Autoload en: " . $autoloadPath);
}

// 5. Captura de Vista
$vista = $_GET['vista'] ?? 'inicio';
$vista = str_replace('public/', '', $vista);
$vista = str_replace('.php', '', $vista);

// =========================================================================
// 🟢 EXCEPCIONES INTERNAS (ENDPOINTS VIRTUALES DEL SISTEMA)
// =========================================================================

// A. Endpoint JSON para el Mapa (visitasMap.js) -> INTACTO
if ($vista === 'visitasMapJSON') {
    $controller = new \aplicacion\controladores\VisitaController();
    $controller->obtenerDatosMapaJSON();
    exit; 
}

// B. Procesar Guardar Registro de Visita -> Respuesta asíncrona limpia
if ($vista === 'admin/guardarVisita') {
    $controller = new \aplicacion\controladores\VisitaController();
    $controller->guardarVisita();
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

// C. Procesar Ajustes de Rangos -> Respuesta asíncrona limpia
if ($vista === 'admin/guardarAjustesVisita') {
    $controller = new \aplicacion\controladores\VisitaController();
    $controller->guardarAjustesVisita();
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

// D. Procesar Eliminación (Suprimir) de Visita -> Respuesta asíncrona limpia
if ($vista === 'admin/eliminarVisita') {
    $controller = new \aplicacion\controladores\VisitaController();
    $controller->eliminarVisita();
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

// =========================================================================
// CARGA DE VISTAS FÍSICAS (.php standard)
// =========================================================================
if ($vista === 'procesar_login') {
    $archivoVista = $raizProyecto . '/procesos/auth/procesar_login.php';
} 
else if (strpos($vista, 'admin/') === 0 || $vista === 'dashboard') {
    if ($vista === 'dashboard' || $vista === 'admin/dashboard') {
        $archivoVista = $raizProyecto . '/aplicacion/vistas/admin/dashboard.php';
    } else {
        $rutaLimpia = str_replace('admin/', '', $vista);
        $archivoVista = $raizProyecto . '/aplicacion/vistas/admin/' . $rutaLimpia . '.php';
    }
} 
else {
    $archivoVista = $raizProyecto . '/aplicacion/vistas/web/' . $vista . '.php';
}

if (file_exists($archivoVista)) {
    include $archivoVista;
} else {
    echo "<div style='background:#fee2e2; color:#b91c1c; padding:20px; border:2px solid #ef4444; font-family:sans-serif;'>";
    echo "<h3>[Error de Ruteo] El archivo solicitado no existe</h3>";
    echo "<b>Vista buscada:</b> " . htmlspecialchars($vista) . "<br>";
    echo "<b>Ruta física:</b> " . htmlspecialchars($archivoVista) . "<br>";
    echo "</div>";
    exit;
}
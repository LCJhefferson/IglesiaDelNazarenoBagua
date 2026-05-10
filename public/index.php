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

// 5. Captura de Vista (Limpiando posibles prefijos como 'public/')
$vista = $_GET['vista'] ?? 'inicio';
$vista = str_replace('public/', '', $vista); // Si viene 'public/dashboard', lo deja en 'dashboard'

if ($vista === 'procesar_login') {
    $archivoVista = $raizProyecto . '/procesos/auth/procesar_login.php';
} else if ($vista === 'dashboard' || $vista === 'admin/dashboard') {
    // Caso especial para el panel de administración
    $archivoVista = $raizProyecto . '/aplicacion/vistas/admin/dashboard.php';
} else {
    // Vistas públicas (inicio, nosotros, etc.)
    $archivoVista = $raizProyecto . '/aplicacion/vistas/web/' . $vista . '.php';
}

// 6. Carga de Archivo sin 404 (Si falla, nos dirá la ruta exacta)
if (file_exists($archivoVista)) {
    include $archivoVista;
} else {
    // En lugar de incluir un 404, lanzamos un mensaje técnico para arreglar la ruta
    echo "<div style='background:#fee2e2; color:#b91c1c; padding:20px; border:2px solid #ef4444; font-family:sans-serif;'>";
    echo "<h3>[Error de Ruteo] El archivo solicitado no existe</h3>";
    echo "<b>Vista buscada:</b> " . htmlspecialchars($vista) . "<br>";
    echo "<b>Ruta física:</b> " . htmlspecialchars($archivoVista) . "<br>";
    echo "<hr><i>Verifica que el archivo exista en esa carpeta o que el nombre coincida exactamente (mayúsculas/minúsculas).</i>";
    echo "</div>";
    exit;
}
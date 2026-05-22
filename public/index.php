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
$autoloadPath = $raizProyecto . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die("Error Crítico: No se encontró el Autoload en: " . $autoloadPath);
}


$_uriActual = strtok($_SERVER['REQUEST_URI'], '?');
if (str_starts_with($_uriActual, '/IglesiaDelNazarenoBagua/api/')) {
    $router = new \aplicacion\core\Router();
    $router->post('/api/login',            [\aplicacion\controladores\api\AuthApiController::class,   'login']);
    $router->post('/api/logout',           [\aplicacion\controladores\api\AuthApiController::class,   'logout']);
    $router->get('/api/recursos',          [\aplicacion\controladores\api\RecursoApiController::class, 'index']);
    $router->get('/api/recursos/stats',    [\aplicacion\controladores\api\RecursoApiController::class, 'stats']);
    $router->get('/api/recursos/{id}',     [\aplicacion\controladores\api\RecursoApiController::class, 'show']);
    $router->post('/api/recursos',         [\aplicacion\controladores\api\RecursoApiController::class, 'store']);
    $router->put('/api/recursos/{id}',     [\aplicacion\controladores\api\RecursoApiController::class, 'update']);
    $router->delete('/api/recursos/{id}',  [\aplicacion\controladores\api\RecursoApiController::class, 'destroy']);
    $router->dispatch();
}

// 🔐 BYPASS TEMPORAL PARA PRUEBAS EN POSTMAN

// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// $_SESSION['usuario']    = 'luis';
// $_SESSION['rol_id']     = 1;
// $_SESSION['rol_nombre'] = 'Admin';






// 5. Captura de Vista
$vista = $_GET['vista'] ?? 'inicio';
$vista = str_replace('public/', '', $vista);
$vista = str_replace('.php', '', $vista);

//Logout
if ($vista === 'logout') {
    (new \aplicacion\controladores\AuthController())->logout();
}

//EXCEPCIONES INTERNAS 
if ($vista === 'visitasMapJSON') {
    $controller = new \aplicacion\controladores\VisitaController();
    $controller->obtenerDatosMapaJSON();
    exit; 
}

// B. Procesar Guardar Registro de Visita
if ($vista === 'admin/guardarVisita') {
    \aplicacion\core\Middleware::csrfVerify();
    $controller = new \aplicacion\controladores\VisitaController();
    $controller->guardarVisita();
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

// C. Procesar Ajustes de Rangos 
if ($vista === 'admin/guardarAjustesVisita') {
    \aplicacion\core\Middleware::csrfVerify();
    $controller = new \aplicacion\controladores\VisitaController();
    $controller->guardarAjustesVisita();
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

// D. Procesar Eliminación (Suprimir) de Visita 
if ($vista === 'admin/eliminarVisita') {
    \aplicacion\core\Middleware::csrfVerify();
    $controller = new \aplicacion\controladores\VisitaController();
    $controller->eliminarVisita();
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

// CARGA DE VISTAS FÍSICAS
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
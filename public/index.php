<?php
// 1. Carga de recursos básicos
require_once __DIR__ . '/../aplicacion/core/Autoload.php';

// 2. Parámetros de navegación
$vista = $_GET['vista'] ?? 'inicio';
$baseVistas = __DIR__ . '/../aplicacion/vistas/';

// --- 3. Lógica de Enrutado ---

// Agregamos el caso especial para procesar el login
if ($vista === 'procesar_login') {
    // Subimos un nivel desde public/ y entramos directo a procesos/
    $archivo = __DIR__ . '/../procesos/auth/procesar_login.php';
} elseif ($vista === 'inicio' || $vista === 'login' || $vista === 'trasmisionPublica') {
    // Vistas públicas
    $archivo = $baseVistas . 'web/' . $vista . '.php';

} elseif ($vista === 'dashboard' || $vista === 'logout') {
    if ($vista === 'dashboard') {
        $archivo = $baseVistas . 'admin/dashboard.php';
    } else {
        $archivo = __DIR__ . '/../procesos/auth/logout.php'; 
    }
} else {
    // Si la vista no es ninguna de las anteriores, asumimos que es una sección interna del dashboard
    $archivo = $baseVistas . 'admin/dashboard.php';
}

// 4. Verificación y Ejecución
if (file_exists($archivo)) {
    include $archivo;
} else {
    header("HTTP/1.0 404 Not Found");
    echo "<h3>Error 404</h3>";
    echo "No se encontró el recurso: <b>" . htmlspecialchars($vista) . "</b><br>";
    echo "Ruta de depuración: " . $archivo;
}

<?php
spl_autoload_register(function ($clase) {
    // Desde aplicacion/core/ subimos DOS niveles para llegar a la raíz
    $rutaBase = __DIR__ . '/../../';
    $archivo  = $rutaBase . str_replace('\\', '/', $clase) . '.php';

    if (file_exists($archivo)) {
        require_once $archivo;
    }
});
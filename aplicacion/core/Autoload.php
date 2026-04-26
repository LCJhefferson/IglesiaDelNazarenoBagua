<?php


spl_autoload_register(function ($clase) {
    // Definimos la ruta base del proyecto
    $rutaBase = __DIR__ . '/../'; 
    $archivo = $rutaBase . str_replace('\\', '/', $clase) . '.php';

    if (file_exists($archivo)) {
        require_once $archivo;
    }
});
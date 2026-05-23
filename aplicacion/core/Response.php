<?php
namespace aplicacion\core;

class Response {
    
    // Método para devolver respuestas JSON exitosas
    public static function json($data, $statusCode = 200) {
        if (ob_get_length()) ob_clean(); // Limpia cualquier texto previo
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // EL MÉTODO QUE FALTABA: Para devolver errores en formato JSON
    public static function error($mensaje, $statusCode = 400) {
        if (ob_get_length()) ob_clean();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => $mensaje
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
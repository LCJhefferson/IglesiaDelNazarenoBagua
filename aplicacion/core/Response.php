<?php
namespace aplicacion\core;

class Response {

    public static function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, int $status = 200): void {
        self::json(['success' => true, 'data' => $data], $status);
    }

    public static function created(mixed $data = null): void {
        self::success($data, 201);
    }

    public static function error(string $message, int $status = 400, mixed $errors = null): void {
        $body = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        self::json($body, $status);
    }

    public static function notFound(string $message = 'Recurso no encontrado'): void {
        self::error($message, 404);
    }

    public static function unprocessable(array $errors): void {
        self::error('Error de validación', 422, $errors);
    }
}

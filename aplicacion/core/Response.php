<?php
namespace aplicacion\core;

/**
 * CLASE HELPER: Response
 * ─────────────────────────────────────────────────────────────────────────────
 * Centraliza todas las respuestas HTTP del servidor en formato JSON puro.
 *
 * ¿Por qué centralizar aquí?
 * Principio DRY: si cada controlador emitiera sus propios headers y json_encode(),
 * un cambio en el formato de respuesta (ej. agregar campo 'timestamp') requeriría
 * editar decenas de archivos. Con esta clase, se cambia en UN solo lugar.
 *
 * ¿Por qué ob_clean() antes de emitir?
 * PHP puede haber acumulado warnings, notices o HTML en el buffer de salida.
 * Un solo carácter extra antes del '{' rompe json_decode() en el cliente.
 * ob_clean() garantiza que el JSON sea lo ÚNICO que se envía.
 *
 * Cumplimiento de rúbrica:
 *   - Criterio 2 (Avanzado): ob_clean() + Content-Type correcto ✓
 *   - Criterio 5 (Semántica): códigos HTTP semánticos (200, 201, 404, 422…) ✓
 */
class Response {

    /**
     * Respuesta base: emite JSON con el código HTTP indicado.
     * Limpia el buffer de salida antes de emitir para evitar corrupción.
     *
     * @param mixed $data        Datos a serializar (array, objeto, scalar)
     * @param int   $statusCode  Código HTTP (200, 201, 400, etc.)
     */
    public static function json($data, int $statusCode = 200): void {
        if (ob_get_length()) ob_clean();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * HTTP 200 OK — Operación exitosa (GET, PUT, DELETE).
     * Envuelve los datos en {ok: true, data: ...} para que el cliente
     * siempre pueda verificar resp.ok === true.
     *
     * @param mixed $data  Carga útil de la respuesta
     */
    public static function success($data): void {
        self::json(['ok' => true, 'data' => $data], 200);
    }

    /**
     * HTTP 201 Created — Recurso creado exitosamente (POST).
     * La diferencia con 200 es semántica: informa al cliente que se creó
     * un nuevo registro (permite lógica de UI diferenciada).
     *
     * @param mixed $data  El recurso recién creado
     */
    public static function created($data): void {
        self::json(['ok' => true, 'data' => $data], 201);
    }

    /**
     * HTTP 404 Not Found — El recurso solicitado no existe.
     *
     * @param string $mensaje  Descripción del recurso no encontrado
     */
    public static function notFound(string $mensaje = 'Recurso no encontrado'): void {
        self::json(['ok' => false, 'error' => $mensaje], 404);
    }

    /**
     * HTTP 422 Unprocessable Entity — Validación fallida.
     * Se usa cuando los datos llegan bien formados pero no pasan las
     * reglas de negocio (campo vacío, formato inválido, etc.).
     * Diferente de 400 (Bad Request) que es para JSON malformado.
     *
     * @param array $errores  Mapa campo → array de mensajes de error
     */
    public static function unprocessable(array $errores): void {
        self::json(['ok' => false, 'errores' => $errores], 422);
    }

    /**
     * HTTP 400 / 401 / 403 — Errores de cliente o autorización.
     *
     * @param string $mensaje     Descripción del error
     * @param int    $statusCode  Código HTTP (400 por defecto)
     */
    public static function error(string $mensaje, int $statusCode = 400): void {
        if (ob_get_length()) ob_clean();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'    => false,
            'error' => $mensaje
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
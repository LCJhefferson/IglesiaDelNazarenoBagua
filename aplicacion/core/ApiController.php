<?php
namespace aplicacion\core;


abstract class ApiController {

    /**
    
     * Flujo:
     *   1. Si Content-Type es application/json → lee php://input y decodifica JSON
     *   2. Si es POST normal (form-data) → usa $_POST
     *   3. Para PUT/DELETE con form-urlencoded → parse_str sobre php://input
     *
     * @return array Datos del cuerpo deserializados
     */
    protected function parseBody(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }

        // Si es POST real o un PUT simulado que viene con form-data (ej. _method=PUT)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_POST)) {
            return $_POST;
        }

        // PUT / DELETE con form-urlencoded
        parse_str(file_get_contents('php://input'), $data);
        return $data;
    }

    /**
     * Verifica que el verbo HTTP de la petición sea el esperado.
     * Si no coincide, responde 405 Method Not Allowed y termina.
     * REST exige que cada verbo (GET, POST, PUT, DELETE) tenga semántica
     * diferente. Sin esta verificación, un endpoint PUT podría ejecutarse
     * accidentalmente con una petición GET, rompiendo la semántica REST.
     *
     * @param string $method El verbo esperado ('GET', 'POST', 'PUT', 'DELETE')
     */
    protected function requireMethod(string $method): void {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            Response::error(
                'Método no permitido. Se esperaba: ' . strtoupper($method),
                405
            );
        }
    }

    /**
     * Emite una respuesta JSON limpia.
     * Llama ob_clean() antes de emitir para evitar que warnings o echos
     * previos corrompan el JSON (un solo carácter extra rompe json_decode).
     *
     * Este método es un alias conveniente a Response::json() para uso
     * interno dentro de los controladores hijos.
     *
     * @param mixed $data       Datos a serializar
     * @param int   $statusCode Código HTTP (200, 201, etc.)
     */
    protected function json($data, int $statusCode = 200): void {
        Response::json($data, $statusCode);
    }
}

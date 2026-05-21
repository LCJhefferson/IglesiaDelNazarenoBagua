<?php
namespace aplicacion\core;

class Router {

    private array  $routes   = [];
    private string $basePath;

    public function __construct(string $basePath = '/IglesiaDelNazarenoBagua') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $path, callable|array $handler): void {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): void {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void {
        $this->add('DELETE', $path, $handler);
    }

    private function add(string $method, string $path, callable|array $handler): void {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $this->toRegex($path),
            'handler' => $handler,
        ];
    }

    /**
     * Convierte {param} en grupos de captura nombrados para preg_match.
     * Ejemplo: /api/recursos/{id}  →  #^/base/api/recursos/(?P<id>[^/]+)$#
     */
    private function toRegex(string $path): string {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $this->basePath . $pattern . '$#';
    }

    /**
     * Despacha la petición actual contra las rutas registradas.
     * Llama al handler con un array de parámetros extraídos de la URL.
     * Si ninguna ruta coincide, responde 404 JSON.
     */
    public function dispatch(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        // Soporte de override de método vía campo oculto _method
        if ($method === 'POST' && !empty($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = strtok($_SERVER['REQUEST_URI'], '?');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $this->call($route['handler'], $params);
            return;
        }

        Response::error('Ruta no encontrada', 404);
    }

    private function call(callable|array $handler, array $params): void {
        if (is_callable($handler)) {
            $handler($params);
            return;
        }
        [$class, $method] = $handler;
        (new $class())->$method($params);
    }
}

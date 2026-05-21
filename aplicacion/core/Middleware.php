<?php
namespace aplicacion\core;

class Middleware {

    /**
     * Protege vistas HTML: redirige al login si no hay sesión válida.
     * $rolesPermitidos vacío = cualquier rol autenticado.
     */
    public static function auth(array $rolesPermitidos = [1, 2]): void {
        self::startSession();
        if (empty($_SESSION['usuario'])) {
            header('Location: /IglesiaDelNazarenoBagua/login');
            exit;
        }
        if (!empty($rolesPermitidos) && !in_array($_SESSION['rol_id'], $rolesPermitidos, true)) {
            header('Location: /IglesiaDelNazarenoBagua/login?error=3');
            exit;
        }
    }

    /**
     * Protege endpoints API: responde JSON 401/403 si no hay sesión válida.
     */
    public static function apiAuth(array $rolesPermitidos = [1, 2]): void {
        self::startSession();
        if (empty($_SESSION['usuario'])) {
            Response::error('No autorizado', 401);
        }
        if (!empty($rolesPermitidos) && !in_array($_SESSION['rol_id'], $rolesPermitidos, true)) {
            Response::error('Acceso denegado', 403);
        }
    }

    /** Genera (o reutiliza) el token CSRF de la sesión y lo devuelve. */
    public static function csrfGenerate(): string {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token CSRF (POST field «csrf_token» o header «X-CSRF-Token»).
     * Si falla, responde JSON 403 y termina la ejecución.
     */
    public static function csrfVerify(): void {
        self::startSession();
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            Response::error('Token CSRF inválido', 403);
        }
    }

    private static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

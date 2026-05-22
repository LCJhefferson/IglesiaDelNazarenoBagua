<?php
namespace aplicacion\controladores\api;

use aplicacion\core\Response;
use aplicacion\dao\UserDAO;

class AuthApiController {

    // ── POST /api/login ───────────────────────────────────────────────────────
    public function login(array $params = []): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Método no permitido', 405);
        }

        $data = $this->parseBody();

        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        if ($username === '' || $password === '') {
            Response::unprocessable([
                'username' => $username === '' ? ['El campo username es requerido'] : [],
                'password' => $password === '' ? ['El campo password es requerido'] : [],
            ]);
        }

        $dao  = new UserDAO();
        $user = $dao->buscarParaLogin($username);

        if (!$user || !password_verify($password, $user['password'])) {
            Response::error('Credenciales inválidas', 401);
        }

        session_regenerate_id(true);

        $_SESSION['usuario']    = $user['username'];
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['rol_id']     = $user['id_rol'];
        $_SESSION['rol_nombre'] = $user['rol_nombre'];

        Response::success([
            'id'         => $user['id'],
            'username'   => $user['username'],
            'rol_id'     => $user['id_rol'],
            'rol_nombre' => $user['rol_nombre'],
        ]);
    }

    // ── POST /api/logout ──────────────────────────────────────────────────────
    public function logout(array $params = []): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        Response::success(['mensaje' => 'Sesión cerrada correctamente']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseBody(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        return $_POST;
    }
}

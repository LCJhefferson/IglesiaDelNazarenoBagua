<?php
namespace aplicacion\controladores;

use aplicacion\dao\UserDAO;

class AuthController {

    private const URL_BASE = '/IglesiaDelNazarenoBagua/';
    private UserDAO $dao;

    public function __construct() {
        $this->dao = new UserDAO();
    }

    public function login(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }
        \aplicacion\core\Middleware::csrfVerify();

        $usuario  = trim($_POST['usuario']  ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($usuario) || empty($password)) {
            $this->redirect('login?error=1');
        }

        $resultado = $this->dao->buscarParaLogin($usuario);

        if (!$resultado) {
            $this->redirect('login?error=3');
        }

        if (!password_verify($password, $resultado['password'])) {
            $this->redirect('login?error=2');
        }

        session_regenerate_id(true);
        $_SESSION['usuario']    = $resultado['username'];
        $_SESSION['rol_id']     = $resultado['id_rol'];
        $_SESSION['rol_nombre'] = $resultado['rol_nombre'];

        if (in_array((int) $resultado['id_rol'], [1, 2], true)) {
            $this->redirect('dashboard');
        } else {
            $this->redirect('login?error=3');
        }
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        $this->redirect('login');
    }

    public function registrar(): void {
        $controller = new RegistroController();
        $ok = $controller->registrar(
            $_POST['username'] ?? '',
            $_POST['password'] ?? '',
            (int) ($_POST['rol']   ?? 3),
            $_POST['estado']   ?? 'activo'
        );

        $this->redirect($ok
            ? 'dashboard?seccion=usuarios_admin&exito=1'
            : 'dashboard?seccion=usuarios_admin&error=1'
        );
    }

    private function redirect(string $ruta): void {
        header('Location: ' . self::URL_BASE . $ruta);
        exit;
    }
}

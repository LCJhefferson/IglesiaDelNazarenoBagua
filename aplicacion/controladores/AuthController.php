<?php
namespace aplicacion\controladores;

// Importamos el modelo de Eloquent
use aplicacion\modelos\Usuario;

class AuthController {

    private const URL_BASE = '/IglesiaDelNazarenoBagua/';

    public function login(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }

        // Verificamos el token de seguridad
        \aplicacion\core\Middleware::csrfVerify();

        // 1. CAPTURAMOS LOS CAMPOS (Usando 'usuario' como viene de tu formulario)
        $usuarioVal  = trim($_POST['usuario']  ?? '');
        $passwordVal = trim($_POST['password'] ?? '');

        // Validación de campos vacíos
        if (empty($usuarioVal) || empty($passwordVal)) {
            $this->redirect('login?error=1');
        }

        $user = Usuario::where('username', $usuarioVal)
                       ->where('estado', 'activo')
                       ->first();

        if (!$user) {
            $this->redirect('login?error=3'); // Usuario no existe o inactivo
        }

        if (!password_verify($passwordVal, $user->password)) {
            $this->redirect('login?error=2'); // Contraseña incorrecta
        }

        session_regenerate_id(true);
        $_SESSION['usuario']    = $user->username;
        $_SESSION['usuario_id'] = $user->id;
        $_SESSION['rol_id']     = $user->id_rol;

        // INICIALIZAMOS EL TIEMPO (Añade esta línea)///////////////////////////
        $_SESSION['ultima_actividad'] = time();

        if (in_array((int) $user->id_rol, [1, 2], true)) {
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

    private function redirect(string $ruta): void {
        header('Location: ' . self::URL_BASE . $ruta);
        exit;
    }
}
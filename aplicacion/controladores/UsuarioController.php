<?php
namespace aplicacion\controladores;

use aplicacion\modelos\Usuario;
use Illuminate\Database\Capsule\Manager as DB;

class UsuarioController {

    public function __construct() {
        // Inicializar si es necesario
    }

public function registrar($username, $password, $id_rol, $estado = 'activo'): bool {
        try {
            // Validación básica de campos requeridos
            if (empty($username) || empty($password) || empty($id_rol)) {
                return false;
            }

            // LE DECIMOS A MYSQL QUIÉN SOY ANTES DE GUARDAR
            $this->configurarUsuarioAuditoria();

            // Creamos el nuevo registro utilizando tu Modelo Eloquent
            $nuevoUsuario = Usuario::create([
                'username' => trim($username),
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'id_rol'   => intval($id_rol),
                'estado'   => $estado
            ]);

            return (bool)$nuevoUsuario;

        } catch (\Exception $e) {
            // Puedes depurar temporalmente con un log si algo falla internamente:
            // error_log($e->getMessage());
            return false;
        }
    }
/**
 * Define en la conexión de MySQL quién está haciendo la acción
 */
private function configurarUsuarioAuditoria() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // ACCESO CORREGIDO:
    // Tu sistema guarda el usuario como objeto en $_SESSION['usuario']
    // Intentamos acceder a la propiedad ->id del objeto
    $usuarioLogueadoId = 0;
    
    if (isset($_SESSION['usuario']) && is_object($_SESSION['usuario'])) {
        $usuarioLogueadoId = $_SESSION['usuario']->id ?? 0;
    } elseif (isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
        $usuarioLogueadoId = $_SESSION['usuario']['id'] ?? 0;
    }
    
    // Si sigue en 0 (error de sesión), forzamos un valor de seguridad o registramos el error
    if ($usuarioLogueadoId == 0) {
        // Opcional: loguear en un archivo de error que el ID no se pudo obtener
    }
    
    // Ejecuta la sentencia en MySQL
    DB::statement("SET @usuario_actual_id = ?", [$usuarioLogueadoId]);
}

    public function desactivar($id): bool {
        try {
            if (empty($id)) {
                return false;
            }

            // LE DECIMOS A MYSQL QUIÉN SOY ANTES DE GUARDAR
            $this->configurarUsuarioAuditoria();

            $usuario = Usuario::find($id);
            if ($usuario) {
                $usuario->estado = 'inactivo';
                return $usuario->save();
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function actualizar($id, $username, $rol, $estado): bool {
        try {
            if (empty($id) || empty($username)) {
                return false;
            }

            // LE DECIMOS A MYSQL QUIÉN SOY ANTES DE GUARDAR
            $this->configurarUsuarioAuditoria();

            $usuario = Usuario::find($id);
            if ($usuario) {
                $usuario->username = $username;
                $usuario->id_rol   = $rol;
                $usuario->estado   = $estado;
                return $usuario->save();
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function cambiarPassword($id, $nuevaPassword): bool {
        try {
            if (empty($id) || empty($nuevaPassword)) {
                return false;
            }

            // LE DECIMOS A MYSQL QUIÉN SOY ANTES DE GUARDAR
            $this->configurarUsuarioAuditoria();

            $usuario = Usuario::find($id);
            if ($usuario) {
                $usuario->password = password_hash($nuevaPassword, PASSWORD_BCRYPT);
                return $usuario->save();
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
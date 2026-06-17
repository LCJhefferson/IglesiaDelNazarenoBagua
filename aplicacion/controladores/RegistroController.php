<?php
namespace aplicacion\controladores;

use aplicacion\modelos\Usuario;

class RegistroController {

    public function registrar($username, $password, $id_rol, $estado): bool {
        try {
            // Verificar si el usuario ya existe
            if (Usuario::where('username', $username)->exists()) {
                return false;
            }

            // Encriptar la contraseña de forma segura
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Crear el nuevo usuario usando Eloquent
            Usuario::create([
                'username' => trim($username),
                'password' => $hashedPassword,
                'id_rol'   => (int)$id_rol,
                'estado'   => $estado
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
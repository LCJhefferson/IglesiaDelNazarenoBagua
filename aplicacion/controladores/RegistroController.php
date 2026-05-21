<?php
namespace aplicacion\controladores;

use aplicacion\modelos\UserLogin;
use aplicacion\dao\UserDAO;

class RegistroController {

    private $userDAO;

    public function __construct() {
        $this->userDAO = new UserDAO();
    }

    public function registrar($username, $password, $id_rol, $estado): bool {
        try {
            $user = new UserLogin($username, $password, $id_rol, $estado);
            return $this->userDAO->registrar($user);
        } catch (\Exception $e) {
            return false;
        }
    }
}
<?php
namespace aplicacion\controladores;

use aplicacion\modelos\userLogin;
use aplicacion\dao\userDAO;

class RegistroController {

    private $userDAO;

    public function __construct() {
        $this->userDAO = new userDAO();
    }

    public function registrar($username, $password, $id_rol, $estado): bool {
        try {
            $user = new userLogin($username, $password, $id_rol, $estado);
            return $this->userDAO->registrar($user);
        } catch (\Exception $e) {
            return false;
        }
    }
}
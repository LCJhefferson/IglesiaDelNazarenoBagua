<?php
namespace aplicacion\controladores;

use aplicacion\dao\GrupoDAO;

class GrupoController {
    private $dao;

    public function __construct() {
        $this->dao = new GrupoDAO();
    }

    public function listar() {
        return $this->dao->listarTodos();
    }
}
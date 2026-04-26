<?php
namespace controladores;

use dao\VisitaDAO;
use modelos\Visita;

class VisitaController {
    private $dao;

    public function __construct() {
        $this->dao = new VisitaDAO();
    }

    public function listar() {
        // Pide al DAO las visitas con nombres de miembros y usuarios
        return $this->dao->listarConDetalles();
    }

    public function registrarVisita() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $visita = new Visita($_POST);
            // La lógica aquí procesa el formulario antes de enviarlo al DAO
            // if($this->dao->guardar($visita)) ...
        }
    }
}
<?php
namespace aplicacion\controladores;

use aplicacion\dao\MiembroDAO;
use aplicacion\modelos\Miembro;

class MiembroController {
    private $dao;

    public function __construct() {
        $this->dao = new MiembroDAO();
    }

    public function manejarPeticion() {
        // 1. Acción: Registrar
        if (isset($_POST['registrar'])) {
            $cargos = $_POST['cargos'] ?? [];
            // Limpiamos $_POST de campos que no van a la tabla 'miembros'
            unset($_POST['registrar'], $_POST['cargos'], $_POST['id']); 
            $this->dao->registrar($_POST, $cargos);
            $this->redireccionar();
        }

        // 2. Acción: Editar
        if (isset($_POST['editar'])) {
            $cargos = $_POST['cargos'] ?? [];
            unset($_POST['editar'], $_POST['cargos']);
            $this->dao->actualizarConCargos($_POST, $cargos);
            $this->redireccionar();
        }

        // 3. Acción: Eliminar (Desactivar)
        if (isset($_GET['eliminar'])) {
            $this->dao->eliminar($_GET['eliminar']);
            $this->redireccionar();
        }

        // 4. Acción: Activar
        if (isset($_GET['activar'])) {
            $this->dao->activar($_GET['activar']);
            $this->redireccionar();
        }
    }

    /**
     * Redirección unificada para evitar pantallas de error de ruteo
     */
    private function redireccionar() {
        header("Location: index.php?vista=dashboard&seccion=membresia");
        exit();
    }

    // --- MÉTODOS DE CONSULTA PARA LA VISTA ---

    public function listarMiembros() {
        return $this->dao->listar();
    }

    public function obtenerCargos() {
        return $this->dao->listarCargos();
    }

    public function obtenerCondiciones() {
        return $this->dao->listarCondiciones();
    }

    public function obtenerTipos() {
        return $this->dao->listarTipos(); 
    }
}
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
            $miembro = new Miembro($_POST);
            $datos = $miembro->toArray();
            
            // IMPORTANTE: Quitamos el ID para el INSERT (evita error en la DB)
            unset($datos['id']); 
            
            $this->dao->registrar($datos, $_POST['cargos'] ?? []);
            $this->redireccionar();
        }

        // 2. Acción: Editar
        if (isset($_POST['editar'])) {
            $miembro = new Miembro($_POST);
            // Aquí SI enviamos el ID porque el UPDATE lo necesita en el WHERE
            $this->dao->actualizarConCargos($miembro->toArray(), $_POST['cargos'] ?? []);
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
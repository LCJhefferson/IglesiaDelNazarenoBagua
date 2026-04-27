<?php
namespace aplicacion\controladores;

use aplicacion\dao\MiembroDAO;

class MiembroController {
    private $dao;

    public function __construct() {
        $this->dao = new MiembroDAO();
    }

    public function manejarPeticion() {
        // Registrar Nuevo Miembro
        if (isset($_POST['registrar'])) {
            $this->dao->registrar($this->mapearDatos());
            $this->redireccionar();
        }

        // Editar Miembro Existente
        if (isset($_POST['editar'])) {
            $datos = $this->mapearDatos();
            $datos['id'] = $_POST['id'];
            $this->dao->actualizar($datos);
            $this->redireccionar();
        }

        // Acción: Eliminar (Desactivar - Borrado Lógico)
        if (isset($_GET['eliminar'])) {
            $this->dao->eliminar($_GET['eliminar']);
            $this->redireccionar();
        }

        // Acción: Activar (Restaurar miembro inactivo)
        if (isset($_GET['activar'])) {
            $this->dao->activar($_GET['activar']);
            $this->redireccionar();
        }
    }

    /**
     * Mapea los datos del formulario POST a un array para el DAO.
     * Incluimos el campo 'estado' que agregamos a la vista.
     */
    private function mapearDatos() {
        return [
            'nombres'          => $_POST['nombres'] ?? '',
            'apellidos'        => $_POST['apellidos'] ?? '',
            'telefono'         => $_POST['telefono'] ?? '',
            'direccion'        => $_POST['direccion'] ?? '',
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
            'cargo_id'         => $_POST['cargo_id'] ?? null,
            'condicion_id'     => $_POST['condicion_id'] ?? null,
            'latitud'          => !empty($_POST['latitud']) ? $_POST['latitud'] : null,
            'longitud'         => !empty($_POST['longitud']) ? $_POST['longitud'] : null,
            'estado'           => $_POST['estado'] ?? 1 // Capturamos el nuevo campo de la vista
        ];
    }

    private function redireccionar() {
        header("Location: dashboard.php?vista=membresia");
        exit(); // Es buena práctica usar exit después de un redireccionamiento
    }

    public function listarMiembros() {
        return $this->dao->listar();
    }

    public function obtenerCargos() {
        return $this->dao->listarCargos();
    }

    public function obtenerCondiciones() {
        return $this->dao->listarCondiciones();
    }
}
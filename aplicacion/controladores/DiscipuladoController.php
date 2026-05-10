<?php
namespace aplicacion\controladores;

use aplicacion\dao\DiscipuladoDAO;

class DiscipuladoController {
    private $dao;

    public function __construct() {
        $this->dao = new DiscipuladoDAO();
    }

    /**
     * Procesa todas las operaciones POST y GET
     */
    public function manejarPeticion() {
        // --- ACCIONES PARA GRUPOS ---
        
        // Registrar o Editar Grupo
        // Verificamos si vienen las llaves 'registrar_grupo' o 'editar_grupo' desde el formulario
        if (isset($_POST['registrar_grupo']) || isset($_POST['editar_grupo'])) {
            
            // Recolectamos los datos. Importante: 'id' viene del input hidden 'grupo_id' 
            // pero su atributo 'name' en el HTML es 'id'
            $datos = [
                'id'              => !empty($_POST['id']) ? intval($_POST['id']) : null,
                'nombre'          => trim($_POST['nombre']),
                'nivel'           => $_POST['nivel'],
                'discipulador_id' => intval($_POST['discipulador_id']),
                'estado_id'       => intval($_POST['estado_id'])
            ];

            if (isset($_POST['registrar_grupo'])) {
                // Lógica de Registro
                $this->dao->registrarGrupo($datos);
            } else if (isset($_POST['editar_grupo']) && $datos['id'] !== null) {
                // Lógica de Actualización
                $this->dao->actualizarGrupo($datos);
            }
            
            // Redireccionamos para limpiar el POST y evitar re-envíos al actualizar la página
            $this->redireccionar('DiscipuladoGrupos');
        }

        // Eliminar Grupo
        if (isset($_GET['eliminar_grupo'])) {
            $id = intval($_GET['eliminar_grupo']);
            // Asegúrate de que el DAO tenga este método para evitar errores
            if (method_exists($this->dao, 'eliminarGrupo')) {
                $this->dao->eliminarGrupo($id);
            }
            $this->redireccionar('DiscipuladoGrupos');
        }

        // --- ACCIONES PARA INTEGRANTES ---

        // Asignar integrante(s) a un grupo (MODIFICADO PARA MULTIPLES)
        if (isset($_POST['asignar_integrante'])) {
            $miembros_ids = $_POST['miembro_id'] ?? []; // Recibe el array de IDs
            $grupo_id     = intval($_POST['grupo_id']);
            
            // Validamos que sea un array y no esté vacío
            if (is_array($miembros_ids) && !empty($miembros_ids)) {
                foreach ($miembros_ids as $m_id) {
                    $id_limpio = intval($m_id);
                    if ($id_limpio > 0) {
                        // Registramos cada miembro al mismo grupo
                        $this->dao->agregarMiembroAGrupo($id_limpio, $grupo_id);
                    }
                }
            }
            
            $this->redireccionar('DiscipuladoIntegrantes');
        }

        // Quitar integrante de un grupo
        if (isset($_GET['quitar_integrante'])) {
            $id_relacion = intval($_GET['quitar_integrante']);
            $this->dao->eliminarIntegranteDeGrupo($id_relacion);
            $this->redireccionar('DiscipuladoIntegrantes');
        }
    }

    /**
     * Prepara los datos para las vistas según la sección
     */
    public function obtenerDatosVista($seccion) {
        // Datos comunes que requieren ambas vistas (combos/selects)
        $datos = [
            'discipuladores' => $this->dao->listarDiscipuladores(),
            'estados'        => $this->dao->listarEstados()
        ];

        if ($seccion === 'DiscipuladoGrupos') {
            $datos['grupos'] = $this->dao->listarGrupos();
        } 
        
        if ($seccion === 'DiscipuladoIntegrantes') {
            // Capturamos filtros de la URL para persistencia si fuera necesario
            $busqueda = $_GET['busq'] ?? '';
            $nivel    = $_GET['nivel'] ?? '';
            $lider    = $_GET['lider'] ?? '';
            
            $datos['integrantes']    = $this->dao->listarIntegrantesDetallado($busqueda, $nivel, $lider);
            $datos['todos_miembros'] = $this->dao->listarTodosMiembrosActivos();
            $datos['todos_grupos']   = $this->dao->listarGrupos(); 
        }

        return $datos;
    }

    /**
     * Redirección centralizada
     */
    private function redireccionar($seccion) {
        // IMPORTANTE: Verifica que 'dashboard' sea el nombre correcto de tu archivo principal
        // Usamos una ruta relativa simple para evitar conflictos de carpetas
        header("Location: dashboard?seccion=" . $seccion);
        exit();
    }
}
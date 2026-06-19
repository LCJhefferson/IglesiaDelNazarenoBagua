<?php
namespace aplicacion\controladores;

use aplicacion\modelos\GrupoDiscipulado;
use aplicacion\modelos\IntegranteDiscipulado;
use aplicacion\modelos\Miembro;
use aplicacion\modelos\EstadoGrupoDiscipulado;
use aplicacion\modelos\EstadoDiscipulo; // Importamos el nuevo modelo

class DiscipuladoController {

    public function manejarPeticion() {
        // --- GESTIÓN DE GRUPOS ---
        if (isset($_POST['registrar_grupo']) || isset($_POST['editar_grupo'])) {
            $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
            $datos = [
                'nombre'          => trim($_POST['nombre']),
                'nivel'           => $_POST['nivel'] ?? 'I',
                'discipulador_id' => (!empty($_POST['discipulador_id'])) ? intval($_POST['discipulador_id']) : null,
                'estado_id'       => (!empty($_POST['estado_id'])) ? intval($_POST['estado_id']) : 1 
            ];

            try {
                if ($id) {
                    GrupoDiscipulado::where('id', $id)->update($datos);
                    $this->redireccionar('DiscipuladoGrupos', 'actualizado');
                } else {
                    GrupoDiscipulado::create($datos);
                    $this->redireccionar('DiscipuladoGrupos', 'creado');
                }
                $this->redireccionar('DiscipuladoGrupos', 'success');
            } catch (\Exception $e) {
                $this->redireccionar('DiscipuladoGrupos', 'error');
            }
        }

        if (isset($_GET['eliminar_grupo'])) {
            try {
                $id = intval($_GET['eliminar_grupo']);
                IntegranteDiscipulado::where('grupo_id', $id)->delete();
                GrupoDiscipulado::destroy($id);
                $this->redireccionar('DiscipuladoGrupos', 'eliminado');
            } catch (\Exception $e) {
                $this->redireccionar('DiscipuladoGrupos', 'error');
            }
        }
// --- GESTIÓN DE INTEGRANTES ---

// 1. Acción: Actualizar Estado del Alumno individual
if (isset($_POST['actualizar_estado_integrante'])) {
    try {
        $integrante_id = intval($_POST['integrante_id']);
        $nuevo_estado  = intval($_POST['estado_discipulo_id']);

        IntegranteDiscipulado::where('id', $integrante_id)->update([
            'estado_discipulo_id' => $nuevo_estado
        ]);
        // CAMBIADO: Antes decía 'success', ahora manda 'actualizado'
        $this->redireccionar('DiscipuladoIntegrantes', 'actualizado'); 
    } catch (\Exception $e) {
        $this->redireccionar('DiscipuladoIntegrantes', 'error');
    }
}

// 2. Acción: Asignar Miembro a Grupo
if (isset($_POST['asignar_integrante'])) {
    try {
        $miembros_ids = $_POST['miembro_id'] ?? [];
        $grupo_id     = intval($_POST['grupo_id']);

        foreach ($miembros_ids as $m_id) {
            IntegranteDiscipulado::firstOrCreate([
                'miembro_id' => intval($m_id),
                'grupo_id'   => $grupo_id
            ], [
                'estado_discipulo_id' => 1 
            ]);
        }
        // CAMBIADO: Antes decía 'success', ahora manda 'asignado'
        $this->redireccionar('DiscipuladoIntegrantes', 'asignado');
    } catch (\Exception $e) {
        $this->redireccionar('DiscipuladoIntegrantes', 'error');
    }
}

// 3. Acción: Quitar Integrante del Grupo
if (isset($_GET['quitar_integrante'])) {
    try {
        IntegranteDiscipulado::destroy(intval($_GET['quitar_integrante']));
        // CAMBIADO: Antes decía 'success', ahora manda 'eliminado'
        $this->redireccionar('DiscipuladoIntegrantes', 'eliminado');
    } catch (\Exception $e) {
        $this->redireccionar('DiscipuladoIntegrantes', 'error');
    }
}

    }

    public function obtenerDatosVista($seccion) {
        // 1. Todos los discipuladores activos (Útil para crear/editar en el modal)
        $todosDiscipuladores = Miembro::whereHas('cargos', function($q) {
            $q->where('nombre', 'LIKE', '%Líder%')
            ->orWhere('nombre', 'LIKE', '%Discipulador%');
        })->where('estado', 1)->orderBy('nombres', 'ASC')->get();

        // 2. Solo los discipuladores que ya tienen un grupo asignado (Útil para la barra de filtros)
        $discipuladoresConGrupo = Miembro::whereHas('cargos', function($q) {
            $q->where('nombre', 'LIKE', '%Líder%')
            ->orWhere('nombre', 'LIKE', '%Discipulador%');
        })
        ->whereHas('gruposDiscipulado') // El método que agregamos en Miembro.php
        ->where('estado', 1)
        ->orderBy('nombres', 'ASC')
        ->get();

        $datos = [
            'estados' => EstadoGrupoDiscipulado::all(),
            'discipuladores_filtros' => $discipuladoresConGrupo, // Para la barra superior
            'discipuladores_todos'   => $todosDiscipuladores     // Para el modal
        ];

        if ($seccion === 'DiscipuladoGrupos') {
            $datos['grupos'] = GrupoDiscipulado::with(['discipulador', 'estado'])
                                ->withCount('integrantes')
                                ->orderBy('id', 'DESC')
                                ->get();
        }

        if ($seccion === 'DiscipuladoIntegrantes') {
            $datos['integrantes']    = IntegranteDiscipulado::with(['miembro', 'grupo.discipulador', 'estadoAlumno'])->get();
            $datos['todos_miembros'] = Miembro::where('estado', 1)->orderBy('nombres')->get();
            $datos['todos_grupos']   = GrupoDiscipulado::all();
            $datos['estados_alumno'] = EstadoDiscipulo::all();
            
            // SOLUCIÓN AL WARNING: Se mapea la clave 'discipuladores' que busca el archivo de la vista
            $datos['discipuladores'] = $discipuladoresConGrupo;
        }

        return $datos;
    }

    /**
     * Redirecciona incluyendo parámetros de estado para activar las alertas Toasts
     */
    private function redireccionar($seccion, $status = null) {
        $url = "dashboard?seccion=" . $seccion;
        if ($status) {
            $url .= "&status=" . $status;
        }
        header("Location: " . $url);
        exit();
    }
}
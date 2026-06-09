<?php
namespace aplicacion\controladores;

use aplicacion\modelos\GrupoDiscipulado;
use aplicacion\modelos\IntegranteDiscipulado;
use aplicacion\modelos\Miembro;
use aplicacion\modelos\EstadoDiscipulado;

class DiscipuladoController {

    public function manejarPeticion() {
        // --- GESTIÓN DE GRUPOS ---
        
        // Registrar o Editar Grupo (¡AQUÍ ESTABA EL ERROR!)
        // Ahora escucha tanto cuando creas (registrar_grupo) como cuando editas (editar_grupo)
        if (isset($_POST['registrar_grupo']) || isset($_POST['editar_grupo'])) {
            
            // Aseguramos que el ID no esté vacío para que sepa que es una edición
            $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
            
            $datos = [
                'nombre'          => trim($_POST['nombre']),
                'nivel'           => $_POST['nivel'] ?? 'I',
                'discipulador_id' => (!empty($_POST['discipulador_id'])) ? intval($_POST['discipulador_id']) : null,
                'estado_id'       => (!empty($_POST['estado_id'])) ? intval($_POST['estado_id']) : 1 
            ];

            if ($id) {
                // Si hay ID, actualizamos
                GrupoDiscipulado::where('id', $id)->update($datos);
            } else {
                // Si no hay ID, creamos uno nuevo
                GrupoDiscipulado::create($datos);
            }
            $this->redireccionar('DiscipuladoGrupos');
        }

        // ... (El resto del código de eliminar_grupo, etc. se queda igual)

        // Eliminar Grupo
        if (isset($_GET['eliminar_grupo'])) {
            $id = intval($_GET['eliminar_grupo']);
            // Eliminar integrantes vinculados para evitar errores de integridad
            IntegranteDiscipulado::where('grupo_id', $id)->delete();
            GrupoDiscipulado::destroy($id);
            $this->redireccionar('DiscipuladoGrupos');
        }

        // --- GESTIÓN DE INTEGRANTES ---
        
        // Asignar Integrantes
        if (isset($_POST['asignar_integrante'])) {
            $miembros_ids = $_POST['miembro_id'] ?? [];
            $grupo_id     = intval($_POST['grupo_id']);

            foreach ($miembros_ids as $m_id) {
                IntegranteDiscipulado::firstOrCreate([
                    'miembro_id' => intval($m_id),
                    'grupo_id'   => $grupo_id
                ]);
            }
            $this->redireccionar('DiscipuladoIntegrantes');
        }

        // Quitar Integrante del grupo
        if (isset($_GET['quitar_integrante'])) {
            IntegranteDiscipulado::destroy(intval($_GET['quitar_integrante']));
            $this->redireccionar('DiscipuladoIntegrantes');
        }
    }

    public function obtenerDatosVista($seccion) {
        $datos = [
            'estados' => EstadoDiscipulado::all(),
            // Filtrar miembros activos que tengan cargos relacionados con liderazgo
            'discipuladores' => Miembro::whereHas('cargos', function($q) {
                $q->where('nombre', 'LIKE', '%Líder%')
                  ->orWhere('nombre', 'LIKE', '%Discipulador%');
            })->where('estado', 1)->get()
        ];

        if ($seccion === 'DiscipuladoGrupos') {
            $datos['grupos'] = GrupoDiscipulado::with(['discipulador', 'estado'])
                                ->withCount('integrantes')
                                ->orderBy('id', 'DESC')
                                ->get();
        }

        if ($seccion === 'DiscipuladoIntegrantes') {
            $datos['integrantes'] = IntegranteDiscipulado::with(['miembro', 'grupo.discipulador'])->get();
            $datos['todos_miembros'] = Miembro::where('estado', 1)->orderBy('nombres')->get();
            $datos['todos_grupos']   = GrupoDiscipulado::all();
        }

        return $datos;
    }

    /**
     * Redirección limpia para evitar que el navegador reenvíe formularios al refrescar
     * y para que el enrutador de index.php no se pierda.
     */
    private function redireccionar($seccion) {
        // Importante: No incluir "public/" ni "/" al inicio para que el index.php trabaje bien
        header("Location: dashboard?seccion=" . $seccion);
        exit();
    }
}
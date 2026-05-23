<?php
namespace aplicacion\dao;

use aplicacion\modelos\Miembro;
use Illuminate\Database\Capsule\Manager as DB;

class MiembroDAO {

    public function listar() {
        return Miembro::with(['cargos'])->orderBy('id', 'DESC')->get();
    }

    public function registrar(array $datos, array $cargosIds) {
        return DB::transaction(function() use ($datos, $cargosIds) {
            $miembro = Miembro::create($datos);
            if (!empty($cargosIds)) {
                $miembro->cargos()->attach($cargosIds);
            }
            return $miembro;
        });
    }

    public function actualizarConCargos(array $datos, array $cargosIds) {
        return DB::transaction(function() use ($datos, $cargosIds) {
            $miembro = Miembro::findOrFail($datos['id']);
            $miembro->update($datos);
            $miembro->cargos()->sync($cargosIds);
            return true;
        });
    }

    public function eliminar($id) {
        return Miembro::where('id', $id)->update(['estado' => 0]);
    }

    public function activar($id) {
        return Miembro::where('id', $id)->update(['estado' => 1]);
    }

    public function listarCargos() {
        return DB::table('cargos')->orderBy('nombre', 'ASC')->get()->toArray();
    }

    public function listarCondiciones() {
        return DB::table('condiciones_miembro')->orderBy('nombre', 'ASC')->get()->toArray();
    }

    public function listarTipos() {
        return DB::table('tipos_miembro')->orderBy('id', 'ASC')->get()->toArray();
    }
}
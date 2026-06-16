<?php
namespace aplicacion\services\reports;

use Illuminate\Database\Capsule\Manager as Capsule;

class ReporteDiscipulado {

    /**
     * Procesa y genera los datos del reporte de discipulado con sus filtros dinámicos
     */
    public static function generar($filtros) {
        $query = Capsule::table('discipulado_integrantes as di')
            ->join('miembros as m', 'di.miembro_id', '=', 'm.id')
            ->join('discipulado_grupos as dg', 'di.grupo_id', '=', 'dg.id')
            ->join('estados_discipulo as ed', 'di.estado_discipulo_id', '=', 'ed.id')
            // CORRECCIÓN CRÍTICA: Se une con miembros (m2) porque discipulador_id apunta a un miembro
            ->leftJoin('miembros as m2', 'dg.discipulador_id', '=', 'm2.id')
            ->select(
                'di.id as integrante_id',
                Capsule::raw("CONCAT(m.nombres, ' ', m.apellidos) as integrante_nombre"),
                'dg.nombre as grupo_nombre',
                // Traemos el nombre correcto del líder asignado
                Capsule::raw("CONCAT(m2.nombres, ' ', m2.apellidos) as discipulador_nombre"),
                'ed.nombre as estado_alumno_texto',
                'di.estado_discipulo_id'
            );

        // Filtro por Miembro específico (Autocomplete)
        if (!empty($filtros['miembro_id'])) {
            $query->where('di.miembro_id', $filtros['miembro_id']);
        }

        // Filtro por Grupo específico (Autocomplete)
        if (!empty($filtros['grupo_id'])) {
            $query->where('di.grupo_id', $filtros['grupo_id']);
        }

        // Filtro por Discipulador / Líder específico (Autocomplete)
        if (!empty($filtros['discipulador_id'])) {
            $query->where('dg.discipulador_id', $filtros['discipulador_id']);
        }

        // Filtro por el Estado Individual del alumno (Select Dinámico)
        if (!empty($filtros['estado_discipulo_id'])) {
            $query->where('di.estado_discipulo_id', $filtros['estado_discipulo_id']);
        }

        return $query->orderBy('dg.nombre', 'ASC')
                     ->orderBy('integrante_nombre', 'ASC')
                     ->get()
                     ->map(function($item) {
                         return (array) $item;
                     })
                     ->toArray();
    }
}
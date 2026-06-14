<?php
namespace aplicacion\servicios\reportes;

use Illuminate\Database\Capsule\Manager as Capsule;

class ReporteVisitas {

    public static function generar($filtros) {
        $query = Capsule::table('visitas')
            ->join('miembros', 'visitas.miembro_id', '=', 'miembros.id')
            ->leftJoin('estados_visita', 'visitas.estado_id', '=', 'estados_visita.id')
            ->select(
                Capsule::raw("CONCAT(miembros.nombres, ' ', miembros.apellidos) as miembro_completo"),
                'miembros.direccion',
                'visitas.fecha_visita',
                'visitas.motivo',
                'estados_visita.nombre as estado_nombre',
                'visitas.estado as estado_visita_num'
            );

        // Filtro por Estado (Realizada / Pendiente)
        if (!empty($filtros['estado'])) {
            // Se puede buscar tanto por el texto del maestro estados_visita o el estado interno
            $query->where('estados_visita.nombre', $filtros['estado']);
        }

        // Filtro por Motivo de Visita
        if (!empty($filtros['motivo'])) {
            if ($filtros['motivo'] === 'Otros') {
                $query->whereNotIn('visitas.motivo', ['Visita Regular', 'Por Enfermedad', 'Evangelística']);
            } else {
                $query->where('visitas.motivo', $filtros['motivo']);
            }
        }

        // REQUERIMIENTO CAMBIADO: Rango de fechas manuales elegidas por el usuario
        if (!empty($filtros['fecha_inicio'])) {
            $query->where('visitas.fecha_visita', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->where('visitas.fecha_visita', '<=', $filtros['fecha_fin']);
        }

        $resultados = $query->get();
        $datosProcesados = [];

        foreach ($resultados as $r) {
            $datosProcesados[] = [
                'nombre_completo' => $r->miembro_completo,
                'direccion'       => $r->direccion ?: 'Sin dirección',
                'ultima_visita'   => date('d/m/Y', strtotime($r->fecha_visita)),
                'motivo'          => $r->motivo ?: 'Sin motivo',
                'estado'          => $r->estado_nombre ?: ($r->estado_visita_num == 1 ? 'Realizada' : 'Pendiente')
            ];
        }

        return $datosProcesados;
    }
}
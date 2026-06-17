<?php
namespace aplicacion\services\reports;

use Illuminate\Database\Capsule\Manager as Capsule;

class ReporteCumpleanos {

    public static function generar($filtros) {
        // Base de la consulta calculando la edad dinámicamente en SQL
        $query = Capsule::table('miembros')
            ->select(
                'id',
                Capsule::raw("CONCAT(nombres, ' ', apellidos) as nombre_completo"),
                'telefono',
                'fecha_nacimiento',
                Capsule::raw("TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad")
            )
            ->whereNotNull('fecha_nacimiento');

        // 1. Filtro por Mes (Modificado para soportar la opción 'todos')
        if (!empty($filtros['mes_cumpleanos'])) {
            if ($filtros['mes_cumpleanos'] !== 'todos') {
                $query->whereRaw('MONTH(fecha_nacimiento) = ?', [$filtros['mes_cumpleanos']]);
            }
        }

        // 2. Filtro por Miembro específico (Autocomplete)
        if (!empty($filtros['miembro_id'])) {
            $query->where('id', $filtros['miembro_id']);
        }

        // 3. Filtros por rangos de Edad
        if (!empty($filtros['edad_min'])) {
            $query->whereRaw("TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) >= ?", [$filtros['edad_min']]);
        }
        if (!empty($filtros['edad_max'])) {
            $query->whereRaw("TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) <= ?", [$filtros['edad_max']]);
        }

        // ORDENAMIENTO INTELIGENTE: 
        // Si eligen 'todos', se ordena por MES y luego por DÍA. Si eligen un mes, solo por DÍA.
        if (isset($filtros['mes_cumpleanos']) && $filtros['mes_cumpleanos'] === 'todos') {
            $query->orderByRaw('MONTH(fecha_nacimiento) ASC')
                  ->orderByRaw('DAY(fecha_nacimiento) ASC');
        } else {
            $query->orderByRaw('DAY(fecha_nacimiento) ASC');
        }

        return $query->get()
                     ->map(function($item) {
                         return (array) $item;
                     })
                     ->toArray();
    }
}
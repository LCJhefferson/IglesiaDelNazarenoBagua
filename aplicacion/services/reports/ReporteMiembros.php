<?php
namespace aplicacion\services\reports; // <-- Corregido a reports

use Illuminate\Database\Capsule\Manager as Capsule;

class ReporteMiembros {

    /**
     * Filtra y retorna los miembros según las selecciones del panel
     */
    public static function generar(array $filtros): array {
        $query = Capsule::table('miembros')
            ->leftJoin('condiciones_miembro', 'miembros.condicion_id', '=', 'condiciones_miembro.id')
            ->select([
                Capsule::raw("CONCAT(miembros.nombres, ' ', miembros.apellidos) as nombre_completo"),
                'miembros.telefono',
                Capsule::raw("TIMESTAMPDIFF(YEAR, miembros.fecha_nacimiento, CURDATE()) as edad"),
                'miembros.direccion',
                'miembros.tipo_miembro_id as origen', 
                Capsule::raw("COALESCE(condiciones_miembro.nombre, 'Sin asignar') as condicion"),
                Capsule::raw("CASE WHEN miembros.estado = 1 THEN 'Activo' ELSE 'Inactivo' END as estado")
            ]);

        // Filtro por Condición
        if (!empty($filtros['condicion'])) {
            $query->where('miembros.condicion_id', '=', $filtros['condicion']);
        }

        // Filtro por Estado
        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $query->where('miembros.estado', '=', (int)$filtros['estado']);
        }

        // Filtro Edad Mínima
        if (!empty($filtros['edad_min'])) {
            $query->whereRaw("TIMESTAMPDIFF(YEAR, miembros.fecha_nacimiento, CURDATE()) >= ?", [(int)$filtros['edad_min']]);
        }

        // Filtro Edad Máxima
        if (!empty($filtros['edad_max'])) {
            $query->whereRaw("TIMESTAMPDIFF(YEAR, miembros.fecha_nacimiento, CURDATE()) <= ?", [(int)$filtros['edad_max']]);
        }

        return $query->orderBy('miembros.apellidos', 'ASC')
                     ->orderBy('miembros.nombres', 'ASC')
                     ->get()
                     ->map(function($item) {
                         return (array) $item;
                     })
                     ->toArray();
    }
}
<?php
namespace aplicacion\servicios\reportes;

use Illuminate\Database\Capsule\Manager as Capsule;

class ReporteCumpleanos {

    public static function generar($filtros) {
        $query = Capsule::table('miembros')
            ->select('nombres', 'apellidos', 'fecha_nacimiento', 'telefono')
            ->whereNotNull('fecha_nacimiento');

        // Filtro por Mes de Onomástico
        if (!empty($filtros['mes_cumple'])) {
            $query->whereRaw('MONTH(fecha_nacimiento) = ?', [$filtros['mes_cumple']]);
        }

        // Filtro opcional por Rango Exacto de Fecha
        if (!empty($filtros['fecha_inicio'])) {
            $query->where('fecha_nacimiento', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->where('fecha_nacimiento', '<=', $filtros['fecha_fin']);
        }

        $resultados = $query->get();
        $datosProcesados = [];

        foreach ($resultados as $r) {
            $edad = date_diff(date_create($r->fecha_nacimiento), date_create('today'))->y;
            $datosProcesados[] = [
                'nombre_completo'  => $r->nombres . ' ' . $r->apellidos,
                'fecha_cumpleanos' => date('d/m/Y', strtotime($r->fecha_nacimiento)),
                'edad_actual'      => $edad . ' años',
                'telefono'         => $r->telefono ?: 'S/N'
            ];
        }

        return $datosProcesados;
    }
}
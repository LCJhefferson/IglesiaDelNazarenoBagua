<?php
namespace aplicacion\Repository;

use aplicacion\modelos\MiembroModelo;
use aplicacion\modelos\VisitaModelo;
use aplicacion\modelos\GrupoDiscipuladoModelo;

class ReporteRepository {

    public function obtenerDatosPorModulo($modulo, $filtros) {
        if ($modulo === 'miembros') {
            return $this->queryMiembros($filtros);
        } elseif ($modulo === 'visitas') {
            return $this->queryVisitas($filtros);
        } elseif ($modulo === 'discipulado') {
            return $this->queryDiscipulado($filtros);
        }
        return [];
    }

    private function queryMiembros($filtros) {
        $datos = [];
        $query = MiembroModelo::query();

        if (!empty($filtros['condicion'])) $query->where('condicion_id', $filtros['condicion']);
        if (isset($filtros['estado']) && $filtros['estado'] !== '') $query->where('estado', $filtros['estado']);
        
        if (!empty($filtros['edad_min']) || !empty($filtros['edad_max'])) {
            $min = !empty($filtros['edad_min']) ? (int)$filtros['edad_min'] : 0;
            $max = !empty($filtros['edad_max']) ? (int)$filtros['edad_max'] : 120;
            $fechaMax = date('Y-m-d', strtotime("-$min years"));
            $fechaMin = date('Y-m-d', strtotime("-$max years -1 day"));
            $query->whereBetween('fecha_nacimiento', [$fechaMin, $fechaMax]);
        }

        foreach ($query->get() as $r) {
            $edad = date_diff(date_create($r->fecha_nacimiento), date_create('today'))->y;
            $datos[] = [
                'nombre'    => $r->nombres . ' ' . $r->apellidos,
                'telefono'  => $r->telefono ?: 'S/N',
                'edad'      => $edad . ' años',
                'direccion' => $r->direccion ?: 'No registrada',
                'origen'    => $r->origen ?: 'Local',
                'condicion' => $r->condicion_nombre ?: 'Saludable',
                'estado'    => ((int)$r->estado === 1) ? 'Activo' : 'Inactivo'
            ];
        }
        return $datos;
    }

    private function queryVisitas($filtros) {
        $datos = [];
        $query = VisitaModelo::query();

        if (isset($filtros['estado']) && $filtros['estado'] !== '') $query->where('estado', $filtros['estado']);
        
        if (!empty($filtros['motivo'])) {
            if ($filtros['motivo'] === 'Otros') {
                $query->whereNotIn('motivo', ['Visita Regular', 'Por Enfermedad', 'Evangelística']);
            } else {
                $query->where('motivo', $filtros['motivo']);
            }
        }

        if (!empty($filtros['meses'])) {
            $meses = (int)$filtros['meses'];
            $query->where('fecha_visita', '>=', date('Y-m-d', strtotime("-$meses months")));
        }

        foreach ($query->get() as $r) {
            $datos[] = [
                'nombre'        => $r->miembro_nombre_completo,
                'direccion'    => $r->direccion_registro ?: 'Sin dirección',
                'ultima_visita'=> date('d/m/Y', strtotime($r->fecha_visita)),
                'motivo'       => $r->motivo,
                'estado'       => $r->estado
            ];
        }
        return $datos;
    }

    private function queryDiscipulado($filtros) {
        $datos = [];
        $query = GrupoDiscipuladoModelo::with(['integrantes.miembro', 'discipulador']);
        
        if (!empty($filtros['grupo_id'])) $query->where('id', $filtros['grupo_id']);
        if (!empty($filtros['discipulador_id'])) $query->where('discipulador_id', $filtros['discipulador_id']);
        if (!empty($filtros['nivel'])) $query->where('nivel', $filtros['nivel']);

        foreach ($query->get() as $g) {
            foreach ($g->integrantes as $i) {
                if (!empty($filtros['miembro_id']) && $i->miembro_id != $filtros['miembro_id']) continue;

                $estadoTxt = 'Activo / En proceso';
                if ($i->estado === 1) $estadoTxt = 'Aprobado';
                elseif ($i->estado === 0) $estadoTxt = 'Desaprobado / Retirado';

                $datos[] = [
                    'grupo_nombre'  => $g->nombre,
                    'nivel'         => $g->nivel,
                    'grupo_estado'  => $g->estado_actual ?: 'En proceso',
                    'discipulador'  => $g->discipulador ? ($g->discipulador->nombres) : 'Sin asignar',
                    'integrante'    => $i->miembro->nombres . ' ' . $i->miembro->apellidos,
                    'estado_alumno' => $estadoTxt
                ];
            }
        }
        return $datos;
    }
}
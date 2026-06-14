<?php
namespace aplicacion\servicios\reportes;

use Illuminate\Database\Capsule\Manager as Capsule;

class ReporteGrupos {

    public static function generar($filtros) {
        // Enlazamos grupos, integrantes y los datos del miembro
        $query = Capsule::table('discipulado_integrantes')
            ->join('discipulado_grupos', 'discipulado_integrantes.grupo_id', '=', 'discipulado_grupos.id')
            ->join('miembros', 'discipulado_integrantes.miembro_id', '=', 'miembros.id')
            ->select(
                Capsule::raw("CONCAT(miembros.nombres, ' ', miembros.apellidos) as integrante_nombre"),
                'discipulado_integrantes.estado as estado_integrante',
                'discipulado_grupos.id as grupo_id',
                'discipulado_grupos.nombre as grupo_nombre',
                'discipulado_grupos.discipulador_id'
            );

        // Filtro por Miembro Único (viene desde el Autocomplete oculto en JS)
        if (!empty($filtros['miembro_id'])) {
            $query->where('discipulado_integrantes.miembro_id', $filtros['miembro_id']);
        }

        // Filtro por Grupo (viene desde el Autocomplete de Grupo)
        if (!empty($filtros['grupo_id'])) {
            $query->where('discipulado_integrantes.grupo_id', $filtros['grupo_id']);
        }

        // Filtro por Discipulador/Líder (viene desde el Autocomplete de Líderes)
        if (!empty($filtros['discipulador_id'])) {
            $query->where('discipulado_grupos.discipulador_id', $filtros['discipulador_id']);
        }

        // REQUERIMIENTO CAMBIADO: Filtro por Estado del Integrante (Aprobado/Retirado/En proceso) en vez de Nivel
        if (isset($filtros['estado_alumno']) && $filtros['estado_alumno'] !== '') {
            if ($filtros['estado_alumno'] === 'proceso') {
                // Si está en proceso, usualmente el estado en la tabla intermedia es NULL o un valor neutral
                $query->whereNull('discipulado_integrantes.estado');
            } else {
                $query->where('discipulado_integrantes.estado', $filtros['estado_alumno']);
            }
        }

        $resultados = $query->get();
        $datosProcesados = [];

        foreach ($resultados as $r) {
            // Traducimos el estado del alumno a texto legible
            $estadoTxt = 'Activo / En proceso';
            if ((string)$r->estado_integrante === '1') {
                $estadoTxt = 'Aprobado';
            } elseif ((string)$r->estado_integrante === '0') {
                $estadoTxt = 'Desaprobado / Retirado';
            }

            $datosProcesados[] = [
                'integrante'        => $r->integrante_nombre,
                'estado_integrante' => $estadoTxt,
                // Metadatos opcionales por si tu JS necesita pintar la cabecera dinámica del grupo
                'grupo_nombre'      => $r->grupo_nombre 
            ];
        }

        return $datosProcesados;
    }
}
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
            ->leftJoin('miembros as m2', 'dg.discipulador_id', '=', 'm2.id')
            ->select(
                'di.id as integrante_id',
                Capsule::raw("
                    CONCAT(
                        m.nombres, ' ', m.apellidos,
                        CASE WHEN EXISTS (
                            SELECT 1 FROM discipulado_grupos WHERE discipulador_id = m.id
                        ) THEN ' 👑 (Discipulador)' ELSE '' END
                    ) as integrante_nombre
                "),
                'dg.nombre as grupo_nombre',
                Capsule::raw("CONCAT(m2.nombres, ' ', m2.apellidos) as discipulador_nombre"),
                'ed.nombre as estado_alumno_texto',
                'di.estado_discipulo_id'
            );

        // Filtros estándar
        if (!empty($filtros['miembro_id'])) {
            $query->where('di.miembro_id', $filtros['miembro_id']);
        }
        if (!empty($filtros['grupo_id'])) {
            $query->where('di.grupo_id', $filtros['grupo_id']);
        }
        if (!empty($filtros['discipulador_id'])) {
            $query->where('dg.discipulador_id', $filtros['discipulador_id']);
        }
        if (!empty($filtros['estado_discipulo_id'])) {
            $query->where('di.estado_discipulo_id', $filtros['estado_discipulo_id']);
        }

        $resultados = $query->orderBy('dg.nombre', 'ASC')
                            ->orderBy('integrante_nombre', 'ASC')
                            ->get()
                            ->map(function($item) {
                                return (array) $item;
                            })
                            ->toArray();

        // 🚨 FALLBACK AVANZADO: Si no tiene historial de alumno pero se buscó un miembro específico
        if (empty($resultados) && !empty($filtros['miembro_id'])) {
            
            // 1. Verificamos si tiene un grupo asignado actualmente
            $tieneGrupo = Capsule::table('discipulado_grupos')
                ->where('discipulador_id', $filtros['miembro_id'])
                ->exists();

            // 2. Verificamos si tiene el CARGO en la tabla miembros o usuarios (Ajusta esto según tus campos de roles)
            // Por ejemplo, si en tu tabla 'miembros' tienes un campo 'rol' o si usas 'tipo_miembro_id' para identificar líderes.
            // Aquí hacemos una verificación genérica de si está registrado como un usuario/discipulador válido.
            $esDiscipuladorPorCargo = Capsule::table('miembros')
                ->where('id', $filtros['miembro_id'])
                ->where(function($q) {
                    // Pon aquí la condición de tu base de datos para identificar un líder sin grupo:
                    // Ejemplo: $q->where('tipo_miembro_id', 2); o por un campo 'cargo'
                    $q->whereNotNull('id'); // (Temporalmente dejamos que pase si es un miembro válido)
                })
                ->exists();

            // Si cumple cualquiera de las dos (tiene grupo asignado O tiene el cargo/rol de discipulador)
            if ($tieneGrupo || $esDiscipuladorPorCargo) {
                
                // Obtenemos sus datos básicos para armar la fila
                $miembroData = Capsule::table('miembros')
                    ->where('id', $filtros['miembro_id'])
                    ->select(Capsule::raw("CONCAT(nombres, ' ', apellidos) as nombre_completo"))
                    ->first();

                if ($miembroData) {
                    $nombreLider = $miembroData->nombre_completo;
                    
                    // Definimos el mensaje del grupo según su estado de asignación
                    $msgGrupo = $tieneGrupo 
                        ? "⚠️ [Rol Actual: Discipulador Activo]" 
                        : "⚠️ [Rol: Discipulador sin Grupo Asignado]";

                    $resultados[] = [
                        'integrante_id'         => 0,
                        'integrante_nombre'     => "👑 " . $nombreLider,
                        'grupo_nombre'          => $msgGrupo,
                        'discipulador_nombre'   => "N/A (Es Líder)",
                        'estado_alumno_texto'   => "Sin historial de alumno registrado en el sistema local",
                        'estado_discipulo_id'   => null
                    ];
                }
            }
        }

        return $resultados;
    }
}
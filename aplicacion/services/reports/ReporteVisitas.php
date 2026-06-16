<?php
namespace aplicacion\services\reports;

use Illuminate\Database\Capsule\Manager as Capsule;
use DateTime;

class ReporteVisitas {

    /**
     * Genera la lista de visitas procesada con estados dinámicos y filtros aplicados
     */
    public static function generar($filtros) {
        // 1. Obtener la configuración del sistema para los límites de tiempo
        $resultadoConf = Capsule::table('configuracion_sistema')
            ->where('clave', 'meses_limite_visita')
            ->first();
        $mesesLimite = $resultadoConf ? intval($resultadoConf->valor) : 6;
        $diasLimiteTotal = ($mesesLimite > 0 ? $mesesLimite : 1) * 30;
        $hoy = new DateTime();

        // 2. Traer miembros activos mediante Query Builder Directo para blindar contra fallos del ORM
        $miembros = Capsule::table('miembros')
            ->where('estado', 1)
            ->select('id', 'nombres', 'apellidos', 'direccion')
            ->get();

        $datosProcesados = [];

        // 3. Procesar datos e inyectar estados dinámicos basados en la lógica del sistema
        foreach ($miembros as $m) {
            
            // Construimos la consulta para la última visita de este miembro en particular
            $queryVisita = Capsule::table('visitas')
                ->where('miembro_id', $m->id)
                ->where('estado', 1);

            // Aplicamos filtro por Motivo de Visita si se solicitó
            if (!empty($filtros['motivo']) && $filtros['motivo'] !== 'Todos') {
                if ($filtros['motivo'] === 'Otros') {
                    $queryVisita->whereNotIn('motivo', ['Visita Regular', 'Por Enfermedad', 'Evangelística']);
                } else {
                    $queryVisita->where('motivo', $filtros['motivo']);
                }
            }

            // Aplicamos filtros de rango de fechas si se solicitaron
            if (!empty($filtros['fecha_inicio'])) {
                $queryVisita->where('fecha_visita', '>=', $filtros['fecha_inicio']);
            }
            if (!empty($filtros['fecha_fin'])) {
                $queryVisita->where('fecha_visita', '<=', $filtros['fecha_fin']);
            }

            // Traemos únicamente la última visita registrada
            $ultimaVisita = $queryVisita->orderBy('fecha_visita', 'desc')->orderBy('id', 'desc')->first();

            // Si el usuario aplicó filtros (fechas/motivo) y este miembro no posee visitas bajo ese criterio, se descarta del reporte
            $tieneFiltrosActivos = !empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin']) || (!empty($filtros['motivo']) && $filtros['motivo'] !== 'Todos');
            if ($tieneFiltrosActivos && !$ultimaVisita) {
                continue;
            }

            $fechaReal = $ultimaVisita ? $ultimaVisita->fecha_visita : null;
            $estadoTexto = '';

            // Inyección lógica de estados dinámicos por semaforización de tiempos
            if (!empty($fechaReal)) {
                $fechaVisitaObj = new DateTime($fechaReal);
                $diferencia = $hoy->diff($fechaVisitaObj);
                $diasTranscurridos = $diferencia->days;
                
                if ($fechaVisitaObj > $hoy) $diasTranscurridos = 0;

                $porcentaje = ($diasTranscurridos / $diasLimiteTotal) * 100;

                if ($porcentaje <= 25) {
                    $estadoTexto = 'Visitado reciente';
                } elseif ($porcentaje <= 70) {
                    $estadoTexto = 'Visitado intermedio';
                } elseif ($porcentaje < 100) {
                    $estadoTexto = 'Pendiente próximo';
                } else {
                    $estadoTexto = 'Pendiente crítico';
                }
                $fechaFormateada = date('d/m/Y', strtotime($fechaReal));
            } else {
                $estadoTexto = 'Pendiente crítico';
                $fechaFormateada = 'Sin visitas';
            }

            // Filtro dinámico por Estado
            if (!empty($filtros['estado']) && $filtros['estado'] !== 'Todos') {
                if (trim(strtolower($filtros['estado'])) !== trim(strtolower($estadoTexto))) {
                    continue; 
                }
            }

            // Estructura limpia e indexada perfectamente para el JavaScript y los Exportadores
            $datosProcesados[] = [
                'nombre_completo' => trim($m->nombres . ' ' . $m->apellidos),
                'direccion'       => $m->direccion ?: 'Sin dirección',
                'ultima_visita'   => $fechaFormateada,
                'motivo'          => $ultimaVisita ? ($ultimaVisita->motivo ?: 'Sin motivo') : 'Sin visitas',
                'estado'          => $estadoTexto
            ];
        }

        // 4. Ordenar el resultado final: Visitas más recientes primero
        usort($datosProcesados, function($a, $b) {
            $fA = $a['ultima_visita'] === 'Sin visitas' ? 0 : strtotime(str_replace('/', '-', $a['ultima_visita']));
            $fB = $b['ultima_visita'] === 'Sin visitas' ? 0 : strtotime(str_replace('/', '-', $b['ultima_visita']));
            return $fB <=> $fA;
        });

        return $datosProcesados;
    }
}
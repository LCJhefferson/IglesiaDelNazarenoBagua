<?php
namespace aplicacion\controladores;

use aplicacion\modelos\Miembro;
use aplicacion\modelos\VisitaModelo;
use Illuminate\Database\Capsule\Manager as DB;
use DateTime;

class VisitaController {

    public function __construct() {
        // Constructor vacío - Usamos Eloquent Models directamente
    }

    /**
     * Obtiene el límite de meses configurado para el sistema
     */
    public function obtenerMesesLimite() {
        $resultado = DB::table('configuracion_sistema')
            ->where('clave', 'meses_limite_visita')
            ->first();
        return $resultado ? intval($resultado->valor) : 6;
    }

    /**
     * Lista las visitas con cálculos de estado para la tabla principal
     */
    public function listarConDetalles($modo = 'ultimo') {
        if ($modo === 'todos') {
            $visitas = VisitaModelo::with('miembro')
                ->where('estado', 1)
                ->whereHas('miembro', function($q) {
                    $q->where('estado', 1);
                })
                ->orderBy('fecha_visita', 'desc')
                ->get();

            $resultados = [];
            foreach ($visitas as $v) {
                $resultados[] = [
                    'ultima_visita_id' => $v->id,
                    'miembro_id'       => $v->miembro->id,
                    'miembro_nombre'   => trim($v->miembro->nombres . ' ' . $v->miembro->apellidos),
                    'direccion'        => $v->miembro->direccion,
                    'fecha_real'       => $v->fecha_visita,
                    'ultimo_motivo'    => $v->motivo
                ];
            }
        } else {
            $miembros = Miembro::where('estado', 1)
                ->with(['visitas' => function($q) {
                    $q->where('estado', 1)->orderBy('fecha_visita', 'desc')->orderBy('id', 'desc');
                }])
                ->get();

            $resultados = [];
            foreach ($miembros as $m) {
                $ultimaVisita = $m->visitas->first(); 
                $resultados[] = [
                    'miembro_id'       => $m->id,
                    'miembro_nombre'   => trim($m->nombres . ' ' . $m->apellidos),
                    'direccion'        => $m->direccion,
                    'fecha_real'       => $ultimaVisita ? $ultimaVisita->fecha_visita : null,
                    'ultima_visita_id' => $ultimaVisita ? $ultimaVisita->id : null,
                    'ultimo_motivo'    => $ultimaVisita ? $ultimaVisita->motivo : null,
                ];
            }

            usort($resultados, function($a, $b) {
                return strtotime($b['fecha_real'] ?? 0) <=> strtotime($a['fecha_real'] ?? 0);
            });
        }

        return $this->aplicarEstadosDinamicos($resultados, 'fecha_real');
    }

    /**
     * Retorna datos para el mapa en formato JSON
     */
    public function obtenerDatosMapaJSON() {
        $miembros = Miembro::where('estado', 1)
            ->whereNotNull('latitud')->where('latitud', '!=', '')
            ->whereNotNull('longitud')->where('longitud', '!=', '')
            ->with(['visitas' => function($q) {
                $q->where('estado', 1)->orderBy('fecha_visita', 'desc');
            }])
            ->get();

        $resultados = [];
        foreach ($miembros as $m) {
            $ultima = $m->visitas->first();
            $resultados[] = [
                'miembro_id'   => $m->id,
                'nombre'       => $m->nombres,
                'apellido'     => $m->apellidos,
                'latitud'      => $m->latitud,
                'longitud'     => $m->longitud,
                'direccion'    => $m->direccion,
                'motivo'       => $ultima ? $ultima->motivo : 'Sin visitas programadas',
                'fecha_visita' => $ultima ? $ultima->fecha_visita : ''
            ];
        }

        $datosFormateados = $this->aplicarEstadosDinamicos($resultados, 'fecha_visita');

        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($datosFormateados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Guarda o Actualiza una visita (Punto clave para el AJAX automático)
     */
    public function guardarVisita() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        // Limpiamos cualquier echo o warning previo para no corromper el JSON
        if (ob_get_length()) ob_clean(); 

        $exito = false;
        $mensajeError = "";

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception("Método de envío no válido.");
            }

            $visita_id = intval($_POST['visita_id'] ?? 0); 
            $miembro_id = intval($_POST['miembro_id'] ?? 0);
            $fecha_visita = $_POST['fecha_visita'] ?? date('Y-m-d');
            $motivoPredefinido = $_POST['motivo_predefinido'] ?? 'Visita Regular';
            $motivoLibre = trim($_POST['motivo_libre'] ?? '');
$hoy = date('Y-m-d');
            $motivoFinal = ($motivoPredefinido === 'Otros') ? $motivoLibre : $motivoPredefinido;
            
if ($fecha_visita > $hoy) {
    throw new \Exception("No puedes registrar una visita con fecha futura.");
}
            if (empty($motivoFinal)) {
                throw new \Exception("El motivo de la visita es obligatorio.");
            }

            if ($visita_id > 0) {
                // MODO EDICIÓN
                $visita = VisitaModelo::find($visita_id);
                if ($visita) {
                    $visita->fecha_visita = $fecha_visita;
                    $visita->motivo = $motivoFinal;
                    $exito = $visita->save();
                } else {
                    throw new \Exception("No se encontró el registro para editar.");
                }
            } else {
                // MODO NUEVO
                $nuevaVisita = VisitaModelo::create([
                    'miembro_id'     => $miembro_id,
                    'fecha_visita'   => $fecha_visita,
                    'motivo'         => $motivoFinal,
                    'registrado_por' => $_SESSION['usuario_id'] ?? 1,
                    'estado_id'      => 1,
                    'estado'         => 1
                ]);
                $exito = (bool)$nuevaVisita;
            }
        } catch (\Exception $e) {
            $exito = false;
            $mensajeError = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode([
            'ok' => $exito,
            'error' => $exito ? null : $mensajeError
        ]);
        exit; 
    }

    /**
     * Guarda ajustes de configuración de meses
     */
    public function guardarAjustesVisita() {
        $exito = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meses_limite'])) {
            $meses = intval($_POST['meses_limite']);
            if ($meses >= 1 && $meses <= 24) {
                $exito = DB::table('configuracion_sistema')
                    ->where('clave', 'meses_limite_visita')
                    ->update(['valor' => $meses]);
                
                $exito = $exito !== false; 
            }
        }
        
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['ok' => $exito]);
        exit;
    }

    /**
     * Eliminación lógica de una visita
     */
    public function eliminarVisita() {
        $exito = false;
        $visitaId = $_POST['visita_id'] ?? null;
        
        if ($visitaId) {
            $exito = VisitaModelo::where('id', $visitaId)->update(['estado' => 0]);
        }
        
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['ok' => (bool)$exito]);
        exit;
    }

    /**
     * HELPER: Calcula colores y estados según la fecha de visita
     */
    private function aplicarEstadosDinamicos($resultados, $campoFecha) {
        $mesesLimite = $this->obtenerMesesLimite();
        $diasLimiteTotal = ($mesesLimite > 0 ? $mesesLimite : 1) * 30; 
        $hoy = new DateTime();

        foreach ($resultados as &$r) {
            if (!empty($r[$campoFecha])) {
                $r['ultima_fecha_formateada'] = date('d/m/Y', strtotime($r[$campoFecha]));
                
                $fechaVisita = new DateTime($r[$campoFecha]);
                $diferencia = $hoy->diff($fechaVisita);
                $diasTranscurridos = $diferencia->days;
                
                // Si la fecha es futura, lo tratamos como reciente
                if ($fechaVisita > $hoy) $diasTranscurridos = 0;

                $porcentaje = ($diasTranscurridos / $diasLimiteTotal) * 100;

                if ($porcentaje <= 25) {
                    $r['clase_css'] = 'estado-verde-reciente';
                    $r['icono'] = 'fa-circle-check';
                    $r['estado_texto'] = 'Visitado reciente';
                } elseif ($porcentaje <= 70) {
                    $r['clase_css'] = 'estado-azul-intermedio';
                    $r['icono'] = 'fa-user-check';
                    $r['estado_texto'] = 'Visitado intermedio';
                } elseif ($porcentaje < 100) {
                    $r['clase_css'] = 'estado-amarillo-proximo';
                    $r['icono'] = 'fa-clock';
                    $r['estado_texto'] = 'Pendiente próximo';
                } else {
                    $r['clase_css'] = 'estado-rojo-critico';
                    $r['icono'] = 'fa-triangle-exclamation';
                    $r['estado_texto'] = 'Pendiente crítico';
                }
            } else {
                $r['ultima_fecha_formateada'] = 'Sin visitas';
                $r['clase_css'] = 'estado-rojo-critico'; 
                $r['icono'] = 'fa-triangle-exclamation';
                $r['estado_texto'] = 'Pendiente crítico'; 
            }
        }
        return $resultados;
    }
}
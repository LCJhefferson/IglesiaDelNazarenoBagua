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
     * Define en la conexión de MySQL quién está haciendo la acción en las visitas
     */
    private function configurarUsuarioAuditoria() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $usuarioLogueadoId = 0;
        
        // 1. Intentamos leer desde el objeto estándar de tu sistema
        if (isset($_SESSION['usuario']) && is_object($_SESSION['usuario'])) {
            $usuarioLogueadoId = $_SESSION['usuario']->id ?? 0;
        } elseif (isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
            $usuarioLogueadoId = $_SESSION['usuario']['id'] ?? 0;
        }
        
        // 2. Si no se encuentra, intentamos leer desde el campo plano por si acaso
        if ($usuarioLogueadoId === 0) {
            $usuarioLogueadoId = $_SESSION['usuario_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
        }

        // 3. FALLBACK DE SEGURIDAD: Si sigue en 0 (petición AJAX sin sesión activa), 
        // le asignamos temporalmente el ID 1 (Administrador) para evitar el NULL y verificar que pinte en el ojo
        if ($usuarioLogueadoId === 0) {
            $usuarioLogueadoId = 1; 
        }
        
        // Ejecutamos la sentencia en la base de datos
        DB::statement("SET @usuario_actual_id = ?", [intval($usuarioLogueadoId)]);
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
                    'ultimo_motivo'    => $v->motivo,
                    'registrado_por'   => $v->registrado_por
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
                    'registrado_por'   => $ultimaVisita ? $ultimaVisita->registrado_por : null,
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
                // --- Llama a la auditoría AQUÍ justo antes de buscar y guardar ---
                $this->configurarUsuarioAuditoria();
                
                $visita = VisitaModelo::find($visita_id);
                if ($visita) {
                    // RBAC para Grupo de Visitas
                    $rolIdActual = (int)($_SESSION['rol_id'] ?? 0);
                    $usuarioIdActual = (int)($_SESSION['usuario']->id ?? $_SESSION['usuario_id'] ?? 1);
                    if (!in_array($rolIdActual, [1, 2]) && (int)$visita->registrado_por !== $usuarioIdActual) {
                        throw new \Exception("No tienes permiso para editar visitas registradas por otros usuarios.");
                    }

                    $visita->fecha_visita = $fecha_visita;
                    $visita->motivo = $motivoFinal;
                    $exito = $visita->save();
                } else {
                    throw new \Exception("No se encontró el registro para editar.");
                }
            } else {
                // MODO NUEVO
                // --- Llama a la auditoría AQUÍ justo antes de crear ---
                $this->configurarUsuarioAuditoria();
                
                $nuevaVisita = VisitaModelo::create([
                    'miembro_id'     => $miembro_id,
                    'fecha_visita'   => $fecha_visita,
                    'motivo'         => $motivoFinal,
                    'registrado_por' => $_SESSION['usuario']->id ?? $_SESSION['usuario_id'] ?? 1,
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
            // SE CONFIGURA LA AUDITORÍA DE CONEXIÓN JUSTO ANTES DE GUARDAR EL UPDATE
            $this->configurarUsuarioAuditoria();

            $visita = VisitaModelo::find($visitaId);
            if ($visita) {
                // RBAC para Grupo de Visitas
                $rolIdActual = (int)($_SESSION['rol_id'] ?? 0);
                $usuarioIdActual = (int)($_SESSION['usuario']->id ?? $_SESSION['usuario_id'] ?? 1);
                if (!in_array($rolIdActual, [1, 2]) && (int)$visita->registrado_por !== $usuarioIdActual) {
                    if (ob_get_length()) ob_clean();
                    header('Content-Type: application/json');
                    echo json_encode(['ok' => false, 'error' => "No tienes permiso para eliminar visitas registradas por otros usuarios."]);
                    exit;
                }
                $exito = VisitaModelo::where('id', $visitaId)->update(['estado' => 0]);
            }
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
<?php
namespace aplicacion\controladores;

use aplicacion\modelos\TransmisionModelo;
use Pusher\Pusher;

class TransmisionController {
    private $pusher;

    public function __construct() {
        // ... (configuración de Pusher igual que antes)
    }

public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_transmision'] ?? null;
            
            // 1. Si existe un ID, buscamos primero el registro actual en la Base de Datos
            $transmisionExistente = null;
            if (!empty($id)) {
                $transmisionExistente = TransmisionModelo::find($id);
            }

            // 2. Mapeamos los datos con validación 'Fallback' (si no vienen en el POST, conservamos lo que ya existía)
            $datos = [
                'titulo'      => $_POST['titulo'] ?? ($transmisionExistente ? $transmisionExistente->titulo : ''),
                'descripcion' => $_POST['descripcion'] ?? ($transmisionExistente ? $transmisionExistente->descripcion : ''),
                'estado_id'   => $_POST['estado_id'] ?? ($transmisionExistente ? $transmisionExistente->estado_id : 2),
                'creado_por'  => $_SESSION['usuario_id'] ?? 1,
                'fecha'       => date('Y-m-d H:i:s')
            ];

            // Tratamiento seguro de la URL de video
            $urlPost = $_POST['link_video'] ?? null;
            if ($urlPost !== null) {
                $datos['link_video'] = $this->formatearUrlYoutube($urlPost);
            } else {
                $datos['link_video'] = $transmisionExistente ? $transmisionExistente->link_video : '';
            }

            // 3. Procesamos las inserciones o actualizaciones en la base de datos
            if (empty($id)) {
                // Crear nueva transmisión
                if ($datos['estado_id'] == 1) {
                    TransmisionModelo::where('estado_id', 1)->update(['estado_id' => 2]);
                    $this->notificarCambio('live_started', $datos);
                }
                TransmisionModelo::create($datos);
            } else {
                // Actualizar o finalizar transmisión existente
                if ($transmisionExistente) {
                    // Si se pasa a estado 2 (Finalizado), podemos cerrar otros vivos preventivamente
                    if ($datos['estado_id'] == 2) {
                        TransmisionModelo::where('estado_id', 1)->update(['estado_id' => 2]);
                    }
                    
                    $transmisionExistente->update($datos);
                    $this->notificarCambio($datos['estado_id'] == 2 ? 'live_finished' : 'live_updated', $datos);
                }
            }

            // 4. Redirección limpia (ya no fallará porque no hay warnings previos)
            header("Location: index.php?vista=dashboard&seccion=transmision&msj=exito");
            exit;
        }
    }

    public function listarTransmisiones() {
        // --- EQUIVALENTE A: listarTodo() del DAO ---
        // Usamos Eloquent para traer los datos y ordenarlos
        return TransmisionModelo::orderBy('id', 'desc')->get();
    }


    private function notificarCambio($tipo, $datos) {
        if ($this->pusher) {
            try {
                $payload = [
                    'message'    => $tipo,
                    'titulo'     => $datos['titulo'] ?? '',
                    'link_video' => $datos['link_video'] ?? '',
                    'estado_id'  => $datos['estado_id'] ?? '',
                    'texto_publico' => $datos['mensaje'] ?? ''
                ];
                $this->pusher->trigger('iglesia-canal', 'evento-vivo', $payload);
            } catch (\Exception $e) {
                error_log("Error de Pusher: " . $e->getMessage());
            }
        }
    }

    private function formatearUrlYoutube($url) {
        if (empty($url)) return '';
        if (strpos($url, 'youtube.com/embed/') !== false) return $url;
        
        $videoId = '';
        if (preg_match('/(?:youtube\.com\/watch\?v=|v=)([\w-]+)/', $url, $matches)) $videoId = $matches[1];
        elseif (preg_match('/youtu\.be\/([\w-]+)/', $url, $matches)) $videoId = $matches[1];
        elseif (preg_match('/youtube\.com\/live\/([\w-]+)/', $url, $matches)) $videoId = $matches[1];
        elseif (preg_match('/youtube\.com\/shorts\/([\w-]+)/', $url, $matches)) $videoId = $matches[1];

        return !empty($videoId) ? "https://www.youtube.com/embed/" . $videoId : $url;
    }

    
}
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
            
            // Datos base del formulario
            $datos = [
                'titulo'      => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'],
                'link_video'  => $this->formatearUrlYoutube($_POST['link_video']),
                'estado_id'   => $_POST['estado_id'],
                'creado_por'  => $_SESSION['usuario_id'] ?? 1,
                'fecha'       => date('Y-m-d H:i:s')
            ];

            if (empty($id)) {
                // --- EQUIVALENTE A: guardar() del DAO ---
                if ($datos['estado_id'] == 1) {
                    // --- EQUIVALENTE A: finalizarVivosAnteriores() ---
                    TransmisionModelo::where('estado_id', 1)->update(['estado_id' => 2]);
                    $this->notificarCambio('live_started', $datos);
                }
                TransmisionModelo::create($datos);
            } else {
                // --- EQUIVALENTE A: actualizar() del DAO ---
                $transmision = TransmisionModelo::find($id);
                if ($transmision) {
                    $transmision->update($datos);
                    $this->notificarCambio($datos['estado_id'] == 2 ? 'live_finished' : 'live_updated', $datos);
                }
            }

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
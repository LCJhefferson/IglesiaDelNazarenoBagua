<?php
namespace aplicacion\controladores;

use aplicacion\dao\TransmisionDAO;
use Pusher\Pusher;

class TransmisionController {
    private $dao;
    private $pusher;

    public function __construct() {
        $this->dao = new TransmisionDAO();
        $options = ['cluster' => 'tu_cluster_aqui', 'useTLS' => true];
        
        try {
            $this->pusher = new Pusher(
                'tu_key_aqui',
                'tu_secret_aqui',
                'tu_app_id_aqui',
                $options
            );
        } catch (\Exception $e) {
            $this->pusher = null;
        }
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_actual = $_POST['id_transmision'] ?? '';
            
            // CORRECCIÓN PARA EL BOTÓN DE FINALIZAR DESDE LA TABLA / CANCELACIÓN RÁPIDA
            if (!empty($id_actual) && isset($_POST['mensaje_pusher'])) {
                $datos = [
                    'id' => $id_actual,
                    'titulo' => $_POST['titulo'] ?? 'Transmisión',
                    'descripcion' => $_POST['descripcion'] ?? '',
                    'link_video' => $_POST['link_video'] ?? '',
                    'estado_id' => 2 // 2 = Finalizado
                ];
                
                $this->dao->actualizar($datos);
                
                $this->notificarCambio('live_finished', [
                    'titulo' => $datos['titulo'],
                    'link_video' => $datos['link_video'],
                    'mensaje' => 'Fin de la transmisión'
                ]);

                // Modificado para mantener el apartado de transmisión
                header("Location: dashboard?seccion=transmision&msj=finalizado");
                exit;
            }

            // Flujo normal del formulario (Iniciar o Guardar cambios)
            if (isset($_POST['titulo'])) {
                $nuevo_estado = $_POST['estado_id'];

                $datos = [
                    'titulo'      => $_POST['titulo'],
                    'descripcion' => $_POST['descripcion'],
                    'link_video'  => $this->formatearUrlYoutube($_POST['link_video']),
                    'estado_id'   => $nuevo_estado,
                    'creado_por'  => $_SESSION['usuario_id'] ?? 1 
                ];

                // 1. LÓGICA DE NUEVA TRANSMISIÓN
                if (empty($id_actual)) {
                    if ($nuevo_estado == 1) {
                        $this->dao->finalizarVivosAnteriores(); 
                        $this->notificarCambio('live_started', $datos);
                    }
                    $this->dao->guardar($datos);
                } 
                // 2. LÓGICA DE EDICIÓN
                else {
                    $datos['id'] = $id_actual;
                    
                    if ($nuevo_estado == 2) {
                        $this->notificarCambio('live_finished', [
                            'titulo' => $datos['titulo'],
                            'link_video' => $datos['link_video'],
                            'mensaje' => 'Fin de la transmisión'
                        ]);
                    } else {
                        $this->notificarCambio('live_updated', $datos);
                    }
                    
                    $this->dao->actualizar($datos);
                }

                // Modificado para mantener el apartado de transmisión
                header("Location: dashboard?seccion=transmision&msj=exito");
                exit;
            }
        }
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

    public function listarTransmisiones() {
        return $this->dao->listarTodo();
    }
}
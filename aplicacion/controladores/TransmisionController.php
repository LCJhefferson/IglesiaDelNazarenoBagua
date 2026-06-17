<?php
namespace aplicacion\controladores;

use aplicacion\modelos\TransmisionModelo;
use Pusher\Pusher;

class TransmisionController {
    private $pusher;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // ... (Tu configuración de Pusher se mantiene igual aquí debajo)
    }

    /**
     * Solución al Warning: Redirecciona de forma limpia por HTTP o por JavaScript
     */
    /**
 * Redirección híbrida y ULTRA-SEGURA
 * Protegida contra XSS (Inyección de código) y Redirecciones Abiertas (Phishing)
 */
private function redireccionar(string $url): void {
    // 1. Mitigación contra Redirecciones Abiertas (Open Redirection)
    // Si la URL contiene un protocolo HTTP externo, validamos que pertenezca a nuestro servidor local
    if (preg_match('/^https?:\/\//i', $url)) {
        $hostPermitido = $_SERVER['HTTP_HOST']; // Captura 'localhost' o el dominio real de la iglesia en producción
        $hostDestino = parse_url($url, PHP_URL_HOST);
        
        if ($hostDestino !== $hostPermitido) {
            // Si intentan redirigir a un sitio externo no autorizado, los mandamos a la raíz segura
            $url = "/IglesiaDelNazarenoBagua/public/index.php?vista=dashboard";
        }
    }

    // 2. Ejecutar redirección nativa si las cabeceras están limpias
    if (!headers_sent()) {
        header("Location: " . $url);
        exit;
    } else {
        // 3. Mitigación ABSOLUTA contra XSS usando json_encode()
        // json_encode sanitiza el string impidiendo que rompan las comillas del JS
        $urlSeguraJs = json_encode($url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        
        echo "<script>window.location.href = " . $urlSeguraJs . ";</script>";
        exit;
    }
}

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_transmision'] ?? null;
            
            // 1. Si existe un ID, buscamos primero el registro actual en la Base de Datos
            $transmisionExistente = null;
            if (!empty($id)) {
                $transmisionExistente = TransmisionModelo::find($id);
            }

            // Detectamos el ID de usuario de forma segura
            $usuarioId = $_SESSION['usuario']->id ?? $_SESSION['usuario_id'] ?? 1;

            // 2. Mapeamos los datos con validación 'Fallback'
            $datos = [
                'titulo'      => $_POST['titulo'] ?? ($transmisionExistente ? $transmisionExistente->titulo : ''),
                'descripcion' => $_POST['descripcion'] ?? ($transmisionExistente ? $transmisionExistente->descripcion : ''),
                'estado_id'   => $_POST['estado_id'] ?? ($transmisionExistente ? $transmisionExistente->estado_id : 2),
                'creado_por'  => $usuarioId,
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
                    if ($datos['estado_id'] == 2) {
                        TransmisionModelo::where('estado_id', 1)->update(['estado_id' => 2]);
                    }
                    
                    $transmisionExistente->update($datos);
                    $this->notificarCambio($datos['estado_id'] == 2 ? 'live_finished' : 'live_updated', $datos);
                }
            }

            // 4. Redirección blindada usando el método híbrido
            $this->redireccionar("index.php?vista=dashboard&seccion=transmision&msj=exito");
        }
    }

    public function listarTransmisiones() {
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
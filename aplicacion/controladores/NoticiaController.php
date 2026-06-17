<?php
namespace aplicacion\controladores;

use aplicacion\modelos\NoticiaModelo;
use aplicacion\modelos\NoticiaImagenModelo;

class NoticiaController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

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

    public function mostrarNoticias() {
        return NoticiaModelo::with('imagenesAdjuntas')
                            ->whereIn('estado', [1, 2])
                            ->orderBy('fecha_creacion', 'desc')
                            ->get();
    }

    public function listarPublicas() {
        return NoticiaModelo::with('imagenesAdjuntas')
                            ->where('estado', 1)
                            ->orderBy('fecha_creacion', 'desc')
                            ->get();
    }

    public function eliminarNoticia($id) {
        $noticia = NoticiaModelo::find($id);
        if ($noticia) {
            $noticia->update(['estado' => 0]); // Eliminado lógico
            $this->redireccionar("/IglesiaDelNazarenoBagua/public/index.php?vista=dashboard&seccion=noticias&eliminado=1");
        }
    }

    public function cambiarVisibilidad($id, $estado) {
        $noticia = NoticiaModelo::find($id);
        if ($noticia) {
            $noticia->update(['estado' => $estado]);
            $this->redireccionar("/IglesiaDelNazarenoBagua/public/index.php?vista=dashboard&seccion=noticias");
        }
    }

    public function guardarNoticia() {
        $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;

        $carpetaDestino = $_SERVER['DOCUMENT_ROOT'] . "/IglesiaDelNazarenoBagua/public/admin/imagenes/noticias/";
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        if (isset($_POST['imagenes_a_eliminar']) && is_array($_POST['imagenes_a_eliminar'])) {
            foreach ($_POST['imagenes_a_eliminar'] as $idFotoBorrar) {
                $this->eliminarFotoGaleria($idFotoBorrar);
            }
        }

        $rutaPortada = $_POST['imagen_actual'] ?? "";
        
        if (!empty($_FILES['imagen']['name'])) {
            $extension  = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($extension, $permitidos)) {
                $nombreArchivo = time() . "_portada_" . basename($_FILES['imagen']['name']);
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaDestino . $nombreArchivo)) {
                    $rutaPortada = "public/admin/imagenes/noticias/" . $nombreArchivo;
                }
            }
        }

        $datos = [
            'titulo'         => $_POST['titulo']    ?? '',
            'resumen'        => $_POST['resumen']   ?? '',
            'contenido'      => $_POST['contenido'] ?? '',
            'imagen_portada' => $rutaPortada,
            'video_link'     => $_POST['video']     ?? '',
            'fecha_creacion' => $_POST['fecha']     ?? date("Y-m-d H:i:s")
        ];

        if ($id) {
            $noticia = NoticiaModelo::find($id);
            $noticia->update($datos);
            $noticiaId = $id;
            $param     = "actualizado=1";
        } else {
            $noticia = NoticiaModelo::create($datos);
            $noticiaId = $noticia->id;
            $param     = "guardado=1";
        }

        if ($noticiaId && !empty($_FILES['imagenes']['name'][0])) {
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            foreach ($_FILES['imagenes']['name'] as $i => $nombre) {
                if ($_FILES['imagenes']['error'][$i] == 0) {
                    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $permitidos)) {
                        $nombreGaleria = time() . "_" . uniqid() . "_galeria_" . basename($nombre);
                        
                        if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$i], $carpetaDestino . $nombreGaleria)) {
                            $rutaArchivo = "public/admin/imagenes/noticias/" . $nombreGaleria;
                            
                            NoticiaImagenModelo::create([
                                'noticia_id' => $noticiaId,
                                'imagen'     => $rutaArchivo 
                            ]);
                        }
                    }
                }
            }
        }

        $this->redireccionar("/IglesiaDelNazarenoBagua/public/index.php?vista=dashboard&seccion=noticias&{$param}");
    }

    public function eliminarFotoGaleria($idFoto) {
        $imagen = NoticiaImagenModelo::find($idFoto);

        if ($imagen) {
            $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . "/IglesiaDelNazarenoBagua/" . $imagen->imagen;
            
            if(file_exists($rutaFisica) && !empty($imagen->imagen)) {
                unlink($rutaFisica);
            }
            
            return $imagen->delete();
        }
        return false;
    }
}
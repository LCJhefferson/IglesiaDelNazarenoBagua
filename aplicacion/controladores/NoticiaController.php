<?php
namespace aplicacion\controladores;

use aplicacion\modelos\NoticiaModelo;
use aplicacion\modelos\NoticiaImagenModelo;

class NoticiaController {

    public function __construct() {
        // Ya no instanciamos el DAO, usamos los modelos estáticamente
    }

    public function mostrarNoticias() {
        // Eloquent trae las noticias con estado 1 o 2, ordenadas, y adjunta su galería
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
            header("Location: /IglesiaDelNazarenoBagua/public/index.php?vista=dashboard&seccion=noticias&eliminado=1");
            exit();
        }
    }

    public function cambiarVisibilidad($id, $estado) {
        $noticia = NoticiaModelo::find($id);
        if ($noticia) {
            $noticia->update(['estado' => $estado]);
            header("Location: /IglesiaDelNazarenoBagua/public/index.php?vista=dashboard&seccion=noticias");
            exit();
        }
    }
 public function guardarNoticia() {
    $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;

    $carpetaDestino = $_SERVER['DOCUMENT_ROOT'] . "/IglesiaDelNazarenoBagua/public/admin/imagenes/noticias/";
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0755, true);
    }

    // --- NUEVO: PROCESAR IMÁGENES MARCADAS PARA ELIMINAR ---
    // Esto lee el array que creamos con JavaScript (imagenes_a_eliminar[])
    if (isset($_POST['imagenes_a_eliminar']) && is_array($_POST['imagenes_a_eliminar'])) {
        foreach ($_POST['imagenes_a_eliminar'] as $idFotoBorrar) {
            // Reutilizamos tu método existente para borrar físico y DB
            $this->eliminarFotoGaleria($idFotoBorrar);
        }
    }

    $rutaPortada = $_POST['imagen_actual'] ?? "";
    
    // Procesar imagen de portada
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

    // --- PROCESAR GALERÍA (SUBIR NUEVAS IMÁGENES) ---
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

    header("Location: /IglesiaDelNazarenoBagua/public/index.php?vista=dashboard&seccion=noticias&{$param}");
    exit();
}

    public function eliminarFotoGaleria($idFoto) {
        $imagen = NoticiaImagenModelo::find($idFoto);

        if ($imagen) {
            // USAMOS $imagen->imagen
            $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . "/IglesiaDelNazarenoBagua/" . $imagen->imagen;
            
            if(file_exists($rutaFisica) && !empty($imagen->imagen)) {
                unlink($rutaFisica);
            }
            
            return $imagen->delete();
        }
        return false;
    }
}
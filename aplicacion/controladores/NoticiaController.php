<?php
namespace controladores;
use dao\NoticiaDAO;

class NoticiaController {
    private $dao;

    public function __construct() {
        $this->dao = new NoticiaDAO();
    }

    public function mostrarNoticias() {
    $noticias = $this->dao->listarTodas();
    foreach ($noticias as &$n) {
        $n['imagenes_adjuntas'] = $this->dao->obtenerImagenesAdjuntas($n['id']);
    }
    return $noticias;
}

    
public function eliminarNoticia($id) {
    if($this->dao->eliminar($id)) {
        // RUTA REAL según tu imagen: aplicacion/vistas/admin/
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=noticias");
        exit();
    }
}

    public function guardarNoticia() {
    $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;

    $carpetaDestino = $_SERVER['DOCUMENT_ROOT'] . "/IglesiaDelNazarenoBagua/admin/imagenes/noticias/";
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0777, true);
    }

    // Procesar Imagen de Portada
    $rutaPortada = "";
    if (!empty($_FILES['imagen']['name'])) {
        $nombreArchivo = time() . "_portada_" . $_FILES['imagen']['name'];
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaDestino . $nombreArchivo)) {
            $rutaPortada = "admin/imagenes/noticias/" . $nombreArchivo;
        }
    } else {
        $rutaPortada = $_POST['imagen_actual'] ?? "";
    }

    // PREPARAR DATOS (Una sola vez para ambos casos)
    $datos = [
        'titulo'         => $_POST['titulo'],
        'resumen'        => $_POST['resumen'],
        'contenido'      => $_POST['contenido'],
        'imagen_portada' => $rutaPortada, // Corregido: antes decía $imagen_portada
        'video_link'     => $_POST['video'] ?? '',
        'fecha'          => $_POST['fecha'] ?? date("Y-m-d H:i:s")
    ];

    // DECIDIR ACCIÓN
    if ($id) {
        $datos['id'] = $id; // Agregamos el ID solo para el UPDATE
        $this->dao->actualizar($datos);
        $noticiaId = $id;
    } else {
        $noticiaId = $this->dao->insertar($datos);
    }

    // Procesar Galería
    if ($noticiaId && !empty($_FILES['imagenes']['name'][0])) {
        foreach ($_FILES['imagenes']['name'] as $i => $nombre) {
            if ($_FILES['imagenes']['error'][$i] == 0) {
                $nombreGaleria = time() . "_galeria_" . $nombre;
                if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$i], $carpetaDestino . $nombreGaleria)) {
                    $rutaGaleria = "admin/imagenes/noticias/" . $nombreGaleria;
                    $this->dao->insertarImagenGaleria($noticiaId, $rutaGaleria);
                }
            }
        }
    }
    
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=noticias");
    exit();
}

public function eliminarFotoGaleria($idFoto) {
   
    if($this->dao->eliminarImagenGaleria($idFoto)) {
        //Podrías borrar el archivo físico aquí 
        return true;
    }
    return false;
}












}
<?php
namespace aplicacion\controladores;
use aplicacion\dao\NoticiaDAO;

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
            header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=noticias&eliminado=1");
            exit();
        }
    }

    public function guardarNoticia() {
        $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;

        $carpetaDestino = $_SERVER['DOCUMENT_ROOT'] . "/IglesiaDelNazarenoBagua/public/admin/imagenes/noticias/";
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        $rutaPortada = "";
        if (!empty($_FILES['imagen']['name'])) {
            $extension  = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($extension, $permitidos)) {
                $nombreArchivo = time() . "_portada_" . basename($_FILES['imagen']['name']);
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaDestino . $nombreArchivo)) {
                    $rutaPortada = "public/admin/imagenes/noticias/" . $nombreArchivo;
                }
            }
        } else {
            $rutaPortada = $_POST['imagen_actual'] ?? "";
        }

        $datos = [
            'titulo'         => $_POST['titulo']    ?? '',
            'resumen'        => $_POST['resumen']   ?? '',
            'contenido'      => $_POST['contenido'] ?? '',
            'imagen_portada' => $rutaPortada,
            'video_link'     => $_POST['video']     ?? '',
            'fecha'          => $_POST['fecha']     ?? date("Y-m-d H:i:s")
        ];

        if ($id) {
            $datos['id'] = $id;
            $this->dao->actualizar($datos);
            $noticiaId = $id;
            $param     = "actualizado=1";
        } else {
            $noticiaId = $this->dao->insertar($datos);
            $param     = "guardado=1";
        }

        if ($noticiaId && !empty($_FILES['imagenes']['name'][0])) {
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            foreach ($_FILES['imagenes']['name'] as $i => $nombre) {
                if ($_FILES['imagenes']['error'][$i] == 0) {
                    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                    if (in_array($ext, $permitidos)) {
                        $nombreGaleria = time() . "_galeria_" . basename($nombre);
                        if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$i], $carpetaDestino . $nombreGaleria)) {
                            $rutaGaleria = "public/admin/imagenes/noticias/" . $nombreGaleria;
                            $this->dao->insertarImagenGaleria($noticiaId, $rutaGaleria);
                        }
                    }
                }
            }
        }

        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=noticias&{$param}");
        exit();
    }

    public function eliminarFotoGaleria($idFoto) {
        $imagen = $this->dao->obtenerImagenPorId($idFoto);

        if($this->dao->eliminarImagenGaleria($idFoto)) {
            if($imagen && !empty($imagen['ruta'])) {
                $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . "/IglesiaDelNazarenoBagua/" . $imagen['ruta'];
                if(file_exists($rutaFisica)) {
                    unlink($rutaFisica);
                }
            }
            return true;
        }
        return false;
    }
}
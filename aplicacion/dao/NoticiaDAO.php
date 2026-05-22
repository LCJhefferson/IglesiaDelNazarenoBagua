<?php
namespace aplicacion\dao;
use aplicacion\config\Conexion;
use PDO;

class NoticiaDAO {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function insertar($datos) {
        $sql = "INSERT INTO noticias (titulo, resumen, contenido, imagen_portada, video_link, fecha_creacion) 
                VALUES (:titulo, :resumen, :contenido, :imagen_portada, :video_link, :fecha)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':titulo'         => $datos['titulo'],
            ':resumen'        => $datos['resumen'],
            ':contenido'      => $datos['contenido'],
            ':imagen_portada' => $datos['imagen_portada'],
            ':video_link'     => $datos['video_link'],
            ':fecha'          => $datos['fecha']
        ]);
        return $this->db->lastInsertId();
    }

    public function insertarImagenGaleria($noticiaId, $rutaImagen) {
        $sql  = "INSERT INTO noticia_imagenes (noticia_id, imagen) VALUES (:noticia_id, :imagen)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':noticia_id' => $noticiaId,
            ':imagen'     => $rutaImagen
        ]);
    }

    public function eliminar($id) {
        $sql  = "UPDATE noticias SET estado = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function listarTodas() {
        $sql = "SELECT * FROM noticias WHERE estado = 1 ORDER BY fecha_creacion DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function actualizar($datos) {
        $sql = "UPDATE noticias SET 
                    titulo          = :titulo, 
                    resumen         = :resumen, 
                    contenido       = :contenido, 
                    imagen_portada  = :imagen_portada, 
                    video_link      = :video_link,
                    fecha_creacion  = :fecha
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        // Aquí es donde ocurría el error si los nombres no coincidían
        return $stmt->execute($datos);
    }

    public function obtenerImagenesAdjuntas($id_noticia) {
        $sql  = "SELECT id, imagen as ruta FROM noticia_imagenes WHERE noticia_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id_noticia]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarImagenGaleria($idFoto) {
        $sql  = "DELETE FROM noticia_imagenes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $idFoto]);
    }

    public function obtenerImagenPorId($id) {
        $sql  = "SELECT id, imagen as ruta FROM noticia_imagenes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
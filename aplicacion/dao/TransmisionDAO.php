<?php
namespace aplicacion\dao;

use aplicacion\config\Conexion;
use PDO;

class TransmisionDAO {
    
    public function finalizarVivosAnteriores() {
        $db = Conexion::conectar();
        // Pasamos todo lo que esté en 'En Vivo' (1) a 'Finalizado' (2)
        $sql = "UPDATE transmisiones SET estado_id = 2 WHERE estado_id = 1";
        return $db->query($sql);
    }

    public function guardar($datos) {
        $db = Conexion::conectar();
        $sql = "INSERT INTO transmisiones (titulo, descripcion, link_video, estado_id, creado_por, fecha) 
                VALUES (:titulo, :descripcion, :link_video, :estado_id, :creado_por, NOW())";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':titulo' => $datos['titulo'],
            ':descripcion' => $datos['descripcion'],
            ':link_video' => $datos['link_video'],
            ':estado_id' => $datos['estado_id'],
            ':creado_por' => $datos['creado_por']
        ]);
    }

    public function actualizar($datos) {
        $db = Conexion::conectar();
        $sql = "UPDATE transmisiones SET 
                titulo = :titulo, 
                descripcion = :descripcion, 
                link_video = :link_video, 
                estado_id = :estado_id 
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':titulo' => $datos['titulo'],
            ':descripcion' => $datos['descripcion'],
            ':link_video' => $datos['link_video'],
            ':estado_id' => $datos['estado_id'],
            ':id' => $datos['id']
        ]);
    }

    public function listarTodo() {
        $db = Conexion::conectar();
        return $db->query("SELECT t.*, e.nombre as estado_nombre 
                          FROM transmisiones t 
                          JOIN estados_transmision e ON t.estado_id = e.id 
                          ORDER BY t.id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
}
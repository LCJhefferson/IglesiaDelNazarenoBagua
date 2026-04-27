<?php
namespace aplicacion\dao;

// Referencia a la ubicación real de tu clase Conexion
use aplicacion\config\Conexion;
use \PDO;

class TransmisionDAO {
    
    public function listar() {
        $db = Conexion::conectar(); 
        $sql = "SELECT t.*, e.nombre as estado_nombre 
                FROM transmisiones t 
                INNER JOIN estados_transmision e ON t.estado_id = e.id 
                ORDER BY t.fecha DESC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar($datos) {
        $db = Conexion::conectar();
        $sql = "INSERT INTO transmisiones (titulo, descripcion, link_video, fecha, creado_por, estado_id) 
                VALUES (:titulo, :descripcion, :link_video, NOW(), :creado_por, :estado_id)";
        $stmt = $db->prepare($sql);
        return $stmt->execute($datos);
    }
}
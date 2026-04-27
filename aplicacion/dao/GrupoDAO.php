<?php
namespace aplicacion\dao;

use aplicacion\config\Conexion;
use PDO;

class GrupoDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    public function listarTodos() {
        // Ajusta los nombres de tablas según tu BD
        $sql = "SELECT g.*, m.nombre as discipulador_nombre 
                FROM grupos_discipulado g
                LEFT JOIN miembros m ON g.discipulador_id = m.id
                WHERE g.estado = 1"; 
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
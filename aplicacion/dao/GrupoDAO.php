<?php
namespace aplicacion\dao;

use aplicacion\config\Conexion;
use PDO;

class GrupoDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    /**
     * Lista todos los grupos con el nombre del discipulador y el nombre del estado actual.
     */
    public function listarTodos() {
        // Según tu imagen: 
        // Tabla: discipulado_grupos
        // Tabla estados: estados_discipulado
        // Campo unión: estado_id
        
        $sql = "SELECT g.*, 
                       CONCAT(m.nombres, ' ', m.apellidos) as discipulador_nombre, 
                       e.nombre as estado_texto
                FROM discipulado_grupos g
                LEFT JOIN miembros m ON g.discipulador_id = m.id
                LEFT JOIN estados_discipulado e ON g.estado_id = e.id
                ORDER BY g.id DESC"; 
                
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un nuevo grupo de discipulado.
     */
    public function registrar($datos) {
        $sql = "INSERT INTO discipulado_grupos (nombre, nivel, discipulador_id, fecha_creacion, estado_id) 
                VALUES (:nombre, :nivel, :discipulador_id, :fecha_creacion, :estado_id)";
        return $this->pdo->prepare($sql)->execute($datos);
    }

    /**
     * Actualiza un grupo existente.
     */
    public function actualizar($datos) {
        $sql = "UPDATE discipulado_grupos SET 
                    nombre = :nombre, 
                    nivel = :nivel, 
                    discipulador_id = :discipulador_id, 
                    estado_id = :estado_id 
                WHERE id = :id";
        return $this->pdo->prepare($sql)->execute($datos);
    }

    /**
     * Obtiene la lista de estados para llenar un <select> en la vista.
     */
    public function listarEstados() {
        $sql = "SELECT * FROM estados_discipulado ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los miembros que pueden ser discipuladores (opcional filtrar por cargo).
     */
    public function listarDiscipuladoresPosibles() {
        $sql = "SELECT id, nombres, apellidos FROM miembros WHERE estado = 1 ORDER BY nombres ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
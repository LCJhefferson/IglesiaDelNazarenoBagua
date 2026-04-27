<?php
namespace aplicacion\dao;

use aplicacion\config\Conexion;
use PDO;

class MiembroDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    public function listar() {
        $sql = "SELECT m.*, c.nombre as cargo_nombre, con.nombre as condicion_nombre 
                FROM miembros m
                LEFT JOIN cargos c ON m.cargo_id = c.id
                LEFT JOIN condiciones_miembro con ON m.condicion_id = con.id
                ORDER BY m.id DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrar($datos) {
        // CORRECCIÓN: Usamos :estado para que sea dinámico según el formulario
        $sql = "INSERT INTO miembros (nombres, apellidos, telefono, direccion, fecha_nacimiento, cargo_id, condicion_id, latitud, longitud, estado) 
                VALUES (:nombres, :apellidos, :telefono, :direccion, :fecha_nacimiento, :cargo_id, :condicion_id, :latitud, :longitud, :estado)";
        return $this->pdo->prepare($sql)->execute($datos);
    }

    public function actualizar($datos) {
        // CORRECCIÓN: Agregamos estado=:estado para que los cambios en el modal se guarden
        $sql = "UPDATE miembros SET nombres=:nombres, apellidos=:apellidos, telefono=:telefono, direccion=:direccion, 
                fecha_nacimiento=:fecha_nacimiento, cargo_id=:cargo_id, condicion_id=:condicion_id, latitud=:latitud, longitud=:longitud, estado=:estado 
                WHERE id=:id";
        return $this->pdo->prepare($sql)->execute($datos);
    }

    public function eliminar($id) {
        $sql = "UPDATE miembros SET estado = 0 WHERE id = :id";
        return $this->pdo->prepare($sql)->execute(['id' => $id]);
    }

    public function activar($id) {
        $sql = "UPDATE miembros SET estado = 1 WHERE id = :id";
        return $this->pdo->prepare($sql)->execute(['id' => $id]);
    }
    
    public function listarCargos() {
        $sql = "SELECT id, nombre FROM cargos ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCondiciones() {
        $sql = "SELECT id, nombre FROM condiciones_miembro ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
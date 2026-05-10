<?php
namespace aplicacion\dao;

use aplicacion\config\Conexion;
use PDO;
use Exception;

class MiembroDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    /**
     * Lista miembros con sus relaciones.
     * GROUP_CONCAT genera una cadena de IDs (1,2,3) que el JS convertirá en Array.
     */
    public function listar() {
        $sql = "SELECT m.*, 
                       GROUP_CONCAT(c.nombre SEPARATOR ', ') as cargo_nombre, 
                       GROUP_CONCAT(c.id) as cargos_ids,
                       con.nombre as condicion_nombre, 
                       t.nombre as tipo_nombre 
                FROM miembros m
                LEFT JOIN miembro_cargos mc ON m.id = mc.miembro_id
                LEFT JOIN cargos c ON mc.cargo_id = c.id
                LEFT JOIN condiciones_miembro con ON m.condicion_id = con.id
                LEFT JOIN tipos_miembro t ON m.tipo_miembro_id = t.id
                GROUP BY m.id
                ORDER BY m.id DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra miembro y sus múltiples cargos en una transacción.
     */
    public function registrar($datosMiembro, $cargosIds) {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO miembros (nombres, apellidos, telefono, direccion, fecha_nacimiento, 
                                        condicion_id, latitud, longitud, estado, tipo_miembro_id) 
                    VALUES (:nombres, :apellidos, :telefono, :direccion, :fecha_nacimiento, 
                            :condicion_id, :latitud, :longitud, :estado, :tipo_miembro_id)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($datosMiembro);
            $miembroId = $this->pdo->lastInsertId();

            if (!empty($cargosIds)) {
                $this->insertarCargos($miembroId, $cargosIds);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("Error en DAO Registrar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza datos y sincroniza cargos (Borra y Re-inserta).
     */
    public function actualizarConCargos($datosMiembro, $cargosIds) {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE miembros SET nombres = :nombres, apellidos = :apellidos, telefono = :telefono, 
                           direccion = :direccion, fecha_nacimiento = :fecha_nacimiento, condicion_id = :condicion_id, 
                           latitud = :latitud, longitud = :longitud, estado = :estado, tipo_miembro_id = :tipo_miembro_id 
                    WHERE id = :id";
            $this->pdo->prepare($sql)->execute($datosMiembro);

            // Sincronización de cargos: Limpiar e insertar de nuevo
            $sqlDelete = "DELETE FROM miembro_cargos WHERE miembro_id = ?";
            $this->pdo->prepare($sqlDelete)->execute([$datosMiembro['id']]);

            if (!empty($cargosIds)) {
                $this->insertarCargos($datosMiembro['id'], $cargosIds);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("Error en DAO Actualizar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper privado para evitar repetir código de inserción de cargos.
     */
    private function insertarCargos($miembroId, $cargosIds) {
        $sqlInsert = "INSERT INTO miembro_cargos (miembro_id, cargo_id) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sqlInsert);
        foreach ($cargosIds as $cargoId) {
            if (!empty($cargoId)) {
                $stmt->execute([$miembroId, $cargoId]);
            }
        }
    }

    public function eliminar($id) {
        $sql = "UPDATE miembros SET estado = 0 WHERE id = :id";
        return $this->pdo->prepare($sql)->execute(['id' => $id]);
    }

    public function activar($id) {
        $sql = "UPDATE miembros SET estado = 1 WHERE id = :id";
        return $this->pdo->prepare($sql)->execute(['id' => $id]);
    }

    // --- MÉTODOS PARA LLENAR SELECTS ---

    public function listarCargos() {
        return $this->pdo->query("SELECT id, nombre FROM cargos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCondiciones() {
        return $this->pdo->query("SELECT id, nombre FROM condiciones_miembro ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTipos() {
        return $this->pdo->query("SELECT id, nombre FROM tipos_miembro ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
}
<?php
namespace aplicacion\dao;

use aplicacion\config\Conexion; 
use PDO;
use Exception;

class DiscipuladoDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar(); 
    }

    // ==========================================
    // SECCIÓN GRUPOS
    // ==========================================

    public function listarGrupos() {
        try {
            $sql = "SELECT 
                        g.*, 
                        COALESCE(CONCAT(m.nombres, ' ', m.apellidos), 'Sin líder') as discipulador_nombre,
                        e.nombre as estado_nombre,
                        (SELECT COUNT(*) FROM discipulado_integrantes WHERE grupo_id = g.id) as num_integrantes
                    FROM discipulado_grupos g
                    LEFT JOIN miembros m ON g.discipulador_id = m.id
                    LEFT JOIN estados_discipulado e ON g.estado_id = e.id
                    ORDER BY g.id DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function registrarGrupo($datos) {
        try {
            $sql = "INSERT INTO discipulado_grupos (nombre, nivel, discipulador_id, estado_id) 
                    VALUES (:nombre, :nivel, :discipulador_id, :estado_id)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'nombre'          => $datos['nombre'],
                'nivel'           => $datos['nivel'],
                'discipulador_id' => $datos['discipulador_id'],
                'estado_id'       => $datos['estado_id']
            ]);
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizarGrupo($datos) {
        try {
            $sql = "UPDATE discipulado_grupos 
                    SET nombre = :nombre, 
                        nivel = :nivel, 
                        discipulador_id = :discipulador_id, 
                        estado_id = :estado_id 
                    WHERE id = :id";
            return $this->pdo->prepare($sql)->execute([
                'id'              => $datos['id'],
                'nombre'          => $datos['nombre'],
                'nivel'           => $datos['nivel'],
                'discipulador_id' => $datos['discipulador_id'],
                'estado_id'       => $datos['estado_id']
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Elimina un grupo (Nuevo método para que funcione el link de eliminar)
     */
    public function eliminarGrupo($id) {
        try {
            // Primero eliminamos a los integrantes del grupo por integridad referencial
            $this->pdo->prepare("DELETE FROM discipulado_integrantes WHERE grupo_id = ?")->execute([$id]);
            
            // Luego eliminamos el grupo
            $sql = "DELETE FROM discipulado_grupos WHERE id = ?";
            return $this->pdo->prepare($sql)->execute([$id]);
        } catch (Exception $e) {
            return false;
        }
    }

    // ==========================================
    // SECCIÓN INTEGRANTES
    // ==========================================

    public function listarIntegrantesDetallado($busqueda = '', $nivel = '', $discipulador_id = '') {
        try {
            $sql = "SELECT 
                        di.id as relacion_id,
                        m.id as miembro_id,
                        CONCAT(m.nombres, ' ', m.apellidos) as miembro_nombre,
                        g.nombre as grupo_nombre,
                        g.nivel as grupo_nivel,
                        CONCAT(lider.nombres, ' ', lider.apellidos) as discipulador_nombre
                    FROM discipulado_integrantes di
                    JOIN miembros m ON di.miembro_id = m.id
                    JOIN discipulado_grupos g ON di.grupo_id = g.id
                    JOIN miembros lider ON g.discipulador_id = lider.id
                    WHERE 1=1";

            if (!empty($busqueda)) $sql .= " AND (m.nombres LIKE :busq OR m.apellidos LIKE :busq)";
            if (!empty($nivel)) $sql .= " AND g.nivel = :nivel";
            if (!empty($discipulador_id)) $sql .= " AND g.discipulador_id = :dis_id";

            $stmt = $this->pdo->prepare($sql);
            
            if (!empty($busqueda)) $stmt->bindValue(':busq', "%$busqueda%");
            if (!empty($nivel)) $stmt->bindValue(':nivel', $nivel);
            if (!empty($discipulador_id)) $stmt->bindValue(':dis_id', $discipulador_id);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function agregarMiembroAGrupo($miembro_id, $grupo_id) {
        try {
            $check = $this->pdo->prepare("SELECT id FROM discipulado_integrantes WHERE miembro_id = ? AND grupo_id = ?");
            $check->execute([$miembro_id, $grupo_id]);
            if ($check->fetch()) return "existe";

            $sql = "INSERT INTO discipulado_integrantes (miembro_id, grupo_id) VALUES (?, ?)";
            return $this->pdo->prepare($sql)->execute([$miembro_id, $grupo_id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function eliminarIntegranteDeGrupo($id_relacion) {
        try {
            $sql = "DELETE FROM discipulado_integrantes WHERE id = ?";
            return $this->pdo->prepare($sql)->execute([$id_relacion]);
        } catch (Exception $e) {
            return false;
        }
    }

    // ==========================================
    // MÉTODOS DE APOYO
    // ==========================================

    public function listarDiscipuladores() {
        $sql = "SELECT m.id, CONCAT(m.nombres, ' ', m.apellidos) as nombre 
                FROM miembros m
                INNER JOIN miembro_cargos mc ON m.id = mc.miembro_id
                INNER JOIN cargos c ON mc.cargo_id = c.id
                WHERE (c.nombre LIKE '%Discipulador%' OR c.nombre LIKE '%Líder%')
                AND m.estado = 1 GROUP BY m.id";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTodosMiembrosActivos() {
        return $this->pdo->query("SELECT id, CONCAT(nombres, ' ', apellidos) as nombre FROM miembros WHERE estado = 1 ORDER BY nombres ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEstados() {
        return $this->pdo->query("SELECT * FROM estados_discipulado")->fetchAll(PDO::FETCH_ASSOC);
    }
}
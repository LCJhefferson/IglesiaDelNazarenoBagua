<?php
namespace aplicacion\dao;

// Importamos la conexión y PDO para que PHP sepa exactamente qué usar
use aplicacion\config\Conexion;
use PDO;
use Exception;

class InicioDAO {
    private $con;

    public function __construct() {
        // Obtenemos la instancia de conexión desde tu clase estática
        $this->con = Conexion::conectar();
    }

    /**
     * Obtiene los conteos principales para el Dashboard
     */
    public function obtenerEstadisticas() {
        try {
            // Verificamos que la conexión exista antes de consultar
            if (!$this->con) {
                return ['miembros' => 0, 'grupos' => 0, 'visitas' => 0, 'recursos' => 0];
            }

            $stats = [];
            
            // 1. Miembros: Actualmente vacía en tu SQL
            $stats['miembros'] = $this->con->query("SELECT COUNT(*) FROM miembros")->fetchColumn() ?: 0;
            
            // 2. Grupos de Discipulado: Actualmente vacía en tu SQL
            $stats['grupos']   = $this->con->query("SELECT COUNT(*) FROM discipulado_grupos WHERE estado = 'activo'")->fetchColumn() ?: 0;
            
            // 3. Visitas Pendientes: Tabla vacía, estado_id 1 = pendiente
            $stats['visitas']  = $this->con->query("SELECT COUNT(*) FROM visitas WHERE estado_id = 1")->fetchColumn() ?: 0;
            
            // 4. Recursos: DEBE MARCAR 2 (ya que tienes 2 inserts en tu SQL)
            $stats['recursos'] = $this->con->query("SELECT COUNT(*) FROM recursos")->fetchColumn() ?: 0;

            return $stats;
        } catch (Exception $e) {
            // Log de error interno si algo falla en la consulta
            error_log("Error en InicioDAO: " . $e->getMessage());
            return ['miembros' => 0, 'grupos' => 0, 'visitas' => 0, 'recursos' => 0];
        }
    }

    /**
     * Obtiene la lista de las próximas 5 visitas programadas
     */
    public function obtenerVisitasRecientes() {
        try {
            if (!$this->con) return [];

            $sql = "SELECT v.fecha_visita, v.motivo, m.nombres, m.apellidos 
                    FROM visitas v 
                    INNER JOIN miembros m ON v.miembro_id = m.id 
                    WHERE v.estado_id = 1 
                    ORDER BY v.fecha_visita ASC LIMIT 5";
                    
            $stmt = $this->con->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en InicioDAO (Visitas): " . $e->getMessage());
            return [];
        }
    }
}
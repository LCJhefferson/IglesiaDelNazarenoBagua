<?php
namespace aplicacion\dao;

use aplicacion\config\Conexion;
use PDO;
use DateTime;

class VisitaDAO {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // =========================================================================
    // 1. CONFIGURACIÓN DINÁMICA
    // =========================================================================
    public function obtenerMesesLimite() {
        $sql = "SELECT valor FROM configuracion_sistema WHERE clave = 'meses_limite_visita' LIMIT 1";
        try {
            $stmt = $this->db->query($sql);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? intval($resultado['valor']) : 6;
        } catch (\PDOException $e) {
            return 6;
        }
    }

    // =========================================================================
    // 2. LISTADO DE VISITAS CON MODOS (ÚLTIMO VS TODOS) - CÁLCULO POR DÍAS
    // =========================================================================
    public function listarConDetalles($modo = 'ultimo') {
        
        if ($modo === 'todos') {
            $sql = "SELECT 
                        v.id AS ultima_visita_id,
                        m.id AS miembro_id,
                        CONCAT(m.nombres, ' ', COALESCE(m.apellidos, '')) AS miembro_nombre,
                        m.direccion,
                        v.fecha_visita AS fecha_real,
                        v.motivo AS ultimo_motivo
                    FROM miembros m
                    INNER JOIN visitas v ON m.id = v.miembro_id
                    WHERE v.estado = 1 AND m.estado = 1
                    ORDER BY v.fecha_visita DESC";
        } else {
            $sql = "SELECT 
                        m.id AS miembro_id,
                        CONCAT(m.nombres, ' ', COALESCE(m.apellidos, '')) AS miembro_nombre,
                        m.direccion,
                        MAX(v.fecha_visita) AS fecha_real,
                        (SELECT v2.id FROM visitas v2 WHERE v2.miembro_id = m.id AND v2.estado = 1 ORDER BY v2.fecha_visita DESC, v2.id DESC LIMIT 1) AS ultima_visita_id,
                        (SELECT v3.motivo FROM visitas v3 WHERE v3.miembro_id = m.id AND v3.estado = 1 ORDER BY v3.fecha_visita DESC, v3.id DESC LIMIT 1) AS ultimo_motivo
                    FROM miembros m
                    LEFT JOIN visitas v ON m.id = v.miembro_id AND v.estado = 1
                    WHERE m.estado = 1
                    GROUP BY m.id, m.nombres, m.apellidos, m.direccion
                    ORDER BY fecha_real DESC, m.nombres ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertimos el límite de meses de la configuración a días totales (ej: 1 mes = 30 días, 3 meses = 90 días)
        $mesesLimite = $this->obtenerMesesLimite();
        $diasLimiteTotal = ($mesesLimite > 0 ? $mesesLimite : 1) * 30; 
        
        $hoy = new DateTime();

        foreach ($resultados as &$r) {
            if (!empty($r['fecha_real'])) {
                $r['ultima_fecha_formateada'] = date('d/m/Y', strtotime($r['fecha_real']));
                
                // Calculamos los DÍAS exactos transcurridos en lugar de meses
                $fechaVisita = new DateTime($r['fecha_real']);
                $intervalo = $hoy->diff($fechaVisita);
                $diasTranscurridos = $intervalo->days; // Obtiene el total neto de días de diferencia

                // Porcentaje basado en el total de días
                $porcentajeTranscurrido = ($diasTranscurridos / $diasLimiteTotal) * 100;

                // Asignación de rangos exactos solicitados
                if ($porcentajeTranscurrido <= 25) {
                    $r['clase_css'] = 'estado-verde-reciente';
                    $r['icono'] = 'fa-circle-check';
                    $r['estado_texto'] = 'Visitado reciente';
                } elseif ($porcentajeTranscurrido <= 70) {
                    $r['clase_css'] = 'estado-azul-intermedio';
                    $r['icono'] = 'fa-user-check';
                    $r['estado_texto'] = 'Visitado intermedio';
                } elseif ($porcentajeTranscurrido < 100) {
                    $r['clase_css'] = 'estado-amarillo-proximo';
                    $r['icono'] = 'fa-clock';
                    $r['estado_texto'] = 'Pendiente próximo';
                } else {
                    $r['clase_css'] = 'estado-rojo-critico';
                    $r['icono'] = 'fa-triangle-exclamation';
                    $r['estado_texto'] = 'Pendiente crítico';
                }
            } else {
                $r['ultima_fecha_formateada'] = 'Sin visitas';
                $r['clase_css'] = 'estado-rojo-critico';
                $r['icono'] = 'fa-triangle-exclamation';
                $r['estado_texto'] = 'Pendiente crítico';
            }
        }

        return $resultados;
    }

    // =========================================================================
    // 3. ELIMINACIÓN LÓGICA DE VISITAS
    // =========================================================================
    public function eliminarVisitaLogica($visita_id) {
        try {
            $sql = "UPDATE visitas SET estado = 0 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $visita_id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    // =========================================================================
    // 4. REGISTRO DE NUEVAS VISITAS
    // =========================================================================
    public function registrarNuevaVisita($miembro_id, $motivo, $usuario_id, $fecha_visita) {
        $sql = "INSERT INTO visitas (miembro_id, fecha_visita, motivo, registrado_por, estado_id, estado) 
                VALUES (:miembro_id, :fecha_visita, :motivo, :registrado_por, 1, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':miembro_id' => $miembro_id,
            ':fecha_visita' => $fecha_visita,
            ':motivo' => $motivo,
            ':registrado_por' => $usuario_id
        ]);
    }

    // =========================================================================
    // 5. LISTADO PARA MAPA - CÁLCULO POR DÍAS (SINCRO PERFECTA)
    // =========================================================================
    public function listarParaMapa() {
        $sql = "SELECT 
                    m.id AS miembro_id,
                    m.nombres AS nombre,     
                    m.apellidos AS apellido, 
                    m.latitud,
                    m.longitud,
                    m.direccion,
                    COALESCE(v.motivo, 'Sin visitas programadas') AS motivo,
                    COALESCE(v.fecha_visita, '') AS fecha_visita
                FROM miembros m
                LEFT JOIN visitas v ON v.id = (
                    SELECT v2.id 
                    FROM visitas v2 
                    WHERE v2.miembro_id = m.id AND v2.estado = 1 
                    ORDER BY v2.fecha_visita DESC 
                    LIMIT 1
                )
                WHERE m.latitud IS NOT NULL AND m.longitud IS NOT NULL AND m.latitud != '' AND m.longitud != '' AND m.estado = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mesesLimite = $this->obtenerMesesLimite();
        $diasLimiteTotal = ($mesesLimite > 0 ? $mesesLimite : 1) * 30; 
        
        $hoy = new DateTime();

        foreach ($resultados as &$r) {
            if (!empty($r['fecha_visita'])) {
                // Calcular días de diferencia netos
                $fechaVisita = new DateTime($r['fecha_visita']);
                $intervalo = $hoy->diff($fechaVisita);
                $diasTranscurridos = $intervalo->days;

                $porcentajeTranscurrido = ($diasTranscurridos / $diasLimiteTotal) * 100;

                // Aplicar exactamente la misma regla semafórica en base a días
                if ($porcentajeTranscurrido <= 25) {
                    $r['clase_css'] = 'estado-verde-reciente';
                    $r['estado_texto'] = 'Visitado reciente';
                } elseif ($porcentajeTranscurrido <= 70) {
                    $r['clase_css'] = 'estado-azul-intermedio';
                    $r['estado_texto'] = 'Visitado intermedio';
                } elseif ($porcentajeTranscurrido < 100) {
                    $r['clase_css'] = 'estado-amarillo-proximo';
                    $r['estado_texto'] = 'Pendiente próximo';
                } else {
                    $r['clase_css'] = 'estado-rojo-critico';
                    $r['estado_texto'] = 'Pendiente crítico';
                }
            } else {
                $r['clase_css'] = 'estado-sin-visita';
                $r['estado_texto'] = 'Sin visitas registradas';
            }
        }

        return $resultados;
    }
}
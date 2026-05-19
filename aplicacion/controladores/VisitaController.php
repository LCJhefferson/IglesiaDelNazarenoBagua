<?php
namespace aplicacion\controladores;

use aplicacion\dao\VisitaDAO;

class VisitaController {
    private $dao;

    public function __construct() {
        $this->dao = new VisitaDAO();
    }

    public function listar($offset = 0, $limit = 50, $filtros = []) {
        return $this->dao->listarConDetalles($offset, $limit, $filtros);
    }

    public function obtenerDatosMapaJSON() {
        header('Content-Type: application/json');
        $datos = $this->dao->listarParaMapa();
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // =========================================================================
    // GUARDAR NUEVA VISITA (Adaptado para AJAX)
    // =========================================================================
    public function guardarVisita() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $exito = false; // Por defecto asumimos fallo

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $miembro_id = intval($_POST['miembro_id'] ?? 0);
            $motivoPredefinido = $_POST['motivo_predefinido'] ?? 'Visita Regular';
            $fecha_visita = $_POST['fecha_visita'] ?? date('Y-m-d');
            $motivoFinal = ($motivoPredefinido === 'Otros') ? trim($_POST['motivo_libre'] ?? '') : $motivoPredefinido;
            $usuario_id = $_SESSION['usuario_id'] ?? 1;

            if ($miembro_id > 0 && !empty($motivoFinal)) {
                // El DAO devuelve true si se insertó correctamente
                $exito = $this->dao->registrarNuevaVisita($miembro_id, $motivoFinal, $usuario_id, $fecha_visita);
            }
        }
        
        // Respondemos en JSON para que el fetch de JS lo procese
        header('Content-Type: application/json');
        echo json_encode(['ok' => $exito]);
        exit;
    }

    // =========================================================================
    // GUARDAR AJUSTES DE MESES (Adaptado para AJAX)
    // =========================================================================
    public function guardarAjustesVisita() {
        $exito = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meses_limite'])) {
            $meses = intval($_POST['meses_limite']);
            if ($meses >= 1 && $meses <= 24) {
                $db = \aplicacion\config\Conexion::conectar();
                $sql = "UPDATE configuracion_sistema SET valor = :valor WHERE clave = 'meses_limite_visita'";
                $stmt = $db->prepare($sql);
                $exito = $stmt->execute([':valor' => $meses]);
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['ok' => $exito]);
        exit;
    }

    // =========================================================================
    // ELIMINAR VISITA / BORRADO LÓGICO (Adaptado para AJAX)
    // =========================================================================
    public function eliminarVisita() {
        $exito = false;
        $visitaId = $_POST['visita_id'] ?? null;
        
        if ($visitaId) {
            $exito = $this->dao->eliminarVisitaLogica($visitaId);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['ok' => $exito]);
        exit;
    }

}
<?php
namespace aplicacion\controladores;

use aplicacion\Repository\ReporteRepository;
use aplicacion\services\ReporteExportService;

class ReporteController {

    private $repository;
    private $exportService;

    public function __construct() {
        // En un Framework usarías inyección de dependencias, aquí los instanciamos de forma clásica
        $this->repository = new ReporteRepository();
        $this->exportService = new ReporteExportService();
    }

    public function obtenerDatos() {
        header('Content-Type: application/json');
        $modulo = $_GET['modulo'] ?? '';
        $filtros = $_POST ?? [];
        
        $datos = $this->repository->obtenerDatosPorModulo($modulo, $filtros);
        echo json_encode($datos);
        exit;
    }

    public function descargar() {
        $modulo = $_GET['modulo'] ?? '';
        $formato = $_GET['formato'] ?? '';
        $filtros = $_GET; 

        $datos = $this->repository->obtenerDatosPorModulo($modulo, $filtros);

        if ($modulo === 'miembros') {
            $columnas = ['Nombre Completo', 'Teléfono', 'Edad', 'Dirección', 'Origen', 'Condición', 'Estado'];
            $this->exportService->exportarEstandar("Reporte de Miembros", $columnas, $datos, $formato);
        } elseif ($modulo === 'visitas') {
            $columnas = ['Nombre Completo', 'Dirección', 'Última Visita', 'Motivo', 'Estado'];
            $this->exportService->exportarEstandar("Reporte de Visitas Supervisoras", $columnas, $datos, $formato);
        } elseif ($modulo === 'discipulado') {
            $this->exportService->exportarDiscipuladoEspecial($datos, $formato);
        }
    }
}
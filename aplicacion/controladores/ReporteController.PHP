<?php
namespace aplicacion\controladores;

// FORZAMOS LA CARGA MANUAL DE TUS SERVICIOS MODULARES
require_once __DIR__ . '/../services/reports/ReporteMiembros.php';
require_once __DIR__ . '/../services/reports/ExportarMiembros.php';
require_once __DIR__ . '/../services/reports/ReporteVisitas.php';
require_once __DIR__ . '/../services/reports/ExportarVisitas.php';
require_once __DIR__ . '/../services/reports/ReporteDiscipulado.php';
require_once __DIR__ . '/../services/reports/ExportarDiscipulado.php';

use aplicacion\services\reports\ReporteDiscipulado;
use aplicacion\services\reports\ExportarDiscipulado;
use aplicacion\modelos\ReporteModel;
use aplicacion\services\reports\ReporteMiembros;
use aplicacion\services\reports\ExportarMiembros;
use aplicacion\services\reports\ReporteVisitas;
use aplicacion\services\reports\ExportarVisitas;

class ReporteController {

    /**
     * Devuelve el JSON para pintar la tabla en tiempo real en la página
     */
    public function obtenerVistaPrevia() {
        header('Content-Type: application/json');
        
        $tipoReporte = $_GET['tipo'] ?? '';
        $filtros = $_GET; 
        $datos = $this->conseguirDatosPorMódulo($tipoReporte, $filtros);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Sugerencias reactivas para los componentes input autocomplete
     */
    public function sugerenciasAutocomplete() {
        header('Content-Type: application/json');
        
        $tipo = $_GET['tipo'] ?? ''; // miembros, grupos, discipuladores
        $term = $_GET['term'] ?? '';
        $resultados = [];

        if (strlen($term) >= 2) {
            if ($tipo === 'miembros') $resultados = ReporteModel::buscarMiembros($term);
            if ($tipo === 'grupos') $resultados = ReporteModel::buscarGrupos($term);
            if ($tipo === 'discipuladores') $resultados = ReporteModel::buscarDiscipuladores($term);
        }

        echo json_encode($resultados);
        exit;
    }

    /**
     * Endpoint encargado de las condiciones dinámicas al cargar la página
     * CORREGIDO: Carga tanto las condiciones de miembros como los estados de discipulado maestro
     */
    public function inicializarFiltros() {
        header('Content-Type: application/json');
        echo json_encode([
            'condiciones'       => ReporteModel::obtenerCondiciones(),
            'estados_discipulo' => ReporteModel::obtenerEstadosDiscipulo() // <-- Llenado exclusivo de la nueva tabla
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * ENDPOINT DE DESCARGAS: Procesa formatos delegando al Exportador modular externo correspondiente
     */
    public function descargarReporte() {
        $tipoReporte = $_GET['tipo'] ?? '';
        $formato     = $_GET['formato'] ?? 'excel';
        $filtros     = $_GET; 

        // Conseguimos la data filtrada usando el archivo de consulta respectivo
        $datos = $this->conseguirDatosPorMódulo($tipoReporte, $filtros);

        // Despachamos al exportador correcto según el tipo de reporte solicitado
        if ($tipoReporte === 'miembros') {
            if ($formato === 'excel') {
                ExportarMiembros::aExcel($datos);
            } elseif ($formato === 'csv') {
                ExportarMiembros::aCSV($datos);
            } elseif ($formato === 'pdf') {
                ExportarMiembros::aPDF($datos);
            }
        } elseif ($tipoReporte === 'visitas') {
            if ($formato === 'excel') {
                ExportarVisitas::aExcel($datos);
            } elseif ($formato === 'csv') {
                ExportarVisitas::aCSV($datos);
            } elseif ($formato === 'pdf') {
                ExportarVisitas::aPDF($datos);
            }
        } elseif ($tipoReporte === 'discipulado') { // <-- Soporte de exportación añadido
            if ($formato === 'excel') {
                ExportarDiscipulado::aExcel($datos);
            } elseif ($formato === 'csv') {
                ExportarDiscipulado::aCSV($datos);
            } elseif ($formato === 'pdf') {
                ExportarDiscipulado::aPDF($datos);
            }
        }
        
        exit;
    }

    /**
     * Helper interno para centralizar la obtención de datos
     */
    private function conseguirDatosPorMódulo($tipoReporte, $filtros) {
        switch ($tipoReporte) {
            case 'miembros':
                return ReporteMiembros::generar($filtros);
            case 'visitas':
                return ReporteVisitas::generar($filtros);
            case 'discipulado': // <-- Enlace directo al nuevo servicio modular
                return ReporteDiscipulado::generar($filtros);
            default:
                return [];
        }
    }
}
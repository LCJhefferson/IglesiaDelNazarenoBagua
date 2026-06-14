<?php
namespace aplicacion\controladores;

// FORZAMOS LA CARGA MANUAL USANDO TU RUTA REAL (services/reports) PARA EVITAR EL CLASS NOT FOUND
require_once __DIR__ . '/../services/reports/ReporteMiembros.php';

use aplicacion\modelos\ReporteModel;
use aplicacion\services\reports\ReporteMiembros;
use aplicacion\services\reports\ReporteVisitas;
use aplicacion\services\reports\ReporteGrupos;

class ReporteController {

    public function obtenerVistaPrevia() {
        header('Content-Type: application/json');
        
        $tipoReporte = $_GET['tipo'] ?? '';
        $filtros = $_GET; 
        $datos = $this->conseguirDatosPorMódulo($tipoReporte, $filtros);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

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
     */
    public function inicializarFiltros() {
        header('Content-Type: application/json');
        echo json_encode([
            'condiciones' => ReporteModel::obtenerCondiciones() // Mantenido exactamente igual
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * FUNCIÓN CLAVE: Permite descargar el reporte filtrado en formatos (Excel, CSV, PDF)
     */
    public function descargarReporte() {
        $tipoReporte = $_GET['tipo'] ?? '';
        $formato     = $_GET['formato'] ?? 'excel';
        $filtros     = $_GET; 

        // Conseguimos la data usando el archivo modular correcto
        $datos = $this->conseguirDatosPorMódulo($tipoReporte, $filtros);

        // Despachamos al formateador de descargas
        if ($formato === 'excel') {
            $this->exportarAExcel($datos, $tipoReporte);
        } elseif ($formato === 'csv') {
            $this->exportarACSV($datos, $tipoReporte);
        } elseif ($formato === 'pdf') {
            $this->exportarAPDF($datos, $tipoReporte);
        }
    }

    /**
     * Helper interno para centralizar a qué servicio llamar
     */
    private function conseguirDatosPorMódulo($tipoReporte, $filtros) {
        switch ($tipoReporte) {
            case 'miembros':
                return ReporteMiembros::generar($filtros);
            default:
                return [];
        }
    }

    /**
     * Generador nativo de Excel binario/HTML para descargas inmediatas
     */
   /**
     * Generador de Excel con diseño profesional y estilizado
     */
    private function exportarAExcel($datos, $nombre) {
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=reporte_{$nombre}_" . date('Ymd') . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        if (empty($datos)) {
            echo "No hay registros para este reporte.";
            exit;
        }

        // Construcción de la tabla con estilos CSS que Microsoft Excel interpreta nativamente
        echo "
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <meta charset='utf-8'>
            <style>
                .titulo { font-family: Arial, sans-serif; font-size: 16px; font-weight: bold; color: #4F46E5; text-align: center; }
                .subtitulo { font-family: Arial, sans-serif; font-size: 11px; color: #555555; text-align: center; }
                th { font-family: Arial, sans-serif; font-size: 11px; background-color: #4F46E5; color: #ffffff; font-weight: bold; text-align: left; border: 0.5pt solid #cbd5e1; padding: 6px; }
                td { font-family: Arial, sans-serif; font-size: 11px; border: 0.5pt solid #4b4b4b; padding: 5px; }
                .par { background-color: #f8fafc; }
            </style>
        </head>
        <body>
            <table>
                <tr>
                    <td colspan='7' class='titulo'>REPORTE DE MIEMBROS</td>
                </tr>
                <tr>
                    <td colspan='7' class='subtitulo'>Iglesia del Nazareno Bagua - Exportado el " . date('d/m/Y H:i') . "</td>
                </tr>
                <tr><td colspan='7' style='border:none;'></td></tr> <thead>
                    <tr>
                        <th>Nombre y Apellidos</th>
                        <th>Teléfono</th>
                        <th>Edad</th>
                        <th>Dirección</th>
                        <th>Origen</th>
                        <th>Condición</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>";

        $contador = 0;
        foreach ($datos as $fila) {
            // Evaluamos el color de fila intercalado (Zebra striping)
            $claseFila = ($contador % 2 === 0) ? "" : "class='par'";
            
            // Traducción exacta de los códigos de origen
            $origenTexto = 'Otros';
            if (($fila['origen'] ?? '') == 1) {
                $origenTexto = 'Local';
            } elseif (($fila['origen'] ?? '') == 2) {
                $origenTexto = 'Externo';
            }

            echo "<tr {$claseFila}>";
            echo "<td>" . htmlspecialchars($fila['nombre_completo'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($fila['telefono'] ?? '-') . "</td>";
            echo "<td style='text-align: center;'>" . htmlspecialchars($fila['edad'] ?? '0') . "</td>";
            echo "<td>" . htmlspecialchars($fila['direccion'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($origenTexto) . "</td>";
            echo "<td>" . htmlspecialchars($fila['condicion'] ?? 'Sin asignar') . "</td>";
            echo "<td>" . htmlspecialchars($fila['estado'] ?? 'Activo') . "</td>";
            echo "</tr>";
            
            $contador++;
        }

        echo "
                </tbody>
            </table>
        </body>
        </html>";
        exit;
    }

    /**
     * Generador nativo de archivos planos CSV
     */
    private function exportarACSV($datos, $nombre) {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=reporte_{$nombre}_" . date('Ymd') . ".csv");
        
        $output = fopen('php://output', 'w');
        
        if (!empty($datos)) {
            fputcsv($output, array_keys($datos[0]));
            foreach ($datos as $fila) {
                fputcsv($output, $fila);
            }
        }
        fclose($output);
        exit;
    }

    /**
     * Renderizador HTML limpio adaptado para visualización o guardado como PDF/Impresión rápido
     */
    /**
     * Renderizador que fuerza la descarga física del reporte como un documento PDF/HTML legible
     */
    /**
     * Genera un archivo binario PDF real utilizando la librería Dompdf instalada
     */
    private function exportarAPDF($datos, $nombre) {
        // 1. Construimos el HTML estructurado con estilos CSS legibles para el PDF
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 10px; color: #333; }
                h2 { text-align: center; color: #4F46E5; font-size: 20px; margin-bottom: 5px; }
                .sub { text-align: center; font-size: 12px; color: #666; margin-bottom: 25px; }
                table { border-collapse: collapse; width: 100%; font-size: 11px; }
                th { background-color: #4F46E5; color: white; padding: 8px; text-align: left; font-weight: bold; }
                td { border: 1px solid #e2e8f0; padding: 7px; }
                tr:nth-child(even) { background-color: #f8fafc; }
            </style>
        </head>
        <body>
            <h2>Reporte de Miembros</h2>
            <div class='sub'>Iglesia del Nazareno Bagua - Generado el " . date('d/m/Y') . "</div>
            <table>
                <thead>
                    <tr>
                        <th>Nombre y Apellidos</th>
                        <th>Teléfono</th>
                        <th>Edad</th>
                        <th>Dirección</th>
                        <th>Origen</th>
                        <th>Condición</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>";

        if (!empty($datos)) {
            foreach ($datos as $f) {
                // Traducción del origen: 1 = Local, 2 = Externo
                $origenTexto = 'Otros';
                if ($f['origen'] == 1) {
                    $origenTexto = 'Local';
                } elseif ($f['origen'] == 2) {
                    $origenTexto = 'Externo';
                }

                $html .= "<tr>
                    <td>" . htmlspecialchars($f['nombre_completo'] ?? '') . "</td>
                    <td>" . htmlspecialchars($f['telefono'] ?? '-') . "</td>
                    <td>" . htmlspecialchars($f['edad'] ?? '0') . "</td>
                    <td>" . htmlspecialchars($f['direccion'] ?? '-') . "</td>
                    <td>" . htmlspecialchars($origenTexto) . "</td>
                    <td>" . htmlspecialchars($f['condicion'] ?? 'Sin asignar') . "</td>
                    <td>" . htmlspecialchars($f['estado'] ?? 'Activo') . "</td>
                </tr>";
            }
        } else {
            $html .= "<tr><td colspan='7' style='text-align:center;'>No hay registros disponibles.</td></tr>";
        }

        $html .= "
                </tbody>
            </table>
        </body>
        </html>";

        // 2. Inicializamos Dompdf y cargamos el HTML creado
        // Nota: Al usar el autoload global de Composer en el index.php, la clase está disponible en el espacio de nombres global
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);

        // (Opcional) Configuramos el tamaño del papel a A4 u Letter en posición vertical
        $dompdf->setPaper('A4', 'portrait');

        // 3. Renderizamos el HTML como un PDF binario real
        $dompdf->render();

        // 4. Forzamos la descarga del archivo adjunto al navegador
        // El parámetro 'Attachment' => true hace que se descargue directo en lugar de abrirse en la pestaña
        $dompdf->stream("reporte_{$nombre}_" . date('Ymd') . ".pdf", array("Attachment" => true));
        exit;
    }
}
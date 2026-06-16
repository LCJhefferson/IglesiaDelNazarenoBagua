<?php
namespace aplicacion\services\reports;

class ExportarVisitas {

    /**
     * Generador de Excel con diseño profesional para el Reporte de Visitas
     */
    public static function aExcel($datos) {
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=reporte_visitas_" . date('Ymd') . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        if (empty($datos)) {
            echo "No hay registros para este reporte de visitas.";
            exit;
        }

        echo "
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <meta charset='utf-8'>
            <style>
                .titulo { font-family: Arial, sans-serif; font-size: 16px; font-weight: bold; color: #0EA5E9; text-align: center; }
                .subtitulo { font-family: Arial, sans-serif; font-size: 11px; color: #555555; text-align: center; }
                th { font-family: Arial, sans-serif; font-size: 11px; background-color: #0EA5E9; color: #ffffff; font-weight: bold; text-align: left; border: 0.5pt solid #cbd5e1; padding: 6px; }
                td { font-family: Arial, sans-serif; font-size: 11px; border: 0.5pt solid #cbd5e1; padding: 5px; }
                .par { background-color: #f0f9ff; }
            </style>
        </head>
        <body>
            <table>
                <tr>
                    <td colspan='5' class='titulo'>REPORTE DE CONTROL DE VISITAS</td>
                </tr>
                <tr>
                    <td colspan='5' class='subtitulo'>Iglesia del Nazareno Bagua - Exportado el " . date('d/m/Y H:i') . "</td>
                </tr>
                <tr><td colspan='5' style='border:none;'></td></tr> 
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Dirección</th>
                        <th>Última Visita</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>";

        $contador = 0;
        foreach ($datos as $fila) {
            $claseFila = ($contador % 2 === 0) ? "" : "class='par'";

            echo "<tr {$claseFila}>";
            echo "<td>" . htmlspecialchars($fila['nombre_completo'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($fila['direccion'] ?? 'Sin dirección') . "</td>";
            echo "<td style='text-align: center;'>" . htmlspecialchars($fila['ultima_visita'] ?? 'Sin visitas') . "</td>";
            echo "<td>" . htmlspecialchars($fila['motivo'] ?? 'Sin motivo') . "</td>";
            echo "<td>" . htmlspecialchars($fila['estado'] ?? 'Pendiente') . "</td>";
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
     * Generador nativo de archivos planos CSV para Visitas
     */
    public static function aCSV($datos) {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=reporte_visitas_" . date('Ymd') . ".csv");
        
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
     * Genera un archivo PDF real utilizando Dompdf para el Reporte de Visitas
     */
    public static function aPDF($datos) {
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 10px; color: #333; }
                h2 { text-align: center; color: #0EA5E9; font-size: 20px; margin-bottom: 5px; }
                .sub { text-align: center; font-size: 12px; color: #666; margin-bottom: 25px; }
                table { border-collapse: collapse; width: 100%; font-size: 11px; }
                th { background-color: #0EA5E9; color: white; padding: 8px; text-align: left; font-weight: bold; }
                td { border: 1px solid #e2e8f0; padding: 7px; }
                tr:nth-child(even) { background-color: #f0f9ff; }
            </style>
        </head>
        <body>
            <h2>Reporte de Control de Visitas</h2>
            <div class='sub'>Iglesia del Nazareno Bagua - Generado el " . date('d/m/Y') . "</div>
            <table>
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Dirección</th>
                        <th>Última Visita</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>";

        if (!empty($datos)) {
            foreach ($datos as $f) {
                $html .= "<tr>
                    <td>" . htmlspecialchars($f['nombre_completo'] ?? '') . "</td>
                    <td>" . htmlspecialchars($f['direccion'] ?? 'Sin dirección') . "</td>
                    <td style='text-align: center;'>" . htmlspecialchars($f['ultima_visita'] ?? 'Sin visitas') . "</td>
                    <td>" . htmlspecialchars($f['motivo'] ?? 'Sin motivo') . "</td>
                    <td>" . htmlspecialchars($f['estado'] ?? 'Pendiente') . "</td>
                </tr>";
            }
        } else {
            $html .= "<tr><td colspan='5' style='text-align:center;'>No hay registros de visitas disponibles.</td></tr>";
        }

        $html .= "
                </tbody>
            </table>
        </body>
        </html>";

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("reporte_visitas_" . date('Ymd') . ".pdf", array("Attachment" => true));
        exit;
    }
}
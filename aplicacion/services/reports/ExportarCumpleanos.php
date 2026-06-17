<?php
namespace aplicacion\services\reports;

class ExportarCumpleanos {

    /**
     * Generador de Excel con diseño profesional unificado (Estilo Miembros)
     */
    public static function aExcel($datos) {
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=reporte_cumpleanos_" . date('Ymd') . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        if (empty($datos)) {
            echo "No hay registros para este reporte.";
            exit;
        }

        // Construcción idéntica con el formato XML corporativo interpretado por Microsoft Excel
        echo "
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <meta charset='utf-8'>
            <style>
                .titulo { font-family: Arial, sans-serif; font-size: 16px; font-weight: bold; color: #ffac11; text-align: center; }
                .subtitulo { font-family: Arial, sans-serif; font-size: 11px; color: #555555; text-align: center; }
                th { font-family: Arial, sans-serif; font-size: 11px; background-color: #ffac11; color: #ffffff; font-weight: bold; text-align: left; border: 0.5pt solid #cbd5e1; padding: 6px; }
                td { font-family: Arial, sans-serif; font-size: 11px; border: 0.5pt solid #cbd5e1; padding: 5px; }
                .par { background-color: #f8fafc; }
            </style>
        </head>
        <body>
            <table>
                <tr>
                    <td colspan='4' class='titulo'>REPORTE MENSUAL DE CUMPLEALOS</td>
                </tr>
                <tr>
                    <td colspan='4' class='subtitulo'>Iglesia del Nazareno Bagua - Exportado el " . date('d/m/Y H:i') . "</td>
                </tr>
                <tr><td colspan='4' style='border:none;'></td></tr> 
                <thead>
                    <tr>
                        <th>Miembro / Cumpleañero</th>
                        <th>Teléfono</th>
                        <th>Fecha de Nacimiento</th>
                        <th>Edad Actual</th>
                    </tr>
                </thead>
                <tbody>";

        $contador = 0;
        foreach ($datos as $fila) {
            $claseFila = ($contador % 2 === 0) ? "" : "class='par'";
            $formatted_date = !empty($fila['fecha_nacimiento']) ? date('d/m/Y', strtotime($fila['fecha_nacimiento'])) : '-';

            echo "<tr {$claseFila}>";
            echo "<td>" . htmlspecialchars($fila['nombre_completo'] ?? '') . "</td>";
            echo "<td style='vnd.ms-excel.numberformat:@'>" . htmlspecialchars($fila['telefono'] ?? '-') . "</td>"; // Conserva ceros iniciales
            echo "<td style='text-align: center;'>" . htmlspecialchars($formatted_date) . "</td>";
            echo "<td style='text-align: center;'>" . htmlspecialchars($fila['edad'] ?? '0') . " años</td>";
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
     * Genera un archivo PDF real con Dompdf y fuerza su DESCARGA AUTOMÁTICA
     */
    public static function aPDF($datos) {
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 10px; color: #333; }
                h2 { text-align: center; color: #ffac11; font-size: 20px; margin-bottom: 5px; }
                .sub { text-align: center; font-size: 12px; color: #363333; margin-bottom: 25px; }
                table { border-collapse: collapse; width: 100%; font-size: 11px; }
                th { background-color: #ffac11; color: white; padding: 8px; text-align: left; font-weight: bold; }
                td { border: 1px solid #ced5df; padding: 7px; }
                tr:nth-child(even) { background-color: #f8fafc; }
            </style>
        </head>
        <body>
            <h2>Reporte Mensual de Cumpleaños</h2>
            <div class='sub'>Iglesia del Nazareno Bagua - Generado el " . date('d/m/Y') . "</div>
            <table>
                <thead>
                    <tr>
                        <th>Miembro / Cumpleañero</th>
                        <th>Teléfono</th>
                        <th>Fecha de Nacimiento</th>
                        <th>Edad Actual</th>
                    </tr>
                </thead>
                <tbody>";

        if (!empty($datos)) {
            foreach ($datos as $f) {
                $formatted_date = !empty($f['fecha_nacimiento']) ? date('d/m/Y', strtotime($f['fecha_nacimiento'])) : '-';
                $html .= "<tr>
                    <td>" . htmlspecialchars($f['nombre_completo'] ?? '') . "</td>
                    <td>" . htmlspecialchars($f['telefono'] ?? '-') . "</td>
                    <td style='text-align: center;'>" . htmlspecialchars($formatted_date) . "</td>
                    <td style='text-align: center;'>" . htmlspecialchars($f['edad'] ?? '0') . " años</td>
                </tr>";
            }
        } else {
            $html .= "<tr><td colspan='4' style='text-align:center;'>No hay registros disponibles.</td></tr>";
        }

        $html .= "
                </tbody>
            </table>
        </body>
        </html>";

        // Inicializar la librería e inyectar el código HTML estructurado
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // CRITICAL CON FIX: Mandamos el array "Attachment" => true para obligar la descarga del archivo sin abrir pestañas del navegador
        $dompdf->stream("reporte_cumpleanos_" . date('Ymd') . ".pdf", array("Attachment" => true));
        exit;
    }
}
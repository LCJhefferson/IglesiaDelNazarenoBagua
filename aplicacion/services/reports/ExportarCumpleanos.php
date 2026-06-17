<?php
namespace aplicacion\services\reports;

class ExportarCumpleanos {

    /**
     * Exporta los datos a formato Excel (.xls) nativo para navegadores
     */
    public static function aExcel($datos) {
        $filename = "Reporte_Cumpleanos_" . date('Ymd_His') . ".xls";
        
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);

        // Emitir el BOM UTF-8 para que Excel reconozca tildes y caracteres especiales
        echo "\xEF\xBB\xBF";

        echo "<table border='1'>";
        echo "<tr style='background-color:#0284c7; color:#ffffff; font-weight:bold;'>";
        echo "<th>Miembro / Cumpleañero</th>";
        echo "<th>Teléfono</th>";
        echo "<th>Fecha de Nacimiento</th>";
        echo "<th>Edad Actual</th>";
        echo "</tr>";

        foreach ($datos as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['nombre_completo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['telefono'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['fecha_nacimiento']) . "</td>";
            echo "<td>" . htmlspecialchars($row['edad']) . " años</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }

    /**
     * Exporta los datos a formato CSV estándar delimitado por comas
     */
    public static function aCSV($datos) {
        $filename = "Reporte_Cumpleanos_" . date('Ymd_His') . ".csv";
        
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        $output = fopen("php://output", "w");
        
        // Emitir el BOM UTF-8 para Excel en CSV
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeceras
        fputcsv($output, ['Miembro / Cumpleañero', 'Telefono', 'Fecha de Nacimiento', 'Edad Actual'], ';');

        foreach ($datos as $row) {
            fputcsv($output, [
                $row['nombre_completo'],
                $row['telefono'] ?? '-',
                $row['fecha_nacimiento'],
                $row['edad'] . " anos"
            ], ';');
        }
        
        fclose($output);
        exit;
    }

    /**
     * Estructura base para PDF (Si usas FPDF o DomPDF)
     */
    public static function aPDF($datos) {
        // Si usas una librería global de PDF como FPDF implementas el diseño aquí
        // Por ahora dejamos una salida limpia o aviso seguro de descarga
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=\"Reporte_Cumpleanos.pdf\"");
        echo "Funcionalidad de impresión PDF en desarrollo. Datos procesados: " . count($datos);
        exit;
    }
}
<?php
namespace aplicacion\services\reports;

class ExportarDiscipulado {

    public static function aExcel($datos) {
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=reporte_discipulado_" . date('Ymd') . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <meta charset='utf-8'>
            <style>
                .titulo { font-family: Arial, sans-serif; font-size: 16px; font-weight: bold; color: #10B981; text-align: center; }
                .subtitulo { font-family: Arial, sans-serif; font-size: 11px; color: #555555; text-align: center; }
                th { font-family: Arial, sans-serif; font-size: 11px; background-color: #10B981; color: #ffffff; font-weight: bold; text-align: left; border: 0.5pt solid #cbd5e1; padding: 6px; }
                td { font-family: Arial, sans-serif; font-size: 11px; border: 0.5pt solid #cbd5e1; padding: 5px; }
                .par { background-color: #f0fdf4; }
            </style>
        </head>
        <body>
            <table>
                <tr><td colspan='4' class='titulo'>REPORTE AVANZADO DE DISCIPULADO</td></tr>
                <tr><td colspan='4' class='subtitulo'>Iglesia del Nazareno Bagua - " . date('d/m/Y H:i') . "</td></tr>
                <tr><td colspan='4' style='border:none;'></td></tr> 
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Discipulador / Líder</th>
                        <th>Integrante (Alumno)</th>
                        <th>Estado del Integrante</th>
                    </tr>
                </thead>
                <tbody>";

        $contador = 0;
        foreach ($datos as $fila) {
            $clase = ($contador % 2 === 0) ? "" : "class='par'";
            echo "<tr {$clase}>";
            echo "<td>" . htmlspecialchars($fila['grupo_nombre'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($fila['discipulador_nombre'] ?? 'Sin asignar') . "</td>";
            echo "<td>" . htmlspecialchars($fila['integrante_nombre'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($fila['estado_alumno_texto'] ?? '') . "</td>";
            echo "</tr>";
            $contador++;
        }

        echo "</tbody></table></body></html>";
        exit;
    }

    public static function aCSV($datos) {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=reporte_discipulado_" . date('Ymd') . ".csv");
        $output = fopen('php://output', 'w');
        if (!empty($datos)) {
            fputcsv($output, ['Grupo', 'Discipulador', 'Integrante', 'Estado Integrante']);
            foreach ($datos as $fila) {
                fputcsv($output, [
                    $fila['grupo_nombre'],
                    $fila['discipulador_nombre'],
                    $fila['integrante_nombre'],
                    $fila['estado_alumno_texto']
                ]);
            }
        }
        fclose($output);
        exit;
    }

    public static function aPDF($datos) {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; margin: 10px; color: #333; }
                h2 { text-align: center; color: #10B981; font-size: 20px; }
                .sub { text-align: center; font-size: 11px; color: #666; margin-bottom: 20px; }
                table { border-collapse: collapse; width: 100%; font-size: 11px; }
                th { background-color: #10B981; color: white; padding: 7px; text-align: left; }
                td { border: 1px solid #e2e8f0; padding: 6px; }
                tr:nth-child(even) { background-color: #f0fdf4; }
            </style>
        </head>
        <body>
            <h2>Reporte Avanzado de Discipulado</h2>
            <div class='sub'>Iglesia del Nazareno Bagua - Generado el " . date('d/m/Y') . "</div>
            <table>
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Discipulador / Líder</th>
                        <th>Integrante (Alumno)</th>
                        <th>Estado del Integrante</th>
                    </tr>
                </thead>
                <tbody>";

        if (!empty($datos)) {
            foreach ($datos as $f) {
                $html .= "<tr>
                    <td>" . htmlspecialchars($f['grupo_nombre']) . "</td>
                    <td>" . htmlspecialchars($f['discipulador_nombre'] ?? 'Sin asignar') . "</td>
                    <td>" . htmlspecialchars($f['integrante_nombre']) . "</td>
                    <td>" . htmlspecialchars($f['estado_alumno_texto']) . "</td>
                </tr>";
            }
        } else {
            $html .= "<tr><td colspan='4' style='text-align:center;'>No hay registros disponibles.</td></tr>";
        }

        $html .= "</tbody></table></body></html>";

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("reporte_discipulado_" . date('Ymd') . ".pdf", ["Attachment" => true]);
        exit;
    }
}
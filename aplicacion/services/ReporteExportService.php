<?php
namespace aplicacion\services;

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ReporteExportService {

    public function exportarEstandar($titulo, $columnas, $datos, $formato) {
        if ($formato === 'pdf') {
            $html = '<style>body{font-family:sans-serif;font-size:11px;} table{width:100%;border-collapse:collapse;} th{background:#2563eb;color:#fff;padding:6px;text-align:left;} td{padding:6px;border-bottom:1px solid #ddd;}</style>';
            $html .= "<h2>$titulo</h2><p>Generado el: ".date('d/m/Y')."</p><table><thead><tr>";
            foreach($columnas as $col) $html .= "<th>$col</th>";
            $html .= "</tr></thead><tbody>";
            foreach($datos as $row) {
                $html .= "<tr>";
                foreach($row as $val) $html .= "<td>".htmlspecialchars($val)."</td>";
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("$titulo.pdf", ["Attachment" => true]);
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $colLetra = 'A';
            foreach($columnas as $col) {
                $sheet->setCellValue($colLetra.'1', $col);
                $sheet->getStyle($colLetra.'1')->getFont()->setBold(true);
                $colLetra++;
            }

            $f = 2;
            foreach($datos as $row) {
                $colLetra = 'A';
                foreach($row as $val) {
                    $sheet->setCellValue($colLetra.$f, $val);
                    $colLetra++;
                }
                $f++;
            }

            header('Content-Disposition: attachment;filename="reporte.' . ($formato === 'excel' ? 'xlsx' : 'csv') . '"');
            $writer = ($formato === 'excel') ? new Xlsx($spreadsheet) : new Csv($spreadsheet);
            $writer->save('php://output');
        }
        exit;
    }

    public function exportarDiscipuladoEspecial($datos, $formato) {
        $primerElemento = $datos[0] ?? ['grupo_nombre'=>'General', 'nivel'=>'N/A', 'grupo_estado'=>'N/A', 'discipulador'=>'N/A'];

        if ($formato === 'pdf') {
            $html = '
            <style>body{font-family:sans-serif; font-size:12px;} .header-box{background:#f1f5f9; padding:10px; border:1px solid #cbd5e1; margin-bottom:15px;} table{width:100%; border-collapse:collapse;} th{background:#475569; color:white; padding:8px;} td{padding:8px; border-bottom:1px solid #e2e8f0;}</style>
            <h2>Reporte de Avance de Discipulado</h2>
            <div class="header-box">
                <b>Nombre del Grupo:</b> '.htmlspecialchars($primerElemento['grupo_nombre']).' &nbsp;&nbsp;&nbsp;&nbsp; <b>Nivel:</b> '.htmlspecialchars($primerElemento['nivel']).' &nbsp;&nbsp;&nbsp;&nbsp; <b>Estado del Grupo:</b> '.htmlspecialchars($primerElemento['grupo_estado']).'<br>
                <b>Discipulador:</b> '.htmlspecialchars($primerElemento['discipulador']).'
            </div>
            <table><thead><tr><th>Nombre del Integrante</th><th>Estado Integrante</th></tr></thead><tbody>';
            foreach($datos as $row) {
                $html .= "<tr><td>".htmlspecialchars($row['integrante'])."</td><td><b>".$row['estado_alumno']."</b></td></tr>";
            }
            $html .= '</tbody></table>';

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream("Discipulado.pdf", ["Attachment" => true]);
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Nombre del grupo: ' . $primerElemento['grupo_nombre']);
            $sheet->setCellValue('C1', 'Nivel: ' . $primerElemento['nivel']);
            $sheet->setCellValue('E1', 'Estado del grupo: ' . $primerElemento['grupo_estado']);
            $sheet->setCellValue('A2', 'Discipulador: ' . $primerElemento['discipulador']);
            $sheet->getStyle('A1:E2')->getFont()->setBold(true);

            $sheet->setCellValue('A4', 'Integrante(s)');
            $sheet->setCellValue('B4', 'Estado Integrante');
            $sheet->getStyle('A4:B4')->getFont()->setBold(true);

            $f = 5;
            foreach($datos as $row) {
                $sheet->setCellValue('A'.$f, $row['integrante']);
                $sheet->setCellValue('B'.$f, $row['estado_alumno']);
                $f++;
            }

            header('Content-Disposition: attachment;filename="Discipulado.' . ($formato === 'excel' ? 'xlsx' : 'csv') . '"');
            $writer = ($formato === 'excel') ? new Xlsx($spreadsheet) : new Csv($spreadsheet);
            $writer->save('php://output');
        }
        exit;
    }
}
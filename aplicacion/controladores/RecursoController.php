<?php
namespace aplicacion\controladores;

use aplicacion\modelos\Recurso;
use aplicacion\modelos\RecursoPapelera;
use aplicacion\services\RecursoThumbService;

class RecursoController {

    public function listar(): array {
        return Recurso::listar();
    }

    public function listarPapelera(): array {
        return RecursoPapelera::listar();
    }

    public function guardar(): void {
        $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $rutaArchivo = $_POST['ruta_actual'] ?? '';
        $tipoArchivo = $_POST['tipo_actual'] ?? 'doc';

        if (!empty($_FILES['archivo_principal']['name'])) {
            $carpeta = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/admin/imagenes/recursos/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);
            $nombre      = time() . '_' . basename($_FILES['archivo_principal']['name']);
            $tipoArchivo = $this->detectarTipo($_FILES['archivo_principal']['type']);
            if (move_uploaded_file($_FILES['archivo_principal']['tmp_name'], $carpeta . $nombre)) {
                $rutaArchivo = 'admin/imagenes/recursos/' . $nombre;
            }
        }

        $datos = [
            'titulo'         => trim($_POST['titulo']         ?? ''),
            'descripcion'    => trim($_POST['descripcion']    ?? ''),
            'categoria'      => $_POST['categoria']           ?? '',
            'tipo'           => $tipoArchivo,
            'ruta_archivo'   => $rutaArchivo,
            'enlace_youtube' => trim($_POST['enlace_youtube'] ?? ''),
            'creado_por'     => $_SESSION['usuario_id']       ?? null,
        ];

        if ($id) {
            Recurso::update($datos, ['id' => $id]);
            RecursoThumbService::generar($id, $rutaArchivo, $tipoArchivo, $datos['enlace_youtube']);
        } else {
            $nuevoId = Recurso::create($datos);
            RecursoThumbService::generar($nuevoId, $rutaArchivo, $tipoArchivo, $datos['enlace_youtube']);
        }

        header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin&exito=1&pagina=archivos");
        exit;
    }

    public function descargar(int $id): void {
        $recurso = Recurso::find($id);

        if (!$recurso) {
            header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin");
            exit;
        }

        if (empty($recurso['ruta_archivo'])) {
            if (!empty($recurso['enlace_youtube'])) {
                Recurso::incrementarDescargas($id);
                header('Location: ' . $recurso['enlace_youtube']);
                exit;
            }
            header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin");
            exit;
        }

        $ruta_abs = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . $recurso['ruta_archivo'];

        if (!file_exists($ruta_abs)) {
            header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin");
            exit;
        }

        Recurso::incrementarDescargas($id);

        while (ob_get_level() > 0) ob_end_clean();

        $mime = mime_content_type($ruta_abs) ?: 'application/octet-stream';
        header('Content-Type: '        . $mime);
        header('Content-Disposition: attachment; filename="' . basename($recurso['ruta_archivo']) . '"');
        header('Content-Length: '      . filesize($ruta_abs));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($ruta_abs);
        exit;
    }

    public function eliminar(int $id): void {
        Recurso::moverAPapelera($id, $_SESSION['usuario_id'] ?? null);
        header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin&exito=2&pagina=archivos");
        exit;
    }

    public function restaurar(int $papeleraId): void {
        RecursoPapelera::restaurar($papeleraId);
        header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin&exito=3&pagina=papelera");
        exit;
    }

    public function eliminarDefinitivo(int $papeleraId): void {
        RecursoPapelera::eliminarDefinitivo($papeleraId);
        header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin&exito=4&pagina=papelera");
        exit;
    }

    public function vaciarPapelera(): void {
        RecursoPapelera::vaciar();
        header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin&exito=5&pagina=papelera");
        exit;
    }

    public function regenerarUno(int $id, string $ruta, string $tipo, string $youtube): void {
        RecursoThumbService::generar($id, $ruta, $tipo, $youtube);
    }

    private function detectarTipo(string $mime): string {
        if (str_contains($mime, 'pdf'))   return 'pdf';
        if (str_contains($mime, 'image')) return 'img';
        if (str_contains($mime, 'video')) return 'vid';
        return 'doc';
    }
}

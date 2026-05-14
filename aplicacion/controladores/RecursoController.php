<?php
namespace aplicacion\controladores;

use aplicacion\dao\RecursoDAO;

class RecursoController {

    private $dao;

    public function __construct() {
        $this->dao = new RecursoDAO();
    }

    // ── LISTAR ──
    public function listar(): array {
        return $this->dao->listar();
    }

    // ── LISTAR PAPELERA ──
    public function listarPapelera(): array {
        return $this->dao->listarPapelera();
    }

    // ── GUARDAR (crear o actualizar) ──
    public function guardar(): void {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        // Subir archivo si existe
        $rutaArchivo  = $_POST['ruta_actual'] ?? '';
        $tipoArchivo  = $_POST['tipo_actual'] ?? 'doc';

        if (!empty($_FILES['archivo_principal']['name'])) {
            $carpeta = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/admin/imagenes/recursos/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
            $nombre      = time() . '_' . basename($_FILES['archivo_principal']['name']);
            $tipoArchivo = $this->detectarTipo($_FILES['archivo_principal']['type']);
            if (move_uploaded_file($_FILES['archivo_principal']['tmp_name'], $carpeta . $nombre)) {
                $rutaArchivo = 'admin/imagenes/recursos/' . $nombre;
            }
        }

        $datos = [
            'titulo'         => trim($_POST['titulo']        ?? ''),
            'descripcion'    => trim($_POST['descripcion']   ?? ''),
            'categoria'      => $_POST['categoria']          ?? '',
            'tipo'           => $tipoArchivo,
            'ruta_archivo'   => $rutaArchivo,
            'enlace_youtube' => trim($_POST['enlace_youtube'] ?? ''),
            'creado_por'     => $_SESSION['usuario_id']       ?? null,
        ];

        if ($id) {
            $datos['id'] = $id;
            $this->dao->actualizar($datos);
        } else {
            $this->dao->insertar($datos);
        }

        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?seccion=recurso_admin&exito=1&pagina=archivos");
        exit;
    }

    // ── DESCARGAR ──
    public function descargar(int $id): void {
        $recurso = $this->dao->obtenerPorId($id);

        if (!$recurso) {
            header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?seccion=recurso_admin");
            exit;
        }

        if (empty($recurso['ruta_archivo'])) {
            if (!empty($recurso['enlace_youtube'])) {
                $this->dao->incrementarDescargas($id);
                header('Location: ' . $recurso['enlace_youtube']);
                exit;
            }
            header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?seccion=recurso_admin");
            exit;
        }

        $ruta_abs = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . $recurso['ruta_archivo'];

        if (!file_exists($ruta_abs)) {
            header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?seccion=recurso_admin");
            exit;
        }

        $this->dao->incrementarDescargas($id);

        $nombre_descarga = basename($recurso['ruta_archivo']);
        $mime = mime_content_type($ruta_abs) ?: 'application/octet-stream';

        // Limpiar buffer para no mezclar HTML del dashboard con el archivo
        while (ob_get_level() > 0) ob_end_clean();

        header('Content-Type: '        . $mime);
        header('Content-Disposition: attachment; filename="' . $nombre_descarga . '"');
        header('Content-Length: '      . filesize($ruta_abs));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($ruta_abs);
        exit;
    }

    // ── MOVER A PAPELERA ──
    public function eliminar(int $id): void {
        $this->dao->moverAPapelera($id);
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?seccion=recurso_admin&exito=2&pagina=archivos");
        exit;
    }

    // ── RESTAURAR ──
    public function restaurar(int $id): void {
        $this->dao->restaurar($id);
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?seccion=recurso_admin&exito=3&pagina=papelera");
        exit;
    }

    // ── ELIMINAR DEFINITIVO ──
    public function eliminarDefinitivo(int $id): void {
        $this->dao->eliminarDefinitivo($id);
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?seccion=recurso_admin&exito=4&pagina=papelera");
        exit;
    }

    // ── VACIAR PAPELERA ──
    public function vaciarPapelera(): void {
        $this->dao->vaciarPapelera();
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?seccion=recurso_admin&exito=5&pagina=papelera");
        exit;
    }

    // ── DETECTAR TIPO POR MIME ──
    private function detectarTipo(string $mime): string {
        if (str_contains($mime, 'pdf'))   return 'pdf';
        if (str_contains($mime, 'image')) return 'img';
        if (str_contains($mime, 'video')) return 'vid';
        return 'doc';
    }
}
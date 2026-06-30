<?php
namespace aplicacion\controladores;

use aplicacion\modelos\Recurso;
use aplicacion\modelos\RecursoPapelera;
use aplicacion\services\RecursoThumbService;

class RecursoController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Redirección híbrida y ULTRA-SEGURA
     * Protegida contra XSS (Inyección de código) y Redirecciones Abiertas (Phishing)
     */
    private function redireccionar(string $url): void {
        if (preg_match('/^https?:\/\//i', $url)) {
            $hostPermitido = $_SERVER['HTTP_HOST'];
            $hostDestino = parse_url($url, PHP_URL_HOST);
            
            if ($hostDestino !== $hostPermitido) {
                $url = "/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin";
            }
        }

        if (!headers_sent()) {
            header("Location: " . $url);
            exit;
        } else {
            $urlSeguraJs = json_encode($url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            echo "<script>window.location.href = " . $urlSeguraJs . ";</script>";
            exit;
        }
    }

    public function listar(): iterable {
        return Recurso::listar();
    }

    public function listarPapelera(): iterable {
        return RecursoPapelera::listar();
    }

    public function guardar(): void {
        $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $rutaArchivo = $_POST['ruta_actual'] ?? '';
        $tipoArchivo = $_POST['tipo_actual'] ?? 'doc';

        if (!empty($_FILES['archivo_principal']['name']) && $_FILES['archivo_principal']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['archivo_principal']['name'], PATHINFO_EXTENSION));
            $permitidos = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'mp4', 'mov', 'avi', 'xls', 'xlsx', 'ppt', 'pptx', 'gif', 'webp'];
            
            if (!in_array($extension, $permitidos, true)) {
                header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin&error=invalid_extension");
                exit;
            }

            $carpeta = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/admin/imagenes/recursos/';
            
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0755, true);
            }
            
            $nombreLimpio = preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['archivo_principal']['name']));
            $nombreArchivo = time() . '_' . $nombreLimpio;
            $rutaFisica = $carpeta . $nombreArchivo;
            
            $tipoArchivo = $this->detectarTipo($_FILES['archivo_principal']['type']);
            
            if (move_uploaded_file($_FILES['archivo_principal']['tmp_name'], $rutaFisica)) {
                $rutaArchivo = 'admin/imagenes/recursos/' . $nombreArchivo;
            }
        }

        $usuarioId = $_SESSION['usuario']->id ?? $_SESSION['usuario_id'] ?? null;

        $datos = [
            'titulo'         => trim(htmlspecialchars($_POST['titulo'] ?? '', ENT_QUOTES)),
            'descripcion'    => trim(htmlspecialchars($_POST['descripcion'] ?? '', ENT_QUOTES)),
            'categoria'      => $_POST['categoria'] ?? '',
            'tipo'           => $tipoArchivo,
            'ruta_archivo'   => $rutaArchivo,
            'enlace_youtube' => trim(filter_var($_POST['enlace_youtube'] ?? '', FILTER_SANITIZE_URL)),
        ];
        
        if (!$id) {
            $datos['creado_por'] = $usuarioId;
        } else {
            $datos['editado_por'] = $usuarioId;
        }

        if ($id) {
            // RBAC validación
            $recursoExistente = Recurso::find($id);
            if ($recursoExistente) {
                $rolIdActual = (int)($_SESSION['rol_id'] ?? 0);
                if (!in_array($rolIdActual, [1, 2, 11]) && (int)$recursoExistente->creado_por !== (int)$usuarioId) {
                    $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin&error=permiso");
                    return;
                }
            }

            Recurso::where('id', $id)->update($datos);
            RecursoThumbService::generar($id, $rutaArchivo, $tipoArchivo, $datos['enlace_youtube']);
        } else {
            $nuevoRecurso = Recurso::create($datos);
            RecursoThumbService::generar($nuevoRecurso->id, $rutaArchivo, $tipoArchivo, $datos['enlace_youtube']);
        }

        $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin&exito=1&pagina=archivos");
    }

   public function descargar(int $id): void {
        $recurso = Recurso::find($id);

        if (!$recurso) {
            $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin");
            return;
        }

        if (empty($recurso->ruta_archivo)) {
            if (!empty($recurso->enlace_youtube)) {
                Recurso::incrementarDescargas($id);
                $this->redireccionar($recurso->enlace_youtube);
                return;
            }
            $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin");
            return;
        }

        $ruta_abs = realpath($_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . $recurso->ruta_archivo);
        $base_dir = realpath($_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/admin/imagenes/recursos/');

        if (!$ruta_abs || !str_starts_with($ruta_abs, $base_dir) || !file_exists($ruta_abs)) {
            $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin");
            return;
        }

        Recurso::incrementarDescargas($id);

        // ====================================================================
        // SOLUCIÓN DEFINITIVA: Limpiar el búfer radicalmente ANTES de los headers
        // ====================================================================
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Determinar el MIME type correcto
        $mime = mime_content_type($ruta_abs) ?: 'application/octet-stream';
        
        // Enviar cabeceras HTTP limpias
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($recurso->ruta_archivo) . '"');
        header('Content-Length: ' . filesize($ruta_abs));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        
        // Leer el archivo y cortar la ejecución inmediatamente
        readfile($ruta_abs);
        exit;
    }

    public function eliminar(int $id): void {
        $usuarioId = $_SESSION['usuario']->id ?? $_SESSION['usuario_id'] ?? null;
        
        // RBAC validación
        $recursoExistente = Recurso::find($id);
        if ($recursoExistente) {
            $rolIdActual = (int)($_SESSION['rol_id'] ?? 0);
            if (!in_array($rolIdActual, [1, 2, 11]) && (int)$recursoExistente->creado_por !== (int)$usuarioId) {
                $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin&error=permiso");
                return;
            }
        }

        Recurso::moverAPapelera($id, $usuarioId);
        $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin&exito=2&pagina=archivos");
    }

    public function restaurar(int $papeleraId): void {
        RecursoPapelera::restaurar($papeleraId);
        $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin&exito=3&pagina=papelera");
    }

    public function eliminarDefinitivo(int $papeleraId): void {
        RecursoPapelera::eliminarDefinitivo($papeleraId);
        $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin&exito=4&pagina=papelera");
    }

    public function vaciarPapelera(): void {
        RecursoPapelera::vaciar();
        $this->redireccionar("/IglesiaDelNazarenoBagua/dashboard?seccion=recurso_admin&exito=5&pagina=papelera");
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
<?php
namespace aplicacion\controladores;

use aplicacion\modelos\Recurso;
use aplicacion\modelos\RecursoPapelera;
use aplicacion\services\RecursoThumbService;

class RecursoController {

    /**
     * Usamos 'iterable' en lugar de 'array' para soportar 
     * Colecciones de Eloquent sin romper el tipado estricto de PHP.
     */
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

        // 1. Manejo seguro de subida de archivos
        if (!empty($_FILES['archivo_principal']['name']) && $_FILES['archivo_principal']['error'] === UPLOAD_ERR_OK) {
            $carpeta = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/admin/imagenes/recursos/';
            
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0755, true);
            }
            
            // Sanitizar el nombre del archivo (solo letras, números, puntos y guiones)
            $nombreLimpio = preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['archivo_principal']['name']));
            $nombreArchivo = time() . '_' . $nombreLimpio;
            $rutaFisica = $carpeta . $nombreArchivo;
            
            $tipoArchivo = $this->detectarTipo($_FILES['archivo_principal']['type']);
            
            if (move_uploaded_file($_FILES['archivo_principal']['tmp_name'], $rutaFisica)) {
                $rutaArchivo = 'admin/imagenes/recursos/' . $nombreArchivo;
            }
        }

        // 2. Preparar y limpiar datos
        $datos = [
            'titulo'         => trim(htmlspecialchars($_POST['titulo'] ?? '', ENT_QUOTES)),
            'descripcion'    => trim(htmlspecialchars($_POST['descripcion'] ?? '', ENT_QUOTES)),
            'categoria'      => $_POST['categoria'] ?? '',
            'tipo'           => $tipoArchivo,
            'ruta_archivo'   => $rutaArchivo,
            'enlace_youtube' => trim(filter_var($_POST['enlace_youtube'] ?? '', FILTER_SANITIZE_URL)),
            'creado_por'     => $_SESSION['usuario_id'] ?? null,
        ];

        // 3. Guardar en Base de Datos usando Eloquent
        if ($id) {
            Recurso::where('id', $id)->update($datos);
            RecursoThumbService::generar($id, $rutaArchivo, $tipoArchivo, $datos['enlace_youtube']);
        } else {
            $nuevoRecurso = Recurso::create($datos);
            // Obtenemos el ID del objeto recién creado por Eloquent
            RecursoThumbService::generar($nuevoRecurso->id, $rutaArchivo, $tipoArchivo, $datos['enlace_youtube']);
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

        // Eloquent usa propiedades de objeto (->), NO arreglos ([])
        if (empty($recurso->ruta_archivo)) {
            if (!empty($recurso->enlace_youtube)) {
                Recurso::incrementarDescargas($id);
                header('Location: ' . $recurso->enlace_youtube);
                exit;
            }
            header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin");
            exit;
        }

        // 4. Seguridad: Prevenir Directory Traversal (Asegurar que el archivo exista en el directorio seguro)
        $ruta_abs = realpath($_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . $recurso->ruta_archivo);
        $base_dir = realpath($_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/admin/imagenes/recursos/');

        // Verificamos que el archivo existe y que está estrictamente dentro de la carpeta permitida
        if (!$ruta_abs || !str_starts_with($ruta_abs, $base_dir) || !file_exists($ruta_abs)) {
            header("Location: /IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin");
            exit;
        }

        Recurso::incrementarDescargas($id);

        // Limpiar buffers de salida para evitar que código HTML se filtre en el archivo y lo corrompa
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $mime = mime_content_type($ruta_abs) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($recurso->ruta_archivo) . '"');
        header('Content-Length: ' . filesize($ruta_abs));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public'); // Importante para compatibilidad de descargas en algunos navegadores
        
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
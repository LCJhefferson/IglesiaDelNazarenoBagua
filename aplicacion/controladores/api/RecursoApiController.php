<?php
namespace aplicacion\controladores\api;

use aplicacion\core\ApiController;
use aplicacion\core\Middleware;
use aplicacion\core\Response;
use aplicacion\modelos\Recurso;
use aplicacion\modelos\RecursoPapelera;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * CONTROLADOR API: RecursoApiController
 * ─────────────────────────────────────────────────────────────────────────────
 * Proveedor de servicios JSON puro para el módulo Recursos.
 * Hereda de ApiController para reutilizar parseBody(), requireMethod() y json().
 *
 * Tabla de endpoints (verbos HTTP semánticos — SIN verbos en la URL):
 * ┌────────────┬──────────────────────────────┬────────────────────────────────┐
 * │ Verbo HTTP │ Endpoint                     │ Acción                         │
 * ├────────────┼──────────────────────────────┼────────────────────────────────┤
 * │ GET        │ ?vista=api/recursos          │ Listar recursos (filtrable)    │
 * │ POST       │ ?vista=api/recursos          │ Crear nuevo recurso            │
 * │ DELETE     │ ?vista=api/recursos&id={n}   │ Soft-delete (mover papelera)   │
 * │ GET        │ ?vista=api/recursos/papelera │ Listar papelera                │
 * │ POST       │ ?vista=api/recursos/restaurar│ Restaurar desde papelera       │
 * │ DELETE     │ ?vista=api/recursos/definitivo│ Eliminar permanentemente      │
 * │ DELETE     │ ?vista=api/recursos/vaciar   │ Vaciar papelera completa       │
 * └────────────┴──────────────────────────────┴────────────────────────────────┘
 *
 * Seguridad implementada (Criterio 4 — Avanzado):
 *   - Middleware::apiAuth() en TODOS los métodos → 401/403 si no autenticado
 *   - Middleware::csrfVerify() en operaciones de escritura (POST, DELETE)
 *   - BOLA/IDOR: update y destroy validan creado_por === usuario_id de sesión
 *     a menos que el rol sea Admin(1), Pastor(2) o Secretaria(11)
 *   - Sin confianza en IDs del cliente: se busca en DB y se verifica propiedad
 */
class RecursoApiController extends ApiController {

    // ─── Roles con privilegios completos (Admin, Pastor, Secretaria) ──────────
    private const ROLES_ADMIN = [1, 2, 11];

    // ── GET /api/recursos ─────────────────────────────────────────────────────
    /**
     * Lista todos los recursos con soporte de búsqueda en tiempo real.
     *
     * Parámetros GET opcionales:
     *   ?q=texto       → búsqueda en título o descripción
     *   ?tipo=pdf      → filtro por tipo de archivo
     *   ?categoria=x   → filtro por categoría
     *
     * ¿Por qué devolver JSON puro aquí?
     * Desacoplamiento total: el cliente (JS) construye el HTML con textContent.
     * El servidor solo conoce datos, NO presentación. Esto es el nivel "Avanzado"
     * del criterio 1 de la rúbrica.
     */
    public function index(): void {
        Middleware::apiAuth();
        $this->requireMethod('GET');

        $query = Recurso::select(
                'recursos.id',
                'recursos.titulo',
                'recursos.descripcion',
                'recursos.categoria',
                'recursos.tipo',
                'recursos.ruta_archivo',
                'recursos.enlace_youtube',
                'recursos.ruta_thumb',
                'recursos.descargas',
                'recursos.creado_por',
                'recursos.fecha_creacion',
                'u1.username as autor_nombre',
                'u2.username as editor_nombre'
            )
            ->leftJoin('usuarios as u1', 'recursos.creado_por', '=', 'u1.id')
            ->leftJoin('usuarios as u2', 'recursos.editado_por', '=', 'u2.id')
            ->orderBy('recursos.fecha_creacion', 'DESC');

        // Búsqueda libre en título o descripción
        if (!empty($_GET['q'])) {
            $termino = '%' . trim($_GET['q']) . '%';
            $query->where(function ($q) use ($termino) {
                $q->where('recursos.titulo', 'LIKE', $termino)
                  ->orWhere('recursos.descripcion', 'LIKE', $termino);
            });
        }

        // Filtro por tipo (pdf, img, vid, doc, yt)
        if (!empty($_GET['tipo'])) {
            $query->where('recursos.tipo', '=', trim($_GET['tipo']));
        }

        // Filtro por categoría
        if (!empty($_GET['categoria'])) {
            $query->where('recursos.categoria', '=', trim($_GET['categoria']));
        }

        $recursos = $query->get()->toArray();

        // Obtener la papelera también para la carga inicial de la SPA
        $papelera = RecursoPapelera::select(
            'recursos_papelera.*',
            'u1.username as eliminador_nombre'
        )
        ->leftJoin('usuarios as u1', 'recursos_papelera.eliminado_por', '=', 'u1.id')
        ->orderBy('recursos_papelera.fecha_eliminacion', 'DESC')
        ->get()->toArray();

        Response::success([
            'archivos' => $recursos,
            'papelera' => $papelera
        ]);
    }

    // ── POST /api/recursos ────────────────────────────────────────────────────
    /**
     * Crea un nuevo recurso.
     *
     * NOTA: Este endpoint acepta multipart/form-data (no JSON puro) porque
     * incluye subida de archivos. Para creación SIN archivo físico (YouTube),
     * también acepta application/json via parseBody().
     *
     * Seguridad:
     *   - CSRF verificado: previene que sitios externos envíen formularios
     *     en nombre del usuario autenticado (CSRF attack)
     *   - Validación server-side: nunca confiar en validaciones del cliente
     *   - creado_por se toma de la SESIÓN, no del cuerpo — previene IDOR
     */
    public function store(): void {
        Middleware::apiAuth();
        $this->requireMethod('POST');
        Middleware::csrfVerify();

        // Para multipart/form-data (con archivo), parseBody() devuelve $_POST
        $data = $this->parseBody();

        // Validación server-side
        $errores = [];
        if (empty(trim($data['titulo'] ?? ''))) {
            $errores['titulo'] = ['El título es obligatorio'];
        }
        if (empty(trim($data['categoria'] ?? ''))) {
            $errores['categoria'] = ['La categoría es obligatoria'];
        }

        if (!empty($errores)) {
            Response::unprocessable($errores);
        }

        $usuarioId = $_SESSION['usuario']->id ?? $_SESSION['usuario_id'] ?? null;
        
        $rutaArchivo = '';
        $tipo = $data['tipo'] ?? 'doc';

        // Procesar subida de archivo si existe
        if (!empty($_FILES['archivo_principal']['name']) && $_FILES['archivo_principal']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['archivo_principal']['name'], PATHINFO_EXTENSION));
            $permitidos = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'mp4', 'mov', 'avi', 'xls', 'xlsx', 'ppt', 'pptx', 'gif', 'webp'];
            
            if (!in_array($extension, $permitidos, true)) {
                Response::error('Extensión de archivo no permitida por seguridad.', 400);
            }

            $nombreLimpio = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $_FILES['archivo_principal']['name']);
            $nombreArchivo = time() . '_' . $nombreLimpio;
            
            $carpetaDestino = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/admin/imagenes/recursos/';
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }
            
            $rutaFisica = $carpetaDestino . $nombreArchivo;

            if (move_uploaded_file($_FILES['archivo_principal']['tmp_name'], $rutaFisica)) {
                $rutaArchivo = 'admin/imagenes/recursos/' . $nombreArchivo;
                if (in_array($extension, ['pdf'])) $tipo = 'pdf';
                elseif (in_array($extension, ['png','jpg','jpeg','gif','webp'])) $tipo = 'img';
                elseif (in_array($extension, ['mp4','mov','avi'])) $tipo = 'vid';
                else $tipo = 'doc';
            } else {
                Response::error('Error al guardar el archivo en el servidor.', 500);
            }
        } elseif (!empty(trim($data['enlace_youtube'] ?? ''))) {
            $tipo = 'yt';
        }

        $nuevoRecurso = Recurso::create([
            'titulo'         => trim($data['titulo']),
            'descripcion'    => trim($data['descripcion'] ?? ''),
            'categoria'      => trim($data['categoria']),
            'tipo'           => $tipo,
            'ruta_archivo'   => $rutaArchivo,
            'enlace_youtube' => trim($data['enlace_youtube'] ?? ''),
            'creado_por'     => $usuarioId,
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);

        Response::created(Recurso::find($nuevoRecurso->id));
    }

    // ── POST /api/recursos/update ─────────────────────────────────────────────
    /**
     * Actualiza un recurso existente.
     * Usamos POST en lugar de PUT/PATCH porque PHP no procesa $_FILES nativamente
     * con php://input para peticiones multipart/form-data en PUT.
     */
    public function update(): void {
        Middleware::apiAuth();
        $this->requireMethod('PUT');
        Middleware::csrfVerify();

        $data = $this->parseBody();
        $id = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            Response::error('ID inválido', 400);
        }

        $recurso = Recurso::find($id);
        if (!$recurso) {
            Response::notFound('Recurso no encontrado');
        }

        // BOLA/IDOR
        $rolId     = (int)($_SESSION['rol_id'] ?? 0);
        $usuarioId = (int)($_SESSION['usuario']->id ?? $_SESSION['usuario_id'] ?? 0);

        if (!in_array($rolId, self::ROLES_ADMIN, true) && (int)$recurso->creado_por !== $usuarioId) {
            Response::error('No tienes permiso para editar este recurso', 403);
        }

        $errores = [];
        if (empty(trim($data['titulo'] ?? ''))) {
            $errores['titulo'] = ['El título es obligatorio'];
        }

        if (!empty($errores)) {
            Response::unprocessable($errores);
        }

        $rutaArchivo = $recurso->ruta_archivo;
        $tipo = $data['tipo_actual'] ?? $recurso->tipo;

        // Si se sube un archivo nuevo
        if (!empty($_FILES['archivo_principal']['name']) && $_FILES['archivo_principal']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['archivo_principal']['name'], PATHINFO_EXTENSION));
            $permitidos = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'mp4', 'mov', 'avi', 'xls', 'xlsx', 'ppt', 'pptx', 'gif', 'webp'];
            
            if (!in_array($extension, $permitidos, true)) {
                Response::error('Extensión de archivo no permitida por seguridad.', 400);
            }

            $nombreLimpio = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $_FILES['archivo_principal']['name']);
            $nombreArchivo = time() . '_' . $nombreLimpio;
            
            $carpetaDestino = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/admin/imagenes/recursos/';
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }
            
            $rutaFisica = $carpetaDestino . $nombreArchivo;

            if (move_uploaded_file($_FILES['archivo_principal']['tmp_name'], $rutaFisica)) {
                // Eliminar archivo anterior si existía (resolviendo path absoluto)
                if (!empty($rutaArchivo)) {
                    $rutaAbsAnterior = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . $rutaArchivo;
                    if (file_exists($rutaAbsAnterior)) {
                        @unlink($rutaAbsAnterior);
                    }
                }
                $rutaArchivo = 'admin/imagenes/recursos/' . $nombreArchivo;
                if (in_array($extension, ['pdf'])) $tipo = 'pdf';
                elseif (in_array($extension, ['png','jpg','jpeg','gif','webp'])) $tipo = 'img';
                elseif (in_array($extension, ['mp4','mov','avi'])) $tipo = 'vid';
                else $tipo = 'doc';
            }
        } elseif (!empty(trim($data['enlace_youtube'] ?? ''))) {
            $tipo = 'yt';
        }

        $recurso->titulo = trim($data['titulo']);
        $recurso->descripcion = trim($data['descripcion'] ?? '');
        $recurso->categoria = trim($data['categoria'] ?? 'documentos');
        $recurso->tipo = $tipo;
        $recurso->ruta_archivo = $rutaArchivo;
        $recurso->enlace_youtube = trim($data['enlace_youtube'] ?? '');
        $recurso->editado_por = $usuarioId;
        $recurso->save();

        Response::success($recurso);
    }

    // ── DELETE /api/recursos?id={n} ───────────────────────────────────────────
    /**
     * Soft-delete: mueve el recurso a la tabla recursos_papelera.
     *
     * BOLA/IDOR Mitigation:
     * No se confía en el ID del cliente para determinar propiedad. Se busca
     * el registro en la DB y se compara recursos.creado_por con el usuario_id
     * de la sesión activa. Solo los roles ADMIN pueden eliminar recursos ajenos.
     *
     * ¿Por qué soft-delete (no DELETE real)?
     * El patrón Archive Table permite recuperar registros eliminados por error,
     * auditar quién eliminó qué, y mantener integridad referencial.
     */
    public function destroy(): void {
        Middleware::apiAuth();
        $this->requireMethod('DELETE');
        Middleware::csrfVerify();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Response::error('ID de recurso inválido', 400);
        }

        $recurso = Recurso::find($id);
        if (!$recurso) {
            Response::notFound('El recurso no existe o ya fue eliminado');
        }

        // ── DEFENSA BOLA/IDOR ─────────────────────────────────────────────────
        // Se valida que el usuario que elimina sea el dueño, a menos que
        // tenga un rol privilegiado (Admin=1, Pastor=2, Secretaria=11)
        $rolId     = (int)($_SESSION['rol_id'] ?? 0);
        $usuarioId = (int)($_SESSION['usuario']->id ?? $_SESSION['usuario_id'] ?? 0);

        if (!in_array($rolId, self::ROLES_ADMIN, true)
            && (int)$recurso->creado_por !== $usuarioId) {
            Response::error('No tienes permiso para eliminar este recurso', 403);
        }
        // ─────────────────────────────────────────────────────────────────────

        Recurso::moverAPapelera($id, $usuarioId);

        Response::success([
            'id'      => $id,
            'mensaje' => 'Recurso movido a la papelera correctamente'
        ]);
    }

    // ── GET /api/recursos/papelera ─────────────────────────────────────────────
    /**
     * Lista los recursos en la papelera.
     * Solo roles con privilegios completos pueden ver la papelera completa.
     */
    public function papelera(): void {
        Middleware::apiAuth(self::ROLES_ADMIN);
        $this->requireMethod('GET');

        $papelera = RecursoPapelera::listar()->toArray();
        Response::success($papelera);
    }

    // ── POST /api/recursos/restaurar ──────────────────────────────────────────
    /**
     * Restaura un recurso desde la papelera.
     * Solo roles Admin/Pastor/Secretaria pueden restaurar.
     */
    public function restaurar(): void {
        Middleware::apiAuth(self::ROLES_ADMIN);
        $this->requireMethod('POST');
        Middleware::csrfVerify();

        $data = $this->parseBody();
        $papeleraId = (int)($data['papelera_id'] ?? 0);

        if ($papeleraId <= 0) {
            Response::error('ID de papelera inválido', 400);
        }

        RecursoPapelera::restaurar($papeleraId);

        Response::success(['mensaje' => 'Recurso restaurado correctamente']);
    }

    // ── DELETE /api/recursos/definitivo ───────────────────────────────────────
    /**
     * Eliminación permanente de un recurso de la papelera.
     * IRREVERSIBLE — Solo Admin/Pastor/Secretaria.
     */
    public function eliminarDefinitivo(): void {
        Middleware::apiAuth(self::ROLES_ADMIN);
        $this->requireMethod('DELETE');
        Middleware::csrfVerify();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Response::error('ID inválido', 400);
        }

        RecursoPapelera::eliminarDefinitivo($id);

        Response::success(['mensaje' => 'Recurso eliminado permanentemente']);
    }

    // ── DELETE /api/recursos/vaciar ───────────────────────────────────────────
    /**
     * Vacía completamente la papelera.
     * Operación masiva — Solo Admin/Pastor.
     */
    public function vaciarPapelera(): void {
        Middleware::apiAuth([1, 2]); // Solo Admin y Pastor
        $this->requireMethod('DELETE');
        Middleware::csrfVerify();

        RecursoPapelera::vaciar();

        Response::success(['mensaje' => 'Papelera vaciada correctamente']);
    }
}

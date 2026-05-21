<?php
namespace aplicacion\controladores\api;

use aplicacion\core\Middleware;
use aplicacion\core\Response;
use aplicacion\core\Validator;
use aplicacion\dao\RecursoDAO;
use aplicacion\modelos\Recurso;

class RecursoApiController {

    private RecursoDAO $dao;

    public function __construct() {
        $this->dao = new RecursoDAO();
    }

    // GET /api/recursos
    public function index(array $params = []): void {
        Middleware::apiAuth();
        Response::success(Recurso::all());
    }

    // GET /api/recursos/{id}
    public function show(array $params): void {
        Middleware::apiAuth();
        $recurso = Recurso::find((int) $params['id']);
        if (!$recurso) {
            Response::notFound('Recurso no encontrado');
        }
        Response::success($recurso);
    }

    // POST /api/recursos
    public function store(array $params = []): void {
        Middleware::apiAuth();

        $data = $this->parseBody();

        $v = Validator::make($data, [
            'titulo'    => 'required|max:200',
            'categoria' => 'required|max:100',
            'tipo'      => 'required|in:pdf,img,vid,doc,yt',
        ]);

        if ($v->fails()) {
            Response::unprocessable($v->errors());
        }

        $id = Recurso::create([
            'titulo'         => trim($data['titulo']),
            'descripcion'    => trim($data['descripcion']    ?? ''),
            'categoria'      => trim($data['categoria']),
            'tipo'           => $data['tipo'],
            'ruta_archivo'   => $data['ruta_archivo']        ?? '',
            'enlace_youtube' => trim($data['enlace_youtube'] ?? ''),
            'creado_por'     => $_SESSION['usuario_id']      ?? null,
        ]);

        Response::created(Recurso::find($id));
    }

    // PUT /api/recursos/{id}
    public function update(array $params): void {
        Middleware::apiAuth();

        $recurso = Recurso::find((int) $params['id']);
        if (!$recurso) {
            Response::notFound('Recurso no encontrado');
        }

        $data = $this->parseBody();

        $v = Validator::make($data, [
            'titulo'    => 'required|max:200',
            'categoria' => 'required|max:100',
            'tipo'      => 'required|in:pdf,img,vid,doc,yt',
        ]);

        if ($v->fails()) {
            Response::unprocessable($v->errors());
        }

        Recurso::update(
            [
                'titulo'         => trim($data['titulo']),
                'descripcion'    => trim($data['descripcion']    ?? ''),
                'categoria'      => trim($data['categoria']),
                'tipo'           => $data['tipo'],
                'ruta_archivo'   => $data['ruta_archivo']        ?? $recurso['ruta_archivo'],
                'enlace_youtube' => trim($data['enlace_youtube'] ?? ''),
            ],
            ['id' => (int) $params['id']]
        );

        Response::success(Recurso::find((int) $params['id']));
    }

    // DELETE /api/recursos/{id}  — soft delete: mueve a papelera
    public function destroy(array $params): void {
        Middleware::apiAuth();

        $id = (int) $params['id'];
        if (!Recurso::find($id)) {
            Response::notFound('Recurso no encontrado');
        }

        $this->dao->moverAPapelera($id);
        Response::success(['id' => $id, 'mensaje' => 'Recurso movido a papelera']);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function parseBody(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $_POST;
        }
        parse_str(file_get_contents('php://input'), $data);
        return $data;
    }
}

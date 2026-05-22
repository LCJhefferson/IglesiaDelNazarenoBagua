<?php
namespace aplicacion\controladores\api;

use aplicacion\core\Middleware;
use aplicacion\core\QueryBuilder;
use aplicacion\core\Response;
use aplicacion\core\Validator;
use aplicacion\modelos\Recurso;

class RecursoApiController {

    // ── GET /api/recursos ─────────────────────────────────────────────────────
    // Soporta: ?q=texto  ?tipo=pdf  ?tipos=pdf,vid  ?categoria=predica
    //          ?page=1   ?per_page=15
    public function index(array $params = []): void {
        Middleware::apiAuth();

        $qb = (new QueryBuilder())
            ->table('recursos')
            ->leftJoin('usuarios', 'recursos.creado_por', '=', 'usuarios.id')
            ->select('recursos.*, usuarios.username AS creado_por_nombre');

        // Búsqueda libre en título O descripción
        if (!empty($_GET['q'])) {
            $q = '%' . trim($_GET['q']) . '%';
            $qb->where('recursos.titulo', 'LIKE', $q)
               ->orWhere('recursos.descripcion', 'LIKE', $q);
        }

        // Filtro por tipo (uno o varios: ?tipos=pdf,vid)
        if (!empty($_GET['tipos'])) {
            $qb->whereIn('recursos.tipo', explode(',', $_GET['tipos']));
        } elseif (!empty($_GET['tipo'])) {
            $qb->where('recursos.tipo', '=', $_GET['tipo']);
        }

        // Filtro por categoría
        if (!empty($_GET['categoria'])) {
            $qb->where('recursos.categoria', '=', trim($_GET['categoria']));
        }

        // Paginación
        if (isset($_GET['page'])) {
            $page    = max(1, (int) $_GET['page']);
            $perPage = max(1, min(100, (int) ($_GET['per_page'] ?? 15)));
            Response::success($qb->paginate($page, $perPage));
        }

        Response::success($qb->get());
    }

    // ── GET /api/recursos/{id} ────────────────────────────────────────────────
    // Incluye join con usuarios para mostrar quién lo creó
    public function show(array $params): void {
        Middleware::apiAuth();

        $recurso = (new QueryBuilder())
            ->table('recursos')
            ->leftJoin('usuarios', 'recursos.creado_por', '=', 'usuarios.id')
            ->select('recursos.*, usuarios.username AS creado_por_nombre')
            ->where('recursos.id', '=', (int) $params['id'])
            ->first();

        if (!$recurso) {
            Response::notFound('Recurso no encontrado');
        }

        Response::success($recurso);
    }

    // ── GET /api/recursos/stats ───────────────────────────────────────────────
    // Total de descargas + conteo por tipo + conteo por categoría
    public function stats(array $params = []): void {
        Middleware::apiAuth();

        $totalDescargas = Recurso::sum('descargas');
        $totalRecursos  = Recurso::count();

        $porTipo = (new QueryBuilder())
            ->table('recursos')
            ->select('tipo, COUNT(*) AS total')
            ->groupBy('tipo')
            ->get();

        $porCategoria = (new QueryBuilder())
            ->table('recursos')
            ->select('categoria, COUNT(*) AS total')
            ->groupBy('categoria')
            ->get();

        Response::success([
            'total_recursos'   => $totalRecursos,
            'total_descargas'  => $totalDescargas,
            'por_tipo'         => $porTipo,
            'por_categoria'    => $porCategoria,
        ]);
    }

    // ── POST /api/recursos ────────────────────────────────────────────────────
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

    // ── PUT /api/recursos/{id} ────────────────────────────────────────────────
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

    // ── DELETE /api/recursos/{id}  (soft-delete → papelera) ──────────────────
    public function destroy(array $params): void {
        Middleware::apiAuth();

        $id = (int) $params['id'];
        if (!Recurso::find($id)) {
            Response::notFound('Recurso no encontrado');
        }

        Recurso::moverAPapelera($id, $_SESSION['usuario_id'] ?? null);
        Response::success(['id' => $id, 'mensaje' => 'Recurso movido a papelera']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

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

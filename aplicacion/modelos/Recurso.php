<?php
namespace aplicacion\modelos;

use aplicacion\core\Model;
use aplicacion\config\Conexion;

/**
 * Modelo Recurso — tabla "recursos" (activos).
 * Implementa Active Record vía la clase abstracta Model.
 * La eliminación usa el patrón Archive Table: mueve a recursos_papelera
 * en lugar de soft-delete con flag.
 */
class Recurso extends Model {

    protected static string $tabla = 'recursos';

    public const TIPOS_VALIDOS      = ['pdf', 'img', 'vid', 'doc', 'yt'];
    public const CATEGORIAS_VALIDAS = ['predica', 'estudio', 'musica', 'devocional', 'evento'];

    /** Lista todos los recursos activos ordenados por fecha descendente. */
    public static function listar(): array {
        return static::qb()
                     ->orderBy('fecha_creacion', 'DESC')
                     ->get();
    }

    /** Recursos por categoría con paginación. */
    public static function porCategoria(string $categoria, int $pagina = 1): array {
        return static::where('categoria', '=', $categoria)
                     ->orderBy('fecha_creacion', 'DESC')
                     ->paginate($pagina, 12);
    }

    /** Cuenta recursos por tipo (útil para dashboards). */
    public static function contarPorTipo(string $tipo): int {
        return static::where('tipo', '=', $tipo)->count();
    }

    /** Total de descargas acumuladas en todo el sistema. */
    public static function totalDescargas(): int {
        return (int) static::qb()->sum('descargas');
    }

    /** Incrementa el contador de descargas de un recurso. */
    public static function incrementarDescargas(int $id): bool {
        $recurso = static::find($id);
        if (!$recurso) return false;
        return static::update(
            ['descargas' => ($recurso['descargas'] ?? 0) + 1],
            ['id' => $id]
        );
    }

    /**
     * Mueve un recurso a papelera dentro de una transacción.
     * Patrón Archive Table: copia a recursos_papelera y elimina de recursos.
     */
    public static function moverAPapelera(int $id, ?int $usuarioId = null): bool {
        $recurso = static::find($id);
        if (!$recurso) return false;

        $pdo = Conexion::conectar();

        try {
            $pdo->beginTransaction();

            RecursoPapelera::create([
                'recurso_id'        => $recurso['id'],
                'titulo'            => $recurso['titulo'],
                'descripcion'       => $recurso['descripcion'],
                'categoria'         => $recurso['categoria'],
                'tipo'              => $recurso['tipo'],
                'ruta_archivo'      => $recurso['ruta_archivo'],
                'enlace_youtube'    => $recurso['enlace_youtube'],
                'ruta_thumb'        => $recurso['ruta_thumb'] ?? null,
                'eliminado_por'     => $usuarioId,
                'fecha_eliminacion' => date('Y-m-d H:i:s'),
            ]);

            static::delete(['id' => $id]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Error moviendo recurso a papelera: ' . $e->getMessage());
            return false;
        }
    }
}

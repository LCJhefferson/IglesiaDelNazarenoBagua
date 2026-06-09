<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Recurso extends Model {

    // Configuración básica
    protected $table = 'recursos';
    protected $primaryKey = 'id';
    public $timestamps = false; // Cambiar a true si tu tabla tiene created_at y updated_at

    protected $fillable = [
        'titulo', 
        'descripcion', 
        'categoria', 
        'tipo', 
        'ruta_archivo', 
        'enlace_youtube', 
        'ruta_thumb', 
        'descargas'
    ];

    // Constantes de validación (se mantienen igual)
    public const TIPOS_VALIDOS      = ['pdf', 'img', 'vid', 'doc', 'yt'];
    public const CATEGORIAS_VALIDAS = ['predica', 'estudio', 'musica', 'devocional', 'evento'];

    /** Lista todos los recursos ordenados por fecha descendente */
    public static function listar() {
        return self::orderBy('fecha_creacion', 'DESC')->get();
    }

    /** Recursos por categoría con paginación */
    public static function porCategoria(string $categoria, int $pagina = 1) {
        return self::where('categoria', $categoria)
                   ->orderBy('fecha_creacion', 'DESC')
                   ->paginate(12, ['*'], 'page', $pagina);
    }

    /** Cuenta recursos por tipo */
    public static function contarPorTipo(string $tipo): int {
        return self::where('tipo', $tipo)->count();
    }

    /** Total de descargas acumuladas */
    public static function totalDescargas(): int {
        return (int) self::sum('descargas');
    }

    /** Incrementa el contador de descargas */
    public static function incrementarDescargas(int $id): bool {
        $recurso = self::find($id);
        if (!$recurso) return false;
        
        $recurso->descargas = $recurso->descargas + 1;
        return $recurso->save();
    }

    /**
     * Mueve un recurso a papelera usando transacciones de Eloquent.
     * Patrón Archive Table.
     */
    public static function moverAPapelera(int $id, ?int $usuarioId = null): bool {
        return DB::transaction(function () use ($id, $usuarioId) {
            $recurso = self::find($id);
            if (!$recurso) return false;

            // Insertamos en la tabla de papelera usando Query Builder
            DB::table('recursos_papelera')->insert([
                'recurso_id'        => $recurso->id,
                'titulo'            => $recurso->titulo,
                'descripcion'       => $recurso->descripcion,
                'categoria'         => $recurso->categoria,
                'tipo'              => $recurso->tipo,
                'ruta_archivo'      => $recurso->ruta_archivo,
                'enlace_youtube'    => $recurso->enlace_youtube,
                'ruta_thumb'        => $recurso->ruta_thumb,
                'eliminado_por'     => $usuarioId,
                'fecha_eliminacion' => date('Y-m-d H:i:s'),
            ]);

            // Eliminamos de la tabla principal
            return $recurso->delete();
        });
    }
}
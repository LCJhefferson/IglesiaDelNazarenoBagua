<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Modelo RecursoPapelera — tabla "recursos_papelera".
 * Implementado con Eloquent para consistencia con el sistema.
 */
class RecursoPapelera extends Model {

    protected $table = 'recursos_papelera';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'recurso_id', 'titulo', 'descripcion', 'categoria', 
        'tipo', 'ruta_archivo', 'enlace_youtube', 'ruta_thumb', 
        'eliminado_por', 'fecha_eliminacion'
    ];

    /** Lista todos los recursos en papelera */
    public static function listar() {
        return self::orderBy('fecha_eliminacion', 'DESC')->get();
    }

    /**
     * Restaura un recurso desde la papelera a la tabla activa.
     */
    public static function restaurar(int $papeleraId): bool {
        return DB::transaction(function () use ($papeleraId) {
            $registro = self::find($papeleraId);
            if (!$registro) return false;

            // Creamos el recurso en la tabla principal
            Recurso::create([
                'titulo'         => $registro->titulo,
                'descripcion'    => $registro->descripcion,
                'categoria'      => $registro->categoria,
                'tipo'           => $registro->tipo,
                'ruta_archivo'   => $registro->ruta_archivo,
                'enlace_youtube' => $registro->enlace_youtube,
                'ruta_thumb'     => $registro->ruta_thumb,
                'creado_por'     => $registro->eliminado_por,
                'descargas'      => 0,
            ]);

            // Eliminamos el registro de la papelera
            return $registro->delete();
        });
    }

    /** Elimina definitivamente un registro de la papelera. */
    public static function eliminarDefinitivo(int $papeleraId): bool {
        $registro = self::find($papeleraId);
        return $registro ? $registro->delete() : false;
    }

    /** Vacía toda la papelera. */
    public static function vaciar(): int {
        $cantidad = self::count();
        self::truncate(); // Borra todo de forma eficiente
        return $cantidad;
    }
}
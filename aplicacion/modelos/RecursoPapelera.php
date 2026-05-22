<?php
namespace aplicacion\modelos;

use aplicacion\core\Model;
use aplicacion\config\Conexion;

/**
 * Modelo RecursoPapelera — tabla "recursos_papelera" (archivo de eliminados).
 * Forma parte del patrón Archive Table junto con Recurso.
 */
class RecursoPapelera extends Model {

    protected static string $tabla = 'recursos_papelera';

    /** Lista todos los recursos en papelera ordenados por fecha de eliminación. */
    public static function listar(): array {
        return static::qb()
                     ->orderBy('fecha_eliminacion', 'DESC')
                     ->get();
    }

    /**
     * Restaura un recurso desde la papelera a la tabla activa.
     * Usa transacción para garantizar consistencia.
     */
    public static function restaurar(int $papeleraId): bool {
        $registro = static::find($papeleraId);
        if (!$registro) return false;

        $pdo = Conexion::conectar();

        try {
            $pdo->beginTransaction();

            Recurso::create([
                'titulo'         => $registro['titulo'],
                'descripcion'    => $registro['descripcion'],
                'categoria'      => $registro['categoria'],
                'tipo'           => $registro['tipo'],
                'ruta_archivo'   => $registro['ruta_archivo'],
                'enlace_youtube' => $registro['enlace_youtube'],
                'ruta_thumb'     => $registro['ruta_thumb'] ?? null,
                'creado_por'     => $registro['eliminado_por'] ?? null,
                'descargas'      => 0,
            ]);

            static::delete(['id' => $papeleraId]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Error restaurando recurso: ' . $e->getMessage());
            return false;
        }
    }

    /** Elimina definitivamente un registro de la papelera. */
    public static function eliminarDefinitivo(int $papeleraId): bool {
        return static::delete(['id' => $papeleraId]);
    }

    /** Vacía toda la papelera. Devuelve la cantidad de registros eliminados. */
    public static function vaciar(): int {
        $count = static::count();
        Conexion::conectar()->exec("DELETE FROM recursos_papelera");
        return $count;
    }
}

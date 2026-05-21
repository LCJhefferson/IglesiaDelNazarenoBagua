<?php
namespace aplicacion\core;

abstract class Model {

    /** Nombre de la tabla en la BD — cada subclase lo sobreescribe. */
    protected static string $tabla = '';

    protected static function qb(): QueryBuilder {
        return (new QueryBuilder())->table(static::$tabla);
    }

    /** Devuelve todas las filas de la tabla. */
    public static function all(): array {
        return static::qb()->get();
    }

    /** Devuelve la fila cuyo id coincide, o null si no existe. */
    public static function find(int $id): ?array {
        return static::qb()->where('id', '=', $id)->first();
    }

    /**
     * Devuelve un QueryBuilder con la condición aplicada,
     * listo para encadenar más métodos (.orderBy, .limit, .get…).
     */
    public static function where(string $columna, string $operador, mixed $valor): QueryBuilder {
        return static::qb()->where($columna, $operador, $valor);
    }

    /** Inserta una fila y devuelve el nuevo id. */
    public static function create(array $data): int {
        return (new QueryBuilder())->insert(static::$tabla, $data);
    }

    /** Actualiza filas que cumplan $where. Devuelve true si tuvo éxito. */
    public static function update(array $data, array $where): bool {
        return (new QueryBuilder())->update(static::$tabla, $data, $where);
    }

    /** Elimina filas que cumplan $where. Devuelve true si tuvo éxito. */
    public static function delete(array $where): bool {
        return (new QueryBuilder())->delete(static::$tabla, $where);
    }
}

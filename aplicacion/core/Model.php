<?php
namespace aplicacion\core;

abstract class Model {

    protected static string $tabla = '';

    protected static function qb(): QueryBuilder {
        return (new QueryBuilder())->table(static::$tabla);
    }

    // ── LECTURA ──────────────────────────────────────────────────────────────

    public static function all(): array {
        return static::qb()->get();
    }

    public static function find(int $id): ?array {
        return static::qb()->where('id', '=', $id)->first();
    }

    public static function where(string $columna, string $operador, mixed $valor): QueryBuilder {
        return static::qb()->where($columna, $operador, $valor);
    }

    public static function whereIn(string $columna, array $valores): QueryBuilder {
        return static::qb()->whereIn($columna, $valores);
    }

    public static function join(string $tabla, string $primero, string $operador, string $segundo, string $tipo = 'INNER'): QueryBuilder {
        return static::qb()->join($tabla, $primero, $operador, $segundo, $tipo);
    }

    public static function leftJoin(string $tabla, string $primero, string $operador, string $segundo): QueryBuilder {
        return static::qb()->leftJoin($tabla, $primero, $operador, $segundo);
    }

    // ── AGREGADOS ────────────────────────────────────────────────────────────

    public static function count(string $columna = '*'): int {
        return static::qb()->count($columna);
    }

    public static function sum(string $columna): float {
        return static::qb()->sum($columna);
    }

    public static function paginate(int $page = 1, int $perPage = 15): array {
        return static::qb()->paginate($page, $perPage);
    }

    // ── ESCRITURA ────────────────────────────────────────────────────────────

    public static function create(array $data): int {
        return (new QueryBuilder())->insert(static::$tabla, $data);
    }

    public static function update(array $data, array $where): bool {
        return (new QueryBuilder())->update(static::$tabla, $data, $where);
    }

    public static function delete(array $where): bool {
        return (new QueryBuilder())->delete(static::$tabla, $where);
    }
}

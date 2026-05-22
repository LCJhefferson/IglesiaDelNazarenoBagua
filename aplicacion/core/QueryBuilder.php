<?php
namespace aplicacion\core;

use aplicacion\config\Conexion;
use PDO;

class QueryBuilder {

    private \PDO   $pdo;
    private string $table      = '';
    private string $columns    = '*';
    private array  $conditions = [];   
    private array  $bindings   = [];
    private array  $joins      = [];
    private ?string $orderByClause = null;
    private ?string $groupByClause = null;
    private ?int    $limitVal  = null;
    private ?int    $offsetVal = null;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }
    public function table(string $table): static {
        $this->table = $table;
        return $this;
    }
    public function select(string $columns): static {
        $this->columns = $columns;
        return $this;
    }
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): static {
        $this->joins[] = "$type JOIN $table ON $first $operator $second";
        return $this;
    }
    public function leftJoin(string $table, string $first, string $operator, string $second): static {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }
    public function where(string $column, string $operator, mixed $value): static {
        $placeholder = ':' . preg_replace('/\W/', '_', $column) . count($this->bindings);
        $this->conditions[]           = ['AND', "$column $operator $placeholder"];
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): static {
        $placeholder = ':' . preg_replace('/\W/', '_', $column) . count($this->bindings);
        $this->conditions[]           = ['OR', "$column $operator $placeholder"];
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): static {
        $placeholders = [];
        foreach ($values as $i => $v) {
            $key = ':' . preg_replace('/\W/', '_', $column) . '_in' . $i;
            $placeholders[]      = $key;
            $this->bindings[$key] = $v;
        }
        $this->conditions[] = ['AND', "$column IN (" . implode(', ', $placeholders) . ")"];
        return $this;
    }
    public function orderBy(string $column, string $direction = 'ASC'): static {
        $this->orderByClause = "$column $direction";
        return $this;
    }

    public function groupBy(string $column): static {
        $this->groupByClause = $column;
        return $this;
    }

    public function limit(int $n): static {
        $this->limitVal = $n;
        return $this;
    }

    public function offset(int $n): static {
        $this->offsetVal = $n;
        return $this;
    }

    private function buildWhere(): string {
        if (empty($this->conditions)) return '';
        $parts = [];
        foreach ($this->conditions as $i => [$connector, $clause]) {
            $parts[] = ($i === 0 ? '' : "$connector ") . $clause;
        }
        return ' WHERE ' . implode(' ', $parts);
    }

    private function buildSql(): string {
        $sql = "SELECT {$this->columns} FROM {$this->table}";
        foreach ($this->joins as $join) {
            $sql .= " $join";
        }
        $sql .= $this->buildWhere();
        if ($this->groupByClause !== null) {
            $sql .= " GROUP BY {$this->groupByClause}";
        }
        if ($this->orderByClause !== null) {
            $sql .= " ORDER BY {$this->orderByClause}";
        }
        if ($this->limitVal !== null) {
            $sql .= " LIMIT {$this->limitVal}";
        }
        if ($this->offsetVal !== null) {
            $sql .= " OFFSET {$this->offsetVal}";
        }
        return $sql;
    }



    public function get(): array {
        $stmt = $this->pdo->prepare($this->buildSql());
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }



    public function count(string $column = '*'): int {
        [$savedCols, $savedLimit, $savedOffset] = [$this->columns, $this->limitVal, $this->offsetVal];
        $this->columns  = "COUNT($column) AS _total";
        $this->limitVal = $this->offsetVal = null;
        $stmt = $this->pdo->prepare($this->buildSql());
        $stmt->execute($this->bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        [$this->columns, $this->limitVal, $this->offsetVal] = [$savedCols, $savedLimit, $savedOffset];
        return (int) ($result['_total'] ?? 0);
    }

    public function sum(string $column): float {
        [$savedCols, $savedLimit, $savedOffset] = [$this->columns, $this->limitVal, $this->offsetVal];
        $this->columns  = "COALESCE(SUM($column), 0) AS _total";
        $this->limitVal = $this->offsetVal = null;
        $stmt = $this->pdo->prepare($this->buildSql());
        $stmt->execute($this->bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        [$this->columns, $this->limitVal, $this->offsetVal] = [$savedCols, $savedLimit, $savedOffset];
        return (float) ($result['_total'] ?? 0);
    }

    public function paginate(int $page = 1, int $perPage = 15): array {
        $total = $this->count();
        $this->limit($perPage)->offset(($page - 1) * $perPage);
        return [
            'data'          => $this->get(),
            'total'         => $total,
            'paginas'       => (int) ceil($total / max(1, $perPage)),
            'pagina_actual' => $page,
            'por_pagina'    => $perPage,
        ];
    }


    public function insert(string $table, array $data): int {
        $cols  = implode(', ', array_keys($data));
        $slots = ':' . implode(', :', array_keys($data));
        $stmt  = $this->pdo->prepare("INSERT INTO $table ($cols) VALUES ($slots)");
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, array $where): bool {
        $set    = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $cond   = implode(' AND ', array_map(fn($k) => "$k = :w_$k", array_keys($where)));
        $params = $data;
        foreach ($where as $k => $v) {
            $params["w_$k"] = $v;
        }
        $stmt = $this->pdo->prepare("UPDATE $table SET $set WHERE $cond");
        return $stmt->execute($params);
    }

    public function delete(string $table, array $where): bool {
        $cond = implode(' AND ', array_map(fn($k) => "$k = :$k", array_keys($where)));
        $stmt = $this->pdo->prepare("DELETE FROM $table WHERE $cond");
        return $stmt->execute($where);
    }
}

<?php
namespace aplicacion\core;

use aplicacion\config\Conexion;
use PDO;

class QueryBuilder {

    private \PDO $pdo;
    private string $table  = '';
    private string $columns = '*';
    private array  $conditions = [];
    private array  $bindings   = [];
    private ?string $orderBy   = null;
    private ?int    $limit     = null;

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

    public function where(string $column, string $operator, mixed $value): static {
        $placeholder = ':' . preg_replace('/\W/', '_', $column) . count($this->bindings);
        $this->conditions[]          = "$column $operator $placeholder";
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static {
        $this->orderBy = "$column $direction";
        return $this;
    }

    public function limit(int $n): static {
        $this->limit = $n;
        return $this;
    }

    public function get(): array {
        $sql = "SELECT {$this->columns} FROM {$this->table}";
        if ($this->conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $this->conditions);
        }
        if ($this->orderBy !== null) {
            $sql .= " ORDER BY {$this->orderBy}";
        }
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }

    public function insert(string $table, array $data): int {
        $cols  = implode(', ', array_keys($data));
        $slots = ':' . implode(', :', array_keys($data));
        $stmt  = $this->pdo->prepare("INSERT INTO $table ($cols) VALUES ($slots)");
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, array $where): bool {
        $set  = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $cond = implode(' AND ', array_map(fn($k) => "$k = :w_$k", array_keys($where)));
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

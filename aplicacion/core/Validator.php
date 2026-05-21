<?php
namespace aplicacion\core;

class Validator {

    private array $errors = [];
    private array $data;

    private function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * Crea el validador y aplica las reglas.
     *
     * Reglas soportadas: required | min:N | max:N | email | in:a,b,c
     *
     * Ejemplo:
     *   Validator::make($_POST, [
     *       'titulo'    => 'required|max:200',
     *       'categoria' => 'required|in:audio,video,documento',
     *   ]);
     */
    public static function make(array $data, array $rules): static {
        $v = new static($data);
        foreach ($rules as $field => $ruleString) {
            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $value = $data[$field] ?? null;
                match ($name) {
                    'required' => $v->required($field, $value),
                    'min'      => $v->minLength($field, $value, (int) $param),
                    'max'      => $v->maxLength($field, $value, (int) $param),
                    'email'    => $v->email($field, $value),
                    'in'       => $v->inList($field, $value, explode(',', $param ?? '')),
                    default    => null,
                };
            }
        }
        return $v;
    }

    public function fails(): bool {
        return !empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }

    /** Devuelve solo los campos que pasaron validación. */
    public function validated(): array {
        return array_diff_key($this->data, $this->errors);
    }

    // ── Reglas internas ─────────────────────────────────────────────────────

    private function required(string $field, mixed $value): void {
        if ($value === null || trim((string) $value) === '') {
            $this->errors[$field][] = "El campo «$field» es obligatorio.";
        }
    }

    private function minLength(string $field, mixed $value, int $min): void {
        if ($value !== null && mb_strlen((string) $value) < $min) {
            $this->errors[$field][] = "«$field» debe tener al menos $min caracteres.";
        }
    }

    private function maxLength(string $field, mixed $value, int $max): void {
        if ($value !== null && mb_strlen((string) $value) > $max) {
            $this->errors[$field][] = "«$field» no puede superar $max caracteres.";
        }
    }

    private function email(string $field, mixed $value): void {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "«$field» debe ser un correo electrónico válido.";
        }
    }

    private function inList(string $field, mixed $value, array $options): void {
        if ($value !== null && !in_array($value, $options, true)) {
            $this->errors[$field][] = "«$field» debe ser uno de: " . implode(', ', $options) . '.';
        }
    }
}

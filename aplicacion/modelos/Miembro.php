<?php
namespace modelos;

class Miembro {
    // Propiedades según tu tabla SQL
    public $id;
    public $nombres;
    public $apellidos;
    public $telefono;
    public $direccion;
    public $fecha_nacimiento;
    public $cargo_id;
    public $condicion_id;
    public $latitud;
    public $longitud;

    // Constructor opcional para inicializar datos rápido
    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->nombres = $data['nombres'] ?? null;
        $this->apellidos = $data['apellidos'] ?? null;
        $this->telefono = $data['telefono'] ?? null;
        $this->direccion = $data['direccion'] ?? null;
        $this->fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
        $this->cargo_id = $data['cargo_id'] ?? null;
        $this->condicion_id = $data['condicion_id'] ?? null;
        $this->latitud = $data['latitud'] ?? null;
        $this->longitud = $data['longitud'] ?? null;
    }
}
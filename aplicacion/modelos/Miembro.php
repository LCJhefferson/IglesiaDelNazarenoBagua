<?php
namespace aplicacion\modelos;

class Miembro {
    public $id;
    public $nombres;
    public $apellidos;
    public $telefono;
    public $direccion;
    public $fecha_nacimiento;
    public $condicion_id;
    public $latitud;
    public $longitud;
    public $estado; 
    public $tipo_miembro_id;
    public $cargos; 

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->nombres = $data['nombres'] ?? null;
        $this->apellidos = $data['apellidos'] ?? null;
        $this->telefono = $data['telefono'] ?? null;
        $this->direccion = $data['direccion'] ?? null;
        $this->fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
        $this->condicion_id = $data['condicion_id'] ?? null;
        $this->latitud = $data['latitud'] ?? null;
        $this->longitud = $data['longitud'] ?? null;
        $this->estado = $data['estado'] ?? 1;
        $this->tipo_miembro_id = $data['tipo_miembro_id'] ?? 1;
        $this->cargos = $data['cargos'] ?? []; 
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'condicion_id' => $this->condicion_id,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'estado' => $this->estado,
            'tipo_miembro_id' => $this->tipo_miembro_id
        ];
    }
}
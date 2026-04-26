<?php
namespace modelos;

class GrupoDiscipulado {
    public $id;
    public $nombre;
    public $nivel;
    public $discipulador_id;
    public $fecha_creacion;
    public $estado;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->nombre = $data['nombre'] ?? null;
        $this->nivel = $data['nivel'] ?? null;
        $this->discipulador_id = $data['discipulador_id'] ?? null;
        $this->fecha_creacion = $data['fecha_creacion'] ?? null;
        $this->estado = $data['estado'] ?? 'activo';
    }
}
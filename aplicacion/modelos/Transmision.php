<?php
namespace modelos;

class Transmision {
    public $id;
    public $titulo;
    public $descripcion;
    public $link_video;
    public $fecha;
    public $creado_por;
    public $estado_id;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->titulo = $data['titulo'] ?? null;
        $this->descripcion = $data['descripcion'] ?? null;
        $this->link_video = $data['link_video'] ?? null;
        $this->fecha = $data['fecha'] ?? null;
        $this->creado_por = $data['creado_por'] ?? null;
        $this->estado_id = $data['estado_id'] ?? null;
    }
}
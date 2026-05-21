<?php
namespace aplicacion\modelos;

class Cargo {
    public $id;
    public $nombre;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->nombre = $data['nombre'] ?? null;
    }
}
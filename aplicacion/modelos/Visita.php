<?php
namespace aplicacion\modelos;

class Visita {
    public $id;
    public $miembro_id;
    public $fecha_visita;
    public $motivo;
    public $registrado_por;
    public $estado_id;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->miembro_id = $data['miembro_id'] ?? null;
        $this->fecha_visita = $data['fecha_visita'] ?? null;
        $this->motivo = $data['motivo'] ?? null;
        $this->registrado_por = $data['registrado_por'] ?? null;
        $this->estado_id = $data['estado_id'] ?? null;
    }
}
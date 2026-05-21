<?php
namespace aplicacion\modelos;

class GrupoDiscipulado {
    public $id;
    public $nombre;
    public $nivel;
    public $discipulador_id;
    public $estado_id;

    public function __construct($data = []) {
        $this->id = isset($data['id']) && $data['id'] !== '' ? (int)$data['id'] : null;
        $this->nombre = $data['nombre'] ?? '';
        $this->nivel = $data['nivel'] ?? 'I';
        $this->discipulador_id = isset($data['discipulador_id']) ? (int)$data['discipulador_id'] : null;
        $this->estado_id = isset($data['estado_id']) ? (int)$data['estado_id'] : 1; 
    }

    /**
     * Factory method para crear el modelo desde el formulario POST de forma segura
     */
    public static function desdePost($post) {
        return new self([
            'id'              => $post['id'] ?? null,
            'nombre'          => $post['nombre'] ?? '',
            'nivel'           => $post['nivel'] ?? 'I',
            'discipulador_id' => $post['discipulador_id'] ?? null,
            'estado_id'       => $post['estado_id'] ?? 1
        ]);
    }

    /**
     * Convierte el objeto a un arreglo para que el DAO lo pueda procesar
     */
    public function toArray() {
        return [
            'id'              => $this->id,
            'nombre'          => $this->nombre,
            'nivel'           => $this->nivel,
            'discipulador_id' => $this->discipulador_id,
            'estado_id'       => $this->estado_id
        ];
    }
}
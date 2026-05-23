<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class TransmisionModelo extends Model {
    protected $table = 'transmisiones';
    protected $primaryKey = 'id';
    public $timestamps = false; // Ya que usas 'fecha' manualmente

    protected $fillable = [
        'titulo', 'descripcion', 'link_video', 'estado_id', 'creado_por', 'fecha'
    ];
}
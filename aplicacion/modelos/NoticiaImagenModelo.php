<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class NoticiaImagenModelo extends Model {
    protected $table = 'noticia_imagenes';
    public $timestamps = false;
    protected $fillable = [
        'noticia_id', 
        'imagen' 
    ];
}
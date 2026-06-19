<?php
namespace aplicacion\modelos;
use Illuminate\Database\Eloquent\Model;

class EstadoGrupoDiscipulado extends Model {
    // Cambiamos aquí al nuevo nombre que le diste a la tabla en MySQL:
    protected $table = 'estados_grupo_discipulado'; 
    public $timestamps = false;
}
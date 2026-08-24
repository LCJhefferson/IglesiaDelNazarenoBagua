<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model {
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['username', 'password', 'id_rol', 'estado'];
    public function rol() {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
}
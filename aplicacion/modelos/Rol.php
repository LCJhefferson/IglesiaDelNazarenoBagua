<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model {
    protected $table = 'roles';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    /**
     * RELACIÓN: Un rol puede pertenecer a muchos usuarios
     */
    public function usuarios() {
        return $this->hasMany(UserLogin::class, 'rol_id');
    }
}
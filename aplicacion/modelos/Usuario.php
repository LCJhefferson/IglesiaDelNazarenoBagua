<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model {
    protected $table = 'usuarios'; // Tu tabla física
    protected $primaryKey = 'id';
    public $timestamps = false; // Solo pon true si tienes created_at y updated_at

    protected $fillable = ['username', 'password', 'id_rol', 'estado'];

    // Relación con Roles (Opcional, pero muy útil)
    public function rol() {
        // Asumiendo que la tabla roles tiene 'id' y 'nombre'
        return $this->belongsTo(Rol::class, 'id_rol');
    }
}
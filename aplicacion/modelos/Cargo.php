<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model {
    // Eloquent asume que la tabla es "cargos", si es diferente, cámbiala aquí:
    protected $table = 'cargos'; 
    
    // Si tu llave primaria no es "id", especifícala:
    protected $primaryKey = 'id';

    // Desactivamos timestamps si tu tabla no tiene created_at y updated_at
    public $timestamps = false;

    // Campos que permitimos llenar masivamente
    protected $fillable = ['nombre'];

    /**
     * RELACIÓN: Un cargo puede tener muchos miembros
     */
    public function miembros() {
        return $this->hasMany(Miembro::class, 'cargo_id');
    }
}
<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class Miembro extends Model {
    protected $table = 'miembros';
    public $timestamps = false; // Tu tabla no usa created_at/updated_at

    protected $fillable = [
        'nombres', 'apellidos', 'telefono', 'direccion', 
        'fecha_nacimiento', 'condicion_id', 'latitud', 
        'longitud', 'estado', 'tipo_miembro_id'
    ];

    // Relación Muchos a Muchos con Cargos
    public function cargos() {
        return $this->belongsToMany(Cargo::class, 'miembro_cargos', 'miembro_id', 'cargo_id');
    }
    public function condicion() {
    return $this->belongsTo(CondicionMiembro::class, 'condicion_id');
}

    public function tipo() {
        return $this->belongsTo(TipoMiembro::class, 'tipo_miembro_id');
    }
    // Relación Uno a Muchos: Un miembro tiene muchas visitas
    public function visitas() {
        return $this->hasMany(VisitaModelo::class, 'miembro_id', 'id');
    }
}
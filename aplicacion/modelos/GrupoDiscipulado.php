<?php
namespace aplicacion\modelos;
use Illuminate\Database\Eloquent\Model;

class GrupoDiscipulado extends Model {
    protected $table = 'discipulado_grupos';
    protected $fillable = ['nombre', 'nivel', 'discipulador_id', 'estado_id'];
    public $timestamps = false;

    // Relación con el líder (Miembro)
    public function discipulador() {
        return $this->belongsTo(Miembro::class, 'discipulador_id');
    }

    // Relación con el estado
    public function estado() {
        return $this->belongsTo(EstadoDiscipulado::class, 'estado_id');
    }

    // Relación con los integrantes asignados
    public function integrantes() {
        return $this->hasMany(IntegranteDiscipulado::class, 'grupo_id');
    }
}
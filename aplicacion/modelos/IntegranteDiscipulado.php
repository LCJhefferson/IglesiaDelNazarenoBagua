<?php
namespace aplicacion\modelos;
use Illuminate\Database\Eloquent\Model;

class IntegranteDiscipulado extends Model {
    protected $table = 'discipulado_integrantes';
    protected $fillable = ['miembro_id', 'grupo_id'];
    public $timestamps = false;

    public function miembro() {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }

    public function grupo() {
        return $this->belongsTo(GrupoDiscipulado::class, 'grupo_id');
    }
}
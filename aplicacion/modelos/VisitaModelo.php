<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class VisitaModelo extends Model {
    protected $table = 'visitas';
    protected $primaryKey = 'id'; // Asegúrate de que Eloquent sepa cuál es la llave primaria
    public $timestamps = false; 

    protected $fillable = [
        'miembro_id', 'fecha_visita', 'motivo', 'registrado_por', 'estado_id', 'estado'
    ];

    public function miembro() {
        return $this->belongsTo(Miembro::class, 'miembro_id', 'id');
    }
}
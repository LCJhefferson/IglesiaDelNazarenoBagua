<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class TipoMiembro extends Model
{
    // Indicar el nombre exacto de la tabla en tu base de datos
    protected $table = 'tipos_miembro'; 

    // Si tu tabla no tiene las columnas created_at y updated_at, pon esto en false
    public $timestamps = false;

    protected $fillable = ['nombre'];

    /**
     * Relación inversa: Un tipo tiene muchos miembros
     */
    public function miembros()
    {
        return $this->hasMany(Miembro::class, 'tipo_miembro_id');
    }
}
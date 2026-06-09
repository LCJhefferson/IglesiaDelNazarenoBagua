<?php
namespace aplicacion\modelos;

use Illuminate\Database\Eloquent\Model;

class NoticiaModelo extends Model {
    protected $table = 'noticias';
    public $timestamps = false; // Desactivamos porque manejas 'fecha_creacion' manualmente

    protected $fillable = [
        'titulo', 
        'resumen', 
        'contenido', 
        'imagen_portada', 
        'video_link', 
        'fecha_creacion',
        'estado'
    ];

    // Relación con la galería de imágenes
    public function imagenesAdjuntas() {
        return $this->hasMany(NoticiaImagenModelo::class, 'noticia_id', 'id');
    }
}
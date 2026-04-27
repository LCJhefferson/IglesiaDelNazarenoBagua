<?php
namespace aplicacion\modelos;

class Recurso {
    private $id;
    private $titulo;
    private $descripcion;
    private $categoria;
    private $tipo;
    private $ruta_archivo;
    private $enlace_youtube;
    private $descargas;
    private $creado_por;

    public function __construct(
        $titulo, $descripcion, $categoria, $tipo,
        $ruta_archivo, $enlace_youtube, $creado_por,
        $id = null, $descargas = 0
    ) {
        $this->id             = $id;
        $this->titulo         = $titulo;
        $this->descripcion    = $descripcion;
        $this->categoria      = $categoria;
        $this->tipo           = $tipo;
        $this->ruta_archivo   = $ruta_archivo;
        $this->enlace_youtube = $enlace_youtube;
        $this->creado_por     = $creado_por;
        $this->descargas      = $descargas;
    }

    // ── GETTERS ──
    public function getId()           { return $this->id; }
    public function getTitulo()       { return $this->titulo; }
    public function getDescripcion()  { return $this->descripcion; }
    public function getCategoria()    { return $this->categoria; }
    public function getTipo()         { return $this->tipo; }
    public function getRutaArchivo()  { return $this->ruta_archivo; }
    public function getEnlaceYoutube(){ return $this->enlace_youtube; }
    public function getDescargas()    { return $this->descargas; }
    public function getCreadoPor()    { return $this->creado_por; }

    // ── SETTERS ──
    public function setTitulo($titulo)             { $this->titulo         = $titulo; }
    public function setDescripcion($descripcion)   { $this->descripcion    = $descripcion; }
    public function setCategoria($categoria)       { $this->categoria      = $categoria; }
    public function setTipo($tipo)                 { $this->tipo           = $tipo; }
    public function setRutaArchivo($ruta)          { $this->ruta_archivo   = $ruta; }
    public function setEnlaceYoutube($enlace)      { $this->enlace_youtube = $enlace; }
}
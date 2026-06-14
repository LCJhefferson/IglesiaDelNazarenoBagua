<?php
namespace aplicacion\modelos;

// Usamos Capsule que mapea tu conexión global de database.php
use Illuminate\Database\Capsule\Manager as Capsule;

class ReporteModel {

    /**
     * Llena el selector de condiciones de manera automática desde la BD
     */
    public static function obtenerCondiciones() {
        return Capsule::table('condiciones_miembro')
            ->select('id', 'nombre')
            ->orderBy('nombre', 'ASC')
            ->get()
            ->toArray();
    }

    /**
     * Búsqueda en tiempo real para el Autocomplete de Miembros
     */
    public static function buscarMiembros($termino) {
        return Capsule::table('miembros')
            ->where('nombres', 'LIKE', "%{$termino}%")
            ->orWhere('apellidos', 'LIKE', "%{$termino}%")
            ->select('id', Capsule::raw("CONCAT(nombres, ' ', apellidos) as nombre_completo"))
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Búsqueda en tiempo real para el Autocomplete de Grupos de Discipulado
     */
    public static function buscarGrupos($termino) {
        return Capsule::table('discipulado_grupos')
            ->where('nombre', 'LIKE', "%{$termino}%")
            ->select('id', 'nombre')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Búsqueda en tiempo real para el Autocomplete de Discipuladores
     * Vincula la tabla usuarios con datos_usuario para obtener los nombres reales
     */
    public static function buscarDiscipuladores($termino) {
        return Capsule::table('usuarios')
            ->join('datos_usuario', 'usuarios.id', '=', 'datos_usuario.usuario_id')
            ->where('datos_usuario.nombres', 'LIKE', "%{$termino}%")
            ->orWhere('datos_usuario.apellidos', 'LIKE', "%{$termino}%")
            ->select('usuarios.id', Capsule::raw("CONCAT(datos_usuario.nombres, ' ', datos_usuario.apellidos) as nombre_completo"))
            ->limit(10)
            ->get()
            ->toArray();
    }
}
<?php
namespace aplicacion\config;

class Conexion {

    private static $host    = "localhost";
    private static $user    = "root";
    private static $pass    = "";
    private static $db_name = "iglesiadelnazareno";

    // Guarda la única instancia
    private static $instancia = null;

    public static function conectar() {
        if (self::$instancia === null) {
            try {
                self::$instancia = new \PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$user,
                    self::$pass
                );
                self::$instancia->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                self::$instancia->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$instancia;
    }
}
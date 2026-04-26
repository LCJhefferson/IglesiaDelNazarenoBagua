<?php
namespace aplicacion\dao;

use aplicacion\modelos\userLogin;
use aplicacion\config\Conexion;

class UserDAO {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // ── REGISTRAR ──
    public function registrar(userLogin $userLogin): bool {
        $sql  = "INSERT INTO usuarios (username, password, id_rol, estado)
                 VALUES (:username, :password, :id_rol, :estado)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':username' => $userLogin->getUsername(),
            ':password' => $userLogin->getPassword(),
            ':id_rol'   => $userLogin->getIdRol(),
            ':estado'   => $userLogin->getEstado(),
        ]);
    }

    // ── BUSCAR PARA LOGIN (username O correo) ──
    public function buscarParaLogin(string $valor): ?array {
        $sql  = "SELECT u.id, u.username, u.password, u.id_rol, u.estado,
                        r.nombre AS rol_nombre
                 FROM usuarios u
                 INNER JOIN roles r ON u.id_rol = r.id
                 WHERE u.username = :valor
                 AND u.estado = 'activo'
                 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':valor' => $valor]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function listar(): array {
    $sql  = "SELECT u.id, u.username, u.estado, u.id_rol,
                    r.nombre AS rol_nombre
             FROM usuarios u
             INNER JOIN roles r ON u.id_rol = r.id
             ORDER BY u.id DESC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}
    
}
<?php
namespace aplicacion\dao;

use aplicacion\config\Conexion;

class RecursoDAO {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // ── LISTAR activos ──
    public function listar(): array {
        $sql  = "SELECT r.*, u.username AS creado_por_nombre
                 FROM recursos r
                 LEFT JOIN usuarios u ON r.creado_por = u.id
                 ORDER BY r.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── INSERTAR ──
    public function insertar(array $datos): bool {
        $sql  = "INSERT INTO recursos 
                    (titulo, descripcion, categoria, tipo, ruta_archivo, enlace_youtube, creado_por)
                 VALUES 
                    (:titulo, :descripcion, :categoria, :tipo, :ruta_archivo, :enlace_youtube, :creado_por)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titulo'         => $datos['titulo'],
            ':descripcion'    => $datos['descripcion'],
            ':categoria'      => $datos['categoria'],
            ':tipo'           => $datos['tipo'],
            ':ruta_archivo'   => $datos['ruta_archivo'],
            ':enlace_youtube' => $datos['enlace_youtube'],
            ':creado_por'     => $datos['creado_por'],
        ]);
    }

    // ── ACTUALIZAR ──
    public function actualizar(array $datos): bool {
        $sql  = "UPDATE recursos SET
                    titulo         = :titulo,
                    descripcion    = :descripcion,
                    categoria      = :categoria,
                    tipo           = :tipo,
                    ruta_archivo   = :ruta_archivo,
                    enlace_youtube = :enlace_youtube
                 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titulo'         => $datos['titulo'],
            ':descripcion'    => $datos['descripcion'],
            ':categoria'      => $datos['categoria'],
            ':tipo'           => $datos['tipo'],
            ':ruta_archivo'   => $datos['ruta_archivo'],
            ':enlace_youtube' => $datos['enlace_youtube'],
            ':id'             => $datos['id'],
        ]);
    }

    // ── MOVER A PAPELERA ──
    public function moverAPapelera(int $id): bool {
        // 1 — Obtener el recurso
        $sql  = "SELECT * FROM recursos WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $recurso = $stmt->fetch();

        if (!$recurso) return false;

        // 2 — Insertar en papelera
        $sql2 = "INSERT INTO recursos_papelera 
                    (recurso_id, titulo, descripcion, categoria, tipo, ruta_archivo, enlace_youtube, eliminado_por)
                 VALUES 
                    (:recurso_id, :titulo, :descripcion, :categoria, :tipo, :ruta_archivo, :enlace_youtube, :eliminado_por)";
        $stmt2 = $this->pdo->prepare($sql2);
        $stmt2->execute([
            ':recurso_id'    => $recurso['id'],
            ':titulo'        => $recurso['titulo'],
            ':descripcion'   => $recurso['descripcion'],
            ':categoria'     => $recurso['categoria'],
            ':tipo'          => $recurso['tipo'],
            ':ruta_archivo'  => $recurso['ruta_archivo'],
            ':enlace_youtube'=> $recurso['enlace_youtube'],
            ':eliminado_por' => $_SESSION['usuario_id'] ?? null,
        ]);

        // 3 — Eliminar de recursos
        $sql3 = "DELETE FROM recursos WHERE id = :id";
        $stmt3 = $this->pdo->prepare($sql3);
        return $stmt3->execute([':id' => $id]);
    }

    // ── LISTAR PAPELERA ──
    public function listarPapelera(): array {
        $sql  = "SELECT * FROM recursos_papelera ORDER BY fecha_eliminacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── RESTAURAR DE PAPELERA ──
    public function restaurar(int $id): bool {
        $sql  = "SELECT * FROM recursos_papelera WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $recurso = $stmt->fetch();

        if (!$recurso) return false;

        $sql2 = "INSERT INTO recursos 
                    (titulo, descripcion, categoria, tipo, ruta_archivo, enlace_youtube, creado_por)
                 VALUES 
                    (:titulo, :descripcion, :categoria, :tipo, :ruta_archivo, :enlace_youtube, :creado_por)";
        $stmt2 = $this->pdo->prepare($sql2);
        $stmt2->execute([
            ':titulo'         => $recurso['titulo'],
            ':descripcion'    => $recurso['descripcion'],
            ':categoria'      => $recurso['categoria'],
            ':tipo'           => $recurso['tipo'],
            ':ruta_archivo'   => $recurso['ruta_archivo'],
            ':enlace_youtube' => $recurso['enlace_youtube'],
            ':creado_por'     => $recurso['eliminado_por'],
        ]);

        $sql3 = "DELETE FROM recursos_papelera WHERE id = :id";
        $stmt3 = $this->pdo->prepare($sql3);
        return $stmt3->execute([':id' => $id]);
    }

    // ── ELIMINAR DEFINITIVO DE PAPELERA ──
    public function eliminarDefinitivo(int $id): bool {
        $sql  = "DELETE FROM recursos_papelera WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // ── INCREMENTAR DESCARGAS ──
    public function incrementarDescargas(int $id): bool {
        $sql  = "UPDATE recursos SET descargas = descargas + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // ── OBTENER POR ID ──
    public function obtenerPorId(int $id): ?array {
        $sql  = "SELECT * FROM recursos WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

}
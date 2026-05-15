<?php
// 1. Incluir el autoload para la conexión y clases
require_once __DIR__ . '/../../../../aplicacion/core/Autoload.php';
use aplicacion\config\Conexion;

// 2. Establecer cabecera JSON
header('Content-Type: application/json');

try {
    $db = Conexion::conectar();

    /**
     * REGLA: Buscamos la transmisión activa con estado_id = 1 (En Vivo)
     * Traemos la más reciente por ID en caso de que existan duplicados por error.
     */
    $sql = "SELECT id, titulo, descripcion, link_video, estado_id 
            FROM transmisiones 
            WHERE estado_id = 1 
            ORDER BY id DESC LIMIT 1";
            
    $stmt = $db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Devolvemos el objeto con estado_id 1
        echo json_encode([
            "is_live" => true,
            "id" => $row['id'],
            "titulo" => $row['titulo'],
            "descripcion" => $row['descripcion'],
            "link_video" => $row['link_video'], // El controlador ya debe guardarla como embed
            "estado_id" => $row['estado_id']
        ]);
    } else {
        /**
         * Si no hay nada en vivo, opcionalmente buscamos si la última fue finalizada (estado 2)
         * para mostrar el mensaje de "Fin de transmisión" en lugar de "Sin señal".
         */
        $sqlUltima = "SELECT estado_id FROM transmisiones ORDER BY id DESC LIMIT 1";
        $stmtUltima = $db->query($sqlUltima);
        $ultima = $stmtUltima->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "is_live" => false,
            "estado_id" => $ultima['estado_id'] ?? 2 // Por defecto finalizado si no hay registros
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "is_live" => false, 
        "error" => "Error de base de datos"
    ]);
}
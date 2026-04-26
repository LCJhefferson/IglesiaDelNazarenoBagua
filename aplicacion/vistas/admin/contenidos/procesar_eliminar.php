<?php
// 1. Habilitar errores para ver qué falla si se queda en blanco
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../../../../aplicacion/core/Autoload.php"; 

use dao\NoticiaDAO;

if(isset($_GET['id'])){
    try {
        $dao = new NoticiaDAO();
        $id = intval($_GET['id']); // Aseguramos que sea un número
        
        if($dao->eliminar($id)) {
            // Si funciona, regresamos al panel
            header("Location: ../dashboard.php?vista=noticias&status=success");
            exit();
        } else {
            echo "Error: No se pudo actualizar el estado en la base de datos.";
        }
    } catch (Exception $e) {
        echo "Error técnico: " . $e->getMessage();
    }
} else {
    echo "ID no recibido.";
}
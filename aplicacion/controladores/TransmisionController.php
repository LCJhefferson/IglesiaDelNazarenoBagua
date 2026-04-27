<?php
namespace aplicacion\controladores;
use aplicacion\dao\TransmisionDAO;

class TransmisionController {
    private $dao;

    public function __construct() {
        $this->dao = new TransmisionDAO();
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar'])) {
            $datos = [
                'titulo'      => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'],
                'link_video'  => $_POST['link_video'],
                'estado_id'   => $_POST['estado_id'],
                'creado_por'  => $_SESSION['usuario_id'] ?? 1 
            ];
            
            if ($this->dao->guardar($datos)) {
                echo "<script>alert('Transmisión guardada'); window.location.href='dashboard.php?view=transmision';</script>";
            }
        }
    }

    public function listarTransmisiones() {
        return $this->dao->listar();
    }
}
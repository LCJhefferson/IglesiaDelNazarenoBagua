<?php
namespace aplicacion\controladores;

use aplicacion\Repository\MiembroRepository;
use aplicacion\modelos\Miembro;

class MiembroController {
    private $dao;

    public function __construct() {
        $this->dao = new MiembroRepository();
    }

    public function manejarPeticion() {
        $urlDestino = "index.php?vista=dashboard&seccion=membresia";

        
        if (isset($_POST['registrar'])) {
            $cargos = $_POST['cargos'] ?? [];
        
            unset($_POST['registrar'], $_POST['cargos'], $_POST['id']); 
            $this->dao->registrar($_POST, $cargos);
            $this->redireccionar($urlDestino);
        }

        
        if (isset($_POST['editar'])) {
            $cargos = $_POST['cargos'] ?? [];
            unset($_POST['editar'], $_POST['cargos']);
            $this->dao->actualizarConCargos($_POST, $cargos);
            $this->redireccionar($urlDestino);
        }

       
        if (isset($_GET['eliminar'])) {
            \aplicacion\core\Middleware::csrfVerify();
            $this->dao->eliminar($_GET['eliminar']);
            $this->redireccionar($urlDestino);
        }

        // 4. Acción: Activar
        if (isset($_GET['activar'])) {
            \aplicacion\core\Middleware::csrfVerify();
            $this->dao->activar($_GET['activar']);
            $this->redireccionar($urlDestino);
        }
    }

    /**
     * Redirección híbrida y ULTRA-SEGURA
     * Protegida contra XSS (Inyección de código) y Redirecciones Abiertas (Phishing)
     */
    private function redireccionar(string $url): void {
        // 1. Mitigación contra Redirecciones Abiertas
        if (preg_match('/^https?:\/\//i', $url)) {
            $hostPermitido = $_SERVER['HTTP_HOST'];
            $hostDestino = parse_url($url, PHP_URL_HOST);
            
            if ($hostDestino !== $hostPermitido) {
                $url = "index.php?vista=dashboard&seccion=membresia";
            }
        }

        
        if (!headers_sent()) {
            header("Location: " . $url);
            exit;
        } else {
           
            $urlSeguraJs = json_encode($url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            
            echo "<script>window.location.href = " . $urlSeguraJs . ";</script>";
            exit;
        }
    }

    public function listarMiembros() {
        return $this->dao->listar();
    }

    public function obtenerCargos() {
        return $this->dao->listarCargos();
    }

    public function obtenerCondiciones() {
        return $this->dao->listarCondiciones();
    }

    public function obtenerTipos() {
        return $this->dao->listarTipos(); 
    }
}
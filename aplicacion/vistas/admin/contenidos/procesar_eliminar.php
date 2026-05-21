<?php
use aplicacion\dao\NoticiaDAO;

if (isset($_GET['id'])) {
    try {
        $dao = new NoticiaDAO();
        $id  = (int) $_GET['id'];

        if ($dao->eliminar($id)) {
            header("Location: /IglesiaDelNazarenoBagua/dashboard?seccion=noticias&status=success");
            exit;
        } else {
            header("Location: /IglesiaDelNazarenoBagua/dashboard?seccion=noticias&status=error");
            exit;
        }
    } catch (\Exception $e) {
        error_log('procesar_eliminar: ' . $e->getMessage());
        header("Location: /IglesiaDelNazarenoBagua/dashboard?seccion=noticias&status=error");
        exit;
    }
} else {
    header("Location: /IglesiaDelNazarenoBagua/dashboard?seccion=noticias");
    exit;
}

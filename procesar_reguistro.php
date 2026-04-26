<?php

require_once __DIR__ . '/aplicacion/core/Autoload.php';
use aplicacion\controladores\RegistroController;

$controller = new RegistroController();

$resultado = $controller->registrar(
    $_POST['username'],
    $_POST['password'],
    $_POST['rol'],
    $_POST['estado']
);

if ($resultado) {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/contenidos/usuarios_admin.php?exito=1");
} else {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/contenidos/usuarios_admin.php?error=1");
}
exit;
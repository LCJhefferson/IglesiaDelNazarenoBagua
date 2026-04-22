<?php
session_start();

// destruir sesión
session_destroy();

// redirigir al login
header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php");
exit;

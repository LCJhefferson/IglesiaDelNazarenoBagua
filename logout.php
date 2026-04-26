<?php
session_start();
session_unset();
session_destroy();
header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php");
exit;
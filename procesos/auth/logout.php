<?php
session_start();
session_unset();
session_destroy();

// Redirigir siempre al index.php de la carpeta public
header("Location: /IglesiaDelNazarenoBagua/public/index.php?vista=login");
exit;
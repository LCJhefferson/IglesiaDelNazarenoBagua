<?php
// Ajustamos la ruta del autoload porque ahora estamos un nivel más profundo
require_once __DIR__ . '/../../../core/Autoload.php';

// Aquí llamarías a tu controlador para traer los datos de la BD
// $datos = $transmisionController->obtenerTransmisionActiva();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Transmisión en Vivo - Iglesia</title>
    <link rel="stylesheet" href="/IglesiaDelNazarenoBagua/public/css/nav.css">
    <link rel="stylesheet" href="/IglesiaDelNazarenoBagua/public/css/footer.css">
    <link rel="stylesheet" href="/IglesiaDelNazarenoBagua/public/css/trasmisionPublica.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <?php include '../componentes/nav.php'; ?>

    <main class="contenedor-transmision">
        <div class="header-vivo">
            <h1><i class="fa-solid fa-video"></i> Transmisión Oficial</h1>
            <div class="badge-vivo">EN VIVO</div>
        </div>

        <div class="video-frame">
            <iframe 
                src="https://www.youtube.com/embed/5qap5aO4i9A" 
                allowfullscreen>
            </iframe>
        </div>

        <div class="video-detalle">
            <h2>Servicio Dominical</h2>
            <p>Bienvenidos a nuestra transmisión. Si el video no carga, refresca la página.</p>
        </div>
    </main>

    <?php include '../componentes/footer.php'; ?>
</body>
</html>
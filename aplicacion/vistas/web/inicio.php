<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iglesia del Nazareno</title>
    <link rel="stylesheet" href="<?= URL ?>public/web/css/inicio.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php 
// IMPORTANTE: Aquí usamos la ruta del disco duro para el include
include __DIR__ . '/componentes/nav.php'; 
?>
<section class="hero-slider">
    <div class="slide active">
        <img src="<?= URL ?>public/web/imagenes/1.png" class="slide-img">
        <div class="overlay"></div>
        <div class="slide-content">
            <h1>Iglesia del Nazareno</h1>
            <h2>Bagua</h2>
            <p>Llamados a Santidad</p>
        </div>
    </div>

    <div class="slide">
        <img src="<?= URL ?>public/web/imagenes/2.png" class="slide-img">
        <div class="overlay"></div>
        <div class="slide-content">
            <h1>Una Familia en Cristo</h1>
            <p>Unidos en amor y fe</p>
        </div>
    </div>

    <div class="slide">
        <img src="<?= URL ?>public/web/imagenes/3.png" class="slide-img">
        <div class="overlay"></div>
        <div class="slide-content">
            <h1>Bienvenido</h1>
            <p>Este es tu hogar</p>
        </div>
    </div>
</section>

<section class="card-section">
    <div class="card-salvacion">
        <h2>¿ERES SALVO?</h2>
        <p>Descubre lo que la Biblia enseña sobre la salvación y cómo tener una relación con Dios.</p>
        <a href="<?= URL ?>Car_salvacion" class="btn-descubre">Descúbrelo</a>
    </div>
</section>

<section class="info-section">
    <h2 class="titulo-seccion">Conócenos</h2>
    <div class="cards-container">
        <a href="<?= URL ?>fe" class="card card-fe">
            <div class="overlay"></div>
            <div class="card-content">
                <h3>Artículos de Fe</h3>
                <p>Descubre en qué creemos como iglesia.</p>
            </div>
        </a>

        <a href="<?= URL ?>mision" class="card card-mision">
             <div class="overlay"></div>
            <div class="card-content">
                <h3>Misión</h3>
                <p>Nuestra razón de ser y propósito.</p>
            </div>
        </a>

        <a href="<?= URL ?>valores" class="card card-valores">
             <div class="overlay"></div>
            <div class="card-content">
                <h3>Valores</h3>
                <p>Principios que guían nuestra vida cristiana.</p>
            </div>
        </a>
    </div>
</section>

<section class="noticias-section">
    <h2 class="titulo-noticias">NOTICIAS NAZARENAS</h2>
    <div class="noticias-container">
        <div class="noticia-card">
            <img src="<?= URL ?>public/web/imagenes/noticia2.webp" alt="Noticia">
            <div class="noticia-content">
                <h3>Evento especial</h3>
                <p>Un tiempo de bendición y comunión para la familia.</p>
                <a href="#" class="btn-leer">Leer más</a>
            </div>
        </div>
        </div>
</section>

<?php 
include __DIR__ . '/componentes/footer.php'; 
?>

<script src="<?= URL ?>public/web/js/index.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iglesia del Nazareno</title>
    <link rel="stylesheet" href="public/css/inicio.css">
    <link rel="stylesheet" href="public/css/nav.css">
    <link rel="stylesheet" href="public/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'aplicacion/vistas/publico/componentes/nav.php'; ?>


 
<section class="hero-slider">

    <!-- SLIDE 1 -->
    <div class="slide active">
        <img src="public/imagenes/1.png" class="slide-img">

        <div class="overlay"></div>

        <div class="slide-content">
            <h1>Iglesia del Nazareno</h1>
            <h2>Bagua</h2>
            <p>Llamados a Santidad</p>
        </div>
    </div>

    <!-- SLIDE 2 -->
    <div class="slide">
        <img src="public/imagenes/2.png" class="slide-img">

        <div class="overlay"></div>

        <div class="slide-content">
            <h1>Una Familia en Cristo</h1>
            <p>Unidos en amor y fe</p>
        </div>
    </div>

    <!-- SLIDE 3 -->
    <div class="slide">
        <img src="public/imagenes/3.png" class="slide-img">

        <div class="overlay"></div>

        <div class="slide-content">
            <h1>Bienvenido</h1>
            <p>Este es tu hogar</p>
        </div>
    </div>

</section>

<!-- CONTENIDO QUE HACE SCROLL -->
<section class="card-section">
    <div class="card-salvacion">
        <h2>¿ERES SALVO?</h2>
        <p>
            Descubre lo que la Biblia enseña sobre la salvación
            y cómo puedes tener una relación personal con Dios.
        </p>

        <a href="Car_salvacion.php" class="btn-descubre">
            Descúbrelo
        </a>
    </div>
</section>


<!-- /////////////////////////// -->
<!-- APARTADO DE MISION , VISION Y VALORES -->
<!-- //////////////////////////// -->
<section class="info-section">

    <h2 class="titulo-seccion">Conócenos</h2>

    <div class="cards-container">

        <a href="fe.php" class="card card-fe">
            <div class="overlay"></div>
    <div class="card-content">
        <h3>Artículos de Fe</h3>
        <p>Descubre en qué creemos como iglesia.</p>
    </div>
</a>

<a href="mision.php" class="card card-mision">
     <div class="overlay"></div>
    <div class="card-content">
        <h3>Misión</h3>
        <p>Nuestra razón de ser y propósito.</p>
    </div>
</a>

<a href="valores.php" class="card card-valores">
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

        <!-- CARD NOTICIA -->
        <div class="noticia-card">
            <img src="public/imagenes/noticia1.jpeg" alt="Noticia">

            <div class="noticia-content">
                <h3>Evento especial en la iglesia</h3>
                <p>
                    Un tiempo de bendición y comunión donde muchas
                    personas fueron impactadas por la palabra de Dios.
                </p>

                <a href="#" class="btn-leer">Leer más</a>
            </div>
        </div>

        <!-- CARD -->
        <div class="noticia-card">
            <img src="public/imagenes/noticia2.webp" alt="Noticia">

            <div class="noticia-content">
                <h3>Campaña evangelística</h3>
                <p>
                    Se realizó una campaña donde muchas personas
                    recibieron el mensaje de salvación.
                </p>

                <a href="#" class="btn-leer">Leer más</a>
            </div>
        </div>

        <!-- CARD -->
        <div class="noticia-card">
            <img src="public/imagenes/noticia3.jpeg" alt="Noticia">

            <div class="noticia-content">
                <h3>Reunión de jóvenes</h3>
                <p>
                    Jóvenes reunidos adorando y aprendiendo más de Dios.
                </p>

                <a href="#" class="btn-leer">Leer más</a>
            </div>
        </div>

    </div>

</section>


<?php include 'aplicacion/vistas/publico/componentes/footer.php'; ?>

<script src="public/js/index.js"></script>

</body>
</html>
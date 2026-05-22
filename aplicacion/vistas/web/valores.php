<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="<?= URL ?>public/web/css/valores.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/cards_conocenos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
</head>
<body>
    <?php include 'componentes/nav.php'; ?>

<br><br><br><br>

    <section class="valores-header">
    <h1 class="titulo-valores">Valores</h1>
    <p class="subtitulo-valores">
        Nuestros Valores Medulares constituyen la esencia de nuestra identidad, respaldan la visión de nuestra denominación y ayudan a dar forma a nuestra cultura.
    </p>
</section>

<section class="valores-container">
    <div class="valores-card-wrapper">
        
        <div class="valores-sidebar">
            <div class="valores-resource-icon">
                <img src="<?= URL ?>public/web/imagenes/core-values-logo.png" alt="Core Values">
            </div>
            <h3>DOCUMENTO DE VALORES MEDULARES</h3>
            <a href="#" class="btn-descargar-valores">
                DESCARGAR <i class="fas fa-arrow-down"></i>
            </a>
        </div>

        <div class="valores-content">
            
            <div class="valor-item">
                <div class="valor-icon-small">
                    <img src="<?= URL ?>public/web/imagenes/icon-church.png" alt="Icono Iglesia">
                </div>
                <h2>UN PUEBLO CRISTIANO</h2>
                <p>Como miembros de la Iglesia Universal, nos unimos a los verdaderos creyentes en la proclamación del Señorío de Jesucristo y en la afirmación de los credos y creencias trinitarios históricos de la fe cristiana.</p>
                <p>Estamos unidos a todos los creyentes en la proclamación del señorío de Jesucristo. Creemos que, en su amor divino, Dios ofrece a todas las personas el perdón de los pecados y la restauración de la relación con Él.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon-small">
                    <img src="<?= URL ?>public/web/imagenes/icon-fire.png" alt="Icono Santidad">
                </div>
                <h2>UN PUEBLO DE SANTIDAD</h2>
                <p>Dios, quien es santo, nos llama a una vida en santidad. Creemos que el Espíritu Santo busca hacer en nosotros una segunda obra de gracia, conocida con diversos términos incluyendo "la entera santificación".</p>
                <p>Creemos en Dios el Padre, el Creador, que da origen a lo que no existe. Antes no existíamos pero Dios nos dio la vida, nos formó para sí mismo y nos creó a Su imagen.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon-small">
                    <img src="<?= URL ?>public/web/imagenes/icon-world.png" alt="Icono Misional">
                </div>
                <h2>UN PUEBLO MISIONAL</h2>
                <p>Somos un pueblo enviado, que responde al llamado de Cristo y fortalecido por el Espíritu Santo, va por todo el mundo para dar testimonio del señorío de Cristo y colaborar con Dios en la edificación de la iglesia.</p>
                <p class="cita-biblica">(Mateo 28:19-20; 2 Corintios 6:1).</p>
            </div>

        </div>
    </div>
</section>
  <?php include __DIR__ . '/cards_conocenos.php'; ?>
<?php 
include __DIR__ . '/componentes/footer.php'; 
?>
</body>
</html>
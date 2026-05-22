<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/creemos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/cards_conocenos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
</head>
<body>
    <?php include 'componentes/nav.php'; ?>

<br><br><br><br>
<section class="creencias-header">
    <h1 class="titulo">En qué creemos</h1>
</section>

<section class="creencias-lema">
    <h2><em>Un SEÑOR</em> | <em>Una FE</em> | <em>Un BAUTISMO</em></h2>
    <p>
        Toda organización que perdura en el tiempo debe su longevidad a una combinación profundamente compartida de propósito, creencias y valores. Así ocurre con la Iglesia del Nazareno. Ella existe para predicar, enseñar y modelar la santidad de corazón y de vida como el núcleo misional de su vocación de hacer discípulos semejantes a Cristo en las naciones. Nuestro presente y nuestro futuro como denominación dependen de nuestra fiel participación en la misión de Dios y de nuestra aceptación de la vocación distintiva que Dios nos ha dado, una entre muchas denominaciones cristianas.
    </p>
</section>
<section class="creencias-header">
    <h1 class="titulo-creencias">Declaración de Creencias</h1>
    <p class="subtitulo-creencias">
        Somos una iglesia de la Gran Comisión. Como comunidad global de fe, <strong>SE NOS HA ENCOMENDADO LLEVAR LAS BUENAS NUEVAS DE VIDA EN CRISTO JESÚS A LAS PERSONAS DE TODAS PARTES</strong> y difundir el mensaje de la santidad bíblica por todo el mundo.
    </p>
</section>
<section class="creencias-container">
    <div class="creencias-card-main">
        
        <div class="creencias-sidebar">
            <img src="<?= URL ?>public/web/imagenes/statement-beliefs-logo.png" alt="Statement of Beliefs">
        </div>

        <div class="creencias-grid">
            
            <div class="creencia-item">
                <h4>CREEMOS</h4>
                <p>en un Dios—el Padre, el Hijo, y el Espíritu Santo.</p>
            </div>

            <div class="creencia-item">
                <h4>CREEMOS</h4>
                <p>que las escrituras del Antiguo y del Nuevo Testamento, dadas por plena inspiración, contienen toda la verdad necesaria para la vida y la fe cristiana.</p>
            </div>

            <div class="creencia-item">
                <h4>CREEMOS</h4>
                <p>que los seres humanos nacen con una naturaleza caída y, por lo tanto, tienen una inclinación hacia el mal, y esto de forma constante.</p>
            </div>

            <div class="creencia-item">
                <h4>CREEMOS</h4>
                <p>que los que no se arrepienten están perdidos de forma irremediable y eterna.</p>
            </div>

            <div class="creencia-item">
                <h4>CREEMOS</h4>
                <p>que la expiación por medio de Jesucristo es para toda la raza humana; y que todo aquel que se arrepienta y crea en el Señor Jesucristo es justificado, regenerado y salvado del dominio del pecado.</p>
            </div>

            <div class="creencia-item">
                <h4>CREEMOS</h4>
                <p>que los creyentes deben ser enteramente santificados tras la regeneración, mediante la fe en el Señor Jesucristo.</p>
            </div>

            <div class="creencia-item">
                <h4>CREEMOS</h4>
                <p>que el Espíritu Santo da testimonio del nuevo nacimiento así como de la entera santificación de los creyentes.</p>
            </div>

            <div class="creencia-item">
                <h4>CREEMOS</h4>
                <p>que nuestro Señor regresará, los muertos resucitarán, y se llevará a cabo el juicio final.</p>
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
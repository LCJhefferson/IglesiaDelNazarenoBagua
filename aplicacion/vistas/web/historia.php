<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/historia.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
</head>
<body>
    <?php 
// Aquí podrías incluir tu config y nav
include __DIR__ . '/componentes/nav.php'; 
?>

<div class="historia-header">
    <h1>Nuestra Herencia de Fe</h1>
    <p>Un viaje a través del tiempo: de los comienzos a la actualidad.</p>
</div>

<section class="timeline-container">
    <div class="timeline-line"></div>

    <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="timeline-date">1908</div>
        <div class="timeline-content">
            <div class="timeline-img">
                <img src="https://via.placeholder.com/600x400" alt="Los inicios">
            </div>
            <div class="timeline-text">
                <h3>El Nacimiento de un Movimiento</h3>
                <p>Aquí va la descripción del evento fundacional. Puedes hablar sobre la unión de las diversas iglesias que dieron origen a la Iglesia del Nazareno y los pioneros que guiaron el camino.</p>
            </div>
        </div>
    </div>

    <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="timeline-date">1950 - 1970</div>
        <div class="timeline-content">
            <div class="timeline-img">
                <img src="https://via.placeholder.com/600x400" alt="Expansión">
            </div>
            <div class="timeline-text">
                <h3>Título del Evento o Época</h3>
                <p>Aquí puedes describir cómo la fe cruzó fronteras. El crecimiento en nuevas regiones, la construcción de los primeros templos emblemáticos y el impacto social de la época.</p>
            </div>
        </div>
    </div>

    <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="timeline-date">2000 - Presente</div>
        <div class="timeline-content">
            <div class="timeline-img">
                <img src="https://via.placeholder.com/600x400" alt="Actualidad">
            </div>
            <div class="timeline-text">
                <h3>Mirando hacia el Futuro</h3>
                <p>Subtítulo sobre la era digital y la misión actual. Describe cómo la iglesia se adapta a los nuevos tiempos sin perder su esencia de "Llamados a Santidad".</p>
            </div>
        </div>
    </div>

</section>

<?php include __DIR__ . '/componentes/footer.php'; ?>
</body>
</html>
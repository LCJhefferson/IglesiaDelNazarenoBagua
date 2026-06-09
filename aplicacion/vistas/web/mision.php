<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/mision.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/cards_conocenos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
</head>
<?php include 'componentes/nav.php'; ?>
<body>
    <section class="mision-header">
    <h1 class="titulo-mision">Declaración de Misión</h1>
    <p class="subtitulo-mision">
        La misión de la Iglesia del Nazareno es <strong>Hacer Discípulos semejantes a Cristo en las naciones.</strong>
    </p>
</section>

<section class="mision-container">
    <div class="mision-card-main">
        <div class="mision-grid">
            <div class="mision-image-box">
                <img src="<?= URL ?>public/web/imagenes/mision-icon.png" alt="Statement of Mission">
            </div>

            <div class="mision-text-box">
                <p>Somos una iglesia de la Gran Comisión. Como comunidad global de fe, <strong>SE NOS HA ENCOMENDADO LLEVAR LAS BUENAS NUEVAS DE VIDA EN CRISTO JESÚS A LAS PERSONAS DE TODAS PARTES</strong> y difundir el mensaje de la santidad bíblica por todo el mundo.</p>
                
                <p><strong>LA IGLESIA DEL NAZARENO UNE A PERSONAS</strong> que han hecho de Jesucristo el señor de sus vidas, compartiendo la comunión cristiana, y buscando fortalecerse mutuamente en el desarrollo de la fe...</p>

                <div class="mision-puntos-clave">
                    <span>EVANGELISMO</span>
                    <span>SANTIFICACIÓN</span>
                    <span>DISCIPULADO</span>
                    <span>COMPASIÓN</span>
                </div>

                <p class="mision-callout">DIOS CONTINÚA LLAMANDO A GENTE ORDINARIA PARA HACER COSAS EXTRAORDINARIAS.</p>
            </div>
        </div>
    </div>
</section>

    <?php include __DIR__ . '/cards_conocenos.php'; ?>
<?php include __DIR__ . '/componentes/footer.php'; ?>
    
</body>
</html>
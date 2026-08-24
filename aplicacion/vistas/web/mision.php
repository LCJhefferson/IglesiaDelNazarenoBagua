<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declaración de Misión - Iglesia del Nazareno</title>
    
    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- CSS GLOBAL Y NAVEGACIÓN -->
    <link rel="stylesheet" href="<?= URL ?>public/web/css/globalPublico.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    
    <!-- CSS ESPECÍFICOS -->
    <link rel="stylesheet" href="<?= URL ?>public/web/css/mision.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/cards_conocenos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
</head>
<body class="pagina-conocenos">

    <?php include 'componentes/nav.php'; ?>

    <main class="mision-container">
        
        <!-- ENCABEZADO PRINCIPAL -->
        <header class="mision-header">
            <span class="badge-tag">• SECCIÓN CONÓCENOS</span>
            <h1 class="titulo-mision">Declaración de <em>Misión</em></h1>
            <p class="subtitulo-mision">
                La misión de la Iglesia del Nazareno es <strong>hacer discípulos semejantes a Cristo en las naciones.</strong>
            </p>
        </header>

        <!-- SECCIÓN DE CONTENIDO SPLIT (IMAGEN Y TEXTO) -->
        <section class="mision-split-card">
            
            <!-- CONTENEDOR DE LA IMAGEN (Reemplaza la ruta 'src' por tu imagen) -->
            <div class="mision-image-column">
                <div class="mision-image-wrapper">
                    <img src="<?= URL ?>public/web/imagenes/misionIMG.jpeg" alt="Misión Iglesia del Nazareno">
                    <span class="image-overlay-badge">
                        <i class="fa-solid fa-globe"></i> Gran Comisión
                    </span>
                </div>
            </div>

            <!-- CONTENIDO DE TEXTO -->
            <div class="mision-info-column">
                
                <div class="mision-lead-block">
                    <h2>Llevar las buenas nuevas a cada rincón</h2>
                    <p>Somos una iglesia de la Gran Comisión. Como comunidad global de fe, <strong>se nos ha encomendado llevar el mensaje de vida en Cristo Jesús a todas las personas</strong> y difundir la santidad bíblica por todo el mundo.</p>
                </div>

                <p class="mision-text-body">
                    Unimos a creyentes que han hecho de Jesucristo el Señor de sus vidas, compartiendo la comunión cristiana y buscando fortalecerse mutuamente en el desarrollo continuo de la fe.
                </p>

                <!-- PILARES DE LA MISIÓN -->
                <div class="mision-pillars-grid">
                    <div class="pillar-item">
                        <div class="pillar-icon"><i class="fa-solid fa-bullhorn"></i></div>
                        <span>Evangelismo</span>
                    </div>
                    <div class="pillar-item">
                        <div class="pillar-icon"><i class="fa-solid fa-fire-flame-curved"></i></div>
                        <span>Santificación</span>
                    </div>
                    <div class="pillar-item">
                        <div class="pillar-icon"><i class="fa-solid fa-user-group"></i></div>
                        <span>Discipulado</span>
                    </div>
                    <div class="pillar-item">
                        <div class="pillar-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
                        <span>Compasión</span>
                    </div>
                </div>

                <!-- CALLOUT BANNER -->
                <div class="mision-quote-box">
                    <i class="fa-solid fa-quote-left quote-icon"></i>
                    <p>"Dios continúa llamando a gente ordinaria para hacer cosas extraordinarias."</p>
                </div>

            </div>

        </section>

    </main>

    <!-- Componentes reutilizables inferior -->
    <?php include __DIR__ . '/cards_conocenos.php'; ?>
    <?php include __DIR__ . '/componentes/footer.php'; ?>

</body>
</html>
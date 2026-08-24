<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valores Medulares - Iglesia del Nazareno</title>
    
    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- CSS GLOBAL Y NAVEGACIÓN -->
    <link rel="stylesheet" href="<?= URL ?>public/web/css/globalPublico.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    
    <!-- CSS ESPECÍFICOS -->
    <link rel="stylesheet" href="<?= URL ?>public/web/css/valores.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/cards_conocenos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
</head>
<body class="pagina-conocenos">

    <?php include 'componentes/nav.php'; ?>

    <main class="valores-container">
        
        <!-- HERO / ENCABEZADO -->
        <header class="valores-header">
            <span class="badge-tag">• SECCIÓN CONÓCENOS</span>
            <h1>Nuestros <em>Valores</em></h1>
            <p class="valores-intro">
                Nuestros Valores  constituyen la esencia de nuestra identidad, respaldan la visión de nuestra denominación y ayudan a dar forma a nuestra cultura eclesiástica en todo el mundo.
            </p>

        </header>

        <!-- SECCIÓN DE VALORES MEDULARES (Tarjetas modernas) -->
        <section class="valores-grid">
            
            <!-- VALOR 1 -->
            <article class="valor-card">
                <div class="valor-header-badge">
                    <div class="valor-icon-wrapper">
                        <i class="fa-solid fa-church"></i>
                    </div>
                    <span class="valor-number">01</span>
                </div>
                <div class="valor-content">
                    <h2>Un Pueblo Cristiano</h2>
                    <p>Como miembros de la Iglesia Universal, nos unimos a los verdaderos creyentes en la proclamación del Señorío de Jesucristo y en la afirmación de los credos y creencias trinitarios históricos de la fe cristiana.</p>
                    <p>Estamos unidos a todos los creyentes en la proclamación del señorío de Jesucristo. Creemos que, en su amor divino, Dios ofrece a todas las personas el perdón de los pecados y la restauración de la relación con Él.</p>
                </div>
            </article>

            <!-- VALOR 2 -->
            <article class="valor-card highlight-valor">
                <div class="valor-header-badge">
                    <div class="valor-icon-wrapper">
                        <i class="fa-solid fa-fire-flame-curved"></i>
                    </div>
                    <span class="valor-number">02</span>
                </div>
                <div class="valor-content">
                    <h2>Un Pueblo de Santidad</h2>
                    <p>Dios, quien es santo, nos llama a una vida en santidad. Creemos que el Espíritu Santo busca hacer en nosotros una segunda obra de gracia, conocida con diversos términos incluyendo "la entera santificación".</p>
                    <p>Creemos en Dios el Padre, el Creador, que da origen a lo que no existe. Antes no existíamos pero Dios nos dio la vida, nos formó para sí mismo y nos creó a Su imagen.</p>
                </div>
            </article>

            <!-- VALOR 3 -->
            <article class="valor-card">
                <div class="valor-header-badge">
                    <div class="valor-icon-wrapper">
                        <i class="fa-solid fa-earth-americas"></i>
                    </div>
                    <span class="valor-number">03</span>
                </div>
                <div class="valor-content">
                    <h2>Un Pueblo Misional</h2>
                    <p>Somos un pueblo enviado, que responde al llamado de Cristo y fortalecido por el Espíritu Santo, va por todo el mundo para dar testimonio del señorío de Cristo y colaborar con Dios en la edificación de la iglesia.</p>
                    <span class="citas-biblicas">(Mateo 28:19-20; 2 Corintios 6:1)</span>
                </div>
            </article>

        </section>

    </main>

    <!-- Componentes reutilizables inferior -->
    <?php include __DIR__ . '/cards_conocenos.php'; ?>
    <?php include __DIR__ . '/componentes/footer.php'; ?>

</body>
</html>
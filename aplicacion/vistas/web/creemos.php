<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>En qué creemos - Iglesia del Nazareno</title>
    
    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- CSS GLOBAL Y NAVEGACIÓN -->
    <link rel="stylesheet" href="<?= URL ?>public/web/css/globalPublico.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    
    <!-- CSS ESPECÍFICOS -->
    <link rel="stylesheet" href="<?= URL ?>public/web/css/creemos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/cards_conocenos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
</head>
<body class="pagina-conocenos">

    <?php include 'componentes/nav.php'; ?>

    <main class="creencias-container">
        
        <!-- ENCABEZADO Y LEMA PRINCIPAL -->
        <header class="creencias-header">
            <span class="badge-tag">• SECCIÓN CONÓCENOS</span>
            <h1 class="titulo">En qué <em>creemos</em></h1>
            
            <div class="creencias-lema">
                <h2><em>Un SEÑOR</em> &bull; <em>Una FE</em> &bull; <em>Un BAUTISMO</em></h2>
                <p>
                    Toda organización que perdura en el tiempo debe su longevidad a una combinación profundamente compartida de propósito, creencias y valores. Así ocurre con la Iglesia del Nazareno. Ella existe para predicar, enseñar y modelar la santidad de corazón y de vida como el núcleo misional de su vocación de hacer discípulos semejantes a Cristo en las naciones. Nuestro presente y nuestro futuro como denominación dependen de nuestra fiel participación en la misión de Dios y de nuestra aceptación de la vocación distintiva que Dios nos ha dado, una entre muchas denominaciones cristianas.
                </p>
            </div>
        </header>

        <!-- SECCIÓN DECLARACIÓN DE CREENCIAS -->
        <section class="creencias-declaracion-header">
            <h2 class="titulo-creencias">Declaración de Creencias</h2>
            <p class="subtitulo-creencias">
                Somos una iglesia de la Gran Comisión. Como comunidad global de fe, <strong>SE NOS HA ENCOMENDADO LLEVAR LAS BUENAS NUEVAS DE VIDA EN CRISTO JESÚS A LAS PERSONAS DE TODAS PARTES</strong> y difundir el mensaje de la santidad bíblica por todo el mundo.
            </p>
        </section>

        <!-- CONTENEDOR EXCLUSIVO PARA LAS TARJETAS (Sin sidebar) -->
        <section class="creencias-grid-full">
            
            <article class="creencia-item">
                <span class="badge-creemos"><i class="fa-solid fa-cross"></i> CREEMOS</span>
                <p>en un Dios—el Padre, el Hijo, y el Espíritu Santo.</p>
            </article>

            <article class="creencia-item">
                <span class="badge-creemos"><i class="fa-solid fa-book-open"></i> CREEMOS</span>
                <p>que las escrituras del Antiguo y del Nuevo Testamento, dadas por plena inspiración, contienen toda la verdad necesaria para la vida y la fe cristiana.</p>
            </article>

            <article class="creencia-item">
                <span class="badge-creemos"><i class="fa-solid fa-heart-crack"></i> CREEMOS</span>
                <p>que los seres humanos nacen con una naturaleza caída y, por lo tanto, tienen una inclinación hacia el mal, y esto de forma constante.</p>
            </article>

            <article class="creencia-item">
                <span class="badge-creemos"><i class="fa-solid fa-triangle-exclamation"></i> CREEMOS</span>
                <p>que los que no se arrepienten están perdidos de forma irremediable y eterna.</p>
            </article>

            <article class="creencia-item">
                <span class="badge-creemos"><i class="fa-solid fa-hand-holding-heart"></i> CREEMOS</span>
                <p>que la expiación por medio de Jesucristo es para toda la raza humana; y que todo aquel que se arrepienta y crea en el Señor Jesucristo es justificado, regenerado y salvado del dominio del pecado.</p>
            </article>

            <article class="creencia-item">
                <span class="badge-creemos"><i class="fa-solid fa-fire-flame-curved"></i> CREEMOS</span>
                <p>que los creyentes deben ser enteramente santificados tras la regeneración, mediante la fe en el Señor Jesucristo.</p>
            </article>

            <article class="creencia-item">
                <span class="badge-creemos"><i class="fa-solid fa-dove"></i> CREEMOS</span>
                <p>que el Espíritu Santo da testimonio del nuevo nacimiento así como de la entera santificación de los creyentes.</p>
            </article>

            <article class="creencia-item">
                <span class="badge-creemos"><i class="fa-solid fa-cloud-sun"></i> CREEMOS</span>
                <p>que nuestro Señor regresará, los muertos resucitarán, y se llevará a cabo el juicio final.</p>
            </article>

        </section>

    </main>

    <?php include __DIR__ . '/cards_conocenos.php'; ?>
    <?php include __DIR__ . '/componentes/footer.php'; ?>

</body>
</html>
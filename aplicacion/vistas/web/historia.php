<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestra Historia - Iglesia del Nazareno</title>
    
    <!-- Fuentes institucionales -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    
    <!-- CSS Globales y de componentes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/globalPublico.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    
    <link rel="stylesheet" href="<?= URL ?>public/web/css/historia.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
</head>
<body class="pagina-historia">

    <?php include __DIR__ . '/componentes/nav.php'; ?>

    <!-- HERO HISTORIA (Alineado completamente a la izquierda) -->
    <section class="hist-hero">
        <div class="hist-hero-inner">

            <div class="hist-eyebrow rev d1">
                <span class="hist-punto"></span>
                NUESTRA HERENCIA DE FE
            </div>

            <h1 class="hist-titulo rev d2">
                Nuestra<br>
                <em>historia,</em><br>
                nuestro legado.
            </h1>

            <p class="hist-subtitulo rev d3">
                Un viaje a través del tiempo: desde nuestros comienzos como movimiento de santidad hasta nuestra expansión sirviendo a las naciones.
            </p>

            <div class="hist-features rev d4">
                <div class="hist-feature">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>DESDE 1908</span>
                </div>
                <div class="hist-feature">
                    <i class="fa-solid fa-globe"></i>
                    <span>IMPACTO GLOBAL</span>
                </div>
                <div class="hist-feature">
                    <i class="fa-solid fa-heart"></i>
                    <span>SANTIDAD BÍBLICA</span>
                </div>
            </div>

        </div>
    </section>

    <!-- MARQUESINA ANIMADA -->
    <?php
    $items = ['FIDELIDAD BÍBLICA', 'HERENCIA SANTA', 'FE EN ACCIÓN', 'DISCÍPULOS EN LAS NACIONES', 'UNIDOS EN CRISTO'];
    $all   = array_merge($items, $items, $items, $items);
    ?>
    <div class="hist-marquee-wrap" aria-hidden="true">
        <div class="hist-marquee-track">
            <?php foreach ($all as $item): ?>
                <span class="hist-marquee-item"><b>◆</b> <?= $item ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- LÍNEA DE TIEMPO CENTRADA -->
    <section class="timeline-section">
        <div class="timeline-container">
            <div class="timeline-line"></div>

            <!-- HITO 1: 1908 -->
            <article class="timeline-item rev-s">
                <div class="timeline-dot"></div>
                <div class="timeline-date">1908</div>
                <div class="timeline-content">
                    <div class="timeline-img">
                        <img src="<?= URL ?>public/web/imagenes/historia-198.jpg" alt="Unión de Pilot Point" onerror="this.src='https://images.unsplash.com/photo-1438232992991-995b705bbb3?auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="timeline-text">
                        <h3>El Nacimiento del Movimiento</h3>
                        <p>Organizada en Pilot Point, Texas, mediante la unión de diversas asociaciones de santidad. Bajo el liderazgo de Phineas F. Bresee y otros pioneros, la iglesia nació con el firme propósito de predicar la santidad bíblica y servir a los más necesitados.</p>
                    </div>
                </div>
            </article>

            <!-- HITO 2: 1914 - 1950 -->
            <article class="timeline-item rev-s">
                <div class="timeline-dot"></div>
                <div class="timeline-date">1914 - 1950</div>
                <div class="timeline-content">
                    <div class="timeline-img">
                        <img src="<?= URL ?>public/web/imagenes/historia-misiones.jpg" alt="Expansión Misionera" onerror="this.src='https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="timeline-text">
                        <h3>Expansión Transcultural y Misiones</h3>
                        <p>La iglesia extendió sus horizontes enviando misioneros a América Latina, Asia y África. Durante este período se construyeron los primeros colegios teológicos, centros médicos y templos, sembrando el Evangelio en diversos idiomas y culturas.</p>
                    </div>
                </div>
            </article>

            <!-- HITO 3: 1970 - 2000 -->
            <article class="timeline-item rev-s">
                <div class="timeline-dot"></div>
                <div class="timeline-date">1970 - 2000</div>
                <div class="timeline-content">
                    <div class="timeline-img">
                        <img src="<?= URL ?>public/web/imagenes/historia-peru.jpg" alt="Consolidación y Crecimiento" onerror="this.src='https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=800&q=80'">
                    </div>
                    <div class="timeline-text">
                        <h3>Consolidación e Impacto Comunitario</h3>
                        <p>Formación de distritos autónomos y un fuerte impulso al discipulado local. La labor de compasión nazarena (NCM) cobró gran fuerza, respondiendo con ayuda humanitaria, desarrollo comunitario y educación teológica en cada región.</p>
                    </div>
                </div>
            </article>

            <!-- HITO 4: Presente -->
            <article class="timeline-item rev-s">
                <div class="timeline-dot"></div>
                <div class="timeline-date">2000 - Presente</div>
                <div class="timeline-content">
                    <div class="timeline-img">
                        <img src="<?= URL ?>public/web/imagenes/histora-actual.jpg" alt="Iglesia Hoy" onerror="this.src='https://images.unsplash.com/photo-151163275486-a01980e01a18?auto=fomat&fit=crop&w=800&q=80'">
                    </div>
                    <div class="timeline-text">
                        <h3>Transformación Digital y Futuro</h3>
                        <p>Presentes en más de 160 áreas mundiales. Asumiendo los desafíos del siglo XXI mediante medios digitales, evangelización creativa y plantación de iglesias, manteniendo inalterable nuestra misión: hacer discípulos semejantes a Cristo.</p>
                    </div>
                </div>
            </article>

        </div>
    </section>

    <?php include __DIR__ . '/componentes/footer.php'; ?>

    <!-- Script para animaciones de entrada -->
    <script>
    const revObs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('in');
                revObs.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('.rev, .rev-s').forEach(el => revObs.observe(el));
    </script>

</body>
</html>
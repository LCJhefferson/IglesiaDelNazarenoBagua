<?php
// Configuración de las 9 imágenes del carrusel
$slides = [
    [
        'img' => '1.png',
        'tag' => 'IGLESIA DEL NAZARENO - BAGUA CAPITAL',
        'titulo' => '"Fíate de Jehová de todo tu corazón."',
        'verso' => 'Proverbios 3:5',
        'subtitulo' => 'Iglesia del Nazareno — Bagua Capital, Perú.',
        'btn1_texto' => '¿Eres salvo?',
        'btn1_url' => URL . 'Car_salvacion',
        'btn2_texto' => 'Conocer la iglesia',
        'btn2_url' => '#conocenos'
    ],
    [
        'img' => '2.png',
        'tag' => 'IGLESIA DEL NAZARENO - BAGUA CAPITAL',
        'titulo' => '"Porque de tal manera amó Dios al mundo..."',
        'verso' => 'Juan 3:16',
        'subtitulo' => 'Un lugar donde encontrarás paz, propósito y comunidad.',
        'btn1_texto' => '¿Eres salvo?',
        'btn1_url' => URL . 'Car_salvacion',
        'btn2_texto' => 'Conocer la iglesia',
        'btn2_url' => '#conocenos'
    ],
    [
        'img' => '3.png',
        'tag' => 'IGLESIA DEL NAZARENO - BAGUA CAPITAL',
        'titulo' => '"Yo soy el camino, la verdad y la vida."',
        'verso' => 'Juan 14:6',
        'subtitulo' => 'Jesús es la respuesta que tu corazón busca.',
        'btn1_texto' => '¿Eres salvo?',
        'btn1_url' => URL . 'Car_salvacion',
        'btn2_texto' => 'Conocer la iglesia',
        'btn2_url' => '#conocenos'
    ],
    [
        'img' => '4.png',
        'tag' => 'IGLESIA DEL NAZARENO - BAGUA CAPITAL',
        'titulo' => '"Todo lo puedo en Cristo que me fortalece."',
        'verso' => 'Filipenses 4:13',
        'subtitulo' => 'Caminando juntos en fe y esperanza.',
        'btn1_texto' => '¿Eres salvo?',
        'btn1_url' => URL . 'Car_salvacion',
        'btn2_texto' => 'Conocer la iglesia',
        'btn2_url' => '#conocenos'
    ],
    [
        'img' => '5.png',
        'tag' => 'IGLESIA DEL NAZARENO - BAGUA CAPITAL',
        'titulo' => '"Jehová es mi pastor; nada me faltará."',
        'verso' => 'Salmos 23:1',
        'subtitulo' => 'Descansa en las promesas del Señor.',
        'btn1_texto' => '¿Eres salvo?',
        'btn1_url' => URL . 'Car_salvacion',
        'btn2_texto' => 'Conocer la iglesia',
        'btn2_url' => '#conocenos'
    ],
    [
        'img' => '6.png',
        'tag' => 'IGLESIA DEL NAZARENO - BAGUA CAPITAL',
        'titulo' => '"Llamados a Santidad"',
        'verso' => '1 Pedro 1:16',
        'subtitulo' => 'Una comunidad entregada a la presencia de Dios.',
        'btn1_texto' => '¿Eres salvo?',
        'btn1_url' => URL . 'Car_salvacion',
        'btn2_texto' => 'Conocer la iglesia',
        'btn2_url' => '#conocenos'
    ],
    [
        'img' => '7.png',
        'tag' => 'IGLESIA DEL NAZARENO - BAGUA CAPITAL',
        'titulo' => '"Lámpara es a mis pies tu palabra."',
        'verso' => 'Salmos 119:105',
        'subtitulo' => 'Guía e iluminación para tu caminar diario.',
        'btn1_texto' => '¿Eres salvo?',
        'btn1_url' => URL . 'Car_salvacion',
        'btn2_texto' => 'Conocer la iglesia',
        'btn2_url' => '#conocenos'
    ],
    [
        'img' => '8.png',
        'tag' => 'IGLESIA DEL NAZARENO - BAGUA CAPITAL',
        'titulo' => '"Una Familia en Cristo"',
        'verso' => 'Efesios 2:19',
        'subtitulo' => 'Unidos en amor, fe y servicio.',
        'btn1_texto' => '¿Eres salvo?',
        'btn1_url' => URL . 'Car_salvacion',
        'btn2_texto' => 'Conocer la iglesia',
        'btn2_url' => '#conocenos'
    ],
    [
        'img' => '9.png',
        'es_logo' => true // La novena imagen es solo el logo, sin texto
    ]
];
?>

<!-- Estilos exclusivos del carrusel -->
<link rel="stylesheet" href="<?= URL ?>public/web/css/hero_carrusel.css">

<section class="hero-slider" id="heroCarrusel">
    <?php foreach ($slides as $index => $slide): ?>
        <div class="slide <?= $index === 0 ? 'active' : '' ?>">
            <img src="<?= URL ?>public/web/imagenes/<?= $slide['img'] ?>" alt="Slide <?= $index + 1 ?>" class="slide-img">
            
            <?php if (empty($slide['es_logo'])): ?>
                <div class="overlay-slider"></div>
                <div class="slide-content">
                    <span class="hero-pill"><?= htmlspecialchars($slide['tag']) ?></span>
                    <h1 class="hero-quote"><?= htmlspecialchars($slide['titulo']) ?></h1>
                    <span class="hero-verse"><?= htmlspecialchars($slide['verso']) ?></span>
                    <p class="hero-subtext"><?= htmlspecialchars($slide['subtitulo']) ?></p>
                    <div class="hero-buttons">
                        <a href="<?= $slide['btn1_url'] ?>" class="btn-hero-primary"><?= htmlspecialchars($slide['btn1_texto']) ?></a>
                        <a href="<?= $slide['btn2_url'] ?>" class="btn-hero-secondary"><?= htmlspecialchars($slide['btn2_texto']) ?></a>
                    </div>
                </div>
            <?php else: ?>
                <div class="overlay-slider-logo"></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <!-- Controles laterales (Flechas) -->
    <button class="hero-nav-btn prev" onclick="moverHeroSlide(-1)" aria-label="Anterior">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button class="hero-nav-btn next" onclick="moverHeroSlide(1)" aria-label="Siguiente">
        <i class="fa-solid fa-chevron-right"></i>
    </button>

    <!-- Puntos/Indicadores inferiores -->
    <div class="hero-dots">
        <?php foreach ($slides as $index => $slide): ?>
            <span class="hero-dot <?= $index === 0 ? 'active' : '' ?>" onclick="irAHeroSlide(<?= $index ?>)"></span>
        <?php endforeach; ?>
    </div>
</section>

<!-- Script exclusivo del carrusel -->
<script src="<?= URL ?>public/web/js/hero_carrusel.js" defer></script>
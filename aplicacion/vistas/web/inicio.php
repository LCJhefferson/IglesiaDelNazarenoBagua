<?php
// Consulta inicial para detectar si hay un vivo activo (Estado 1)
require_once __DIR__ . '/../../../aplicacion/config/Conexion.php';
use aplicacion\config\Conexion;

$db = Conexion::conectar();
$stmt = $db->query("SELECT titulo FROM transmisiones WHERE estado_id = 1 LIMIT 1");
$live = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iglesia del Nazareno</title>
    <link rel="stylesheet" href="<?= URL ?>public/web/css/inicio.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>
<body>

<div id="bannerTransmision" class="banner-vivo" style="<?= $live ? 'display:flex;' : 'display:none;' ?>; position: fixed; bottom: 25px; right: 25px; width: 320px; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color: white; padding: 18px; border-radius: 12px; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.35); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; z-index: 999999; flex-direction: column; gap: 12px; box-sizing: border-box; border: 1px solid rgba(255,255,255,0.1); animation: slideUpFloat 0.4s ease-out;">
    
    <div style="display: flex; align-items: center; gap: 10px;">
        <span class="dot-alerta" style="height: 10px; width: 10px; background-color: #ffffff; border-radius: 50%; display: inline-block; animation: parpadeoAlerta 1.2s infinite; box-shadow: 0 0 8px #ffffff;"></span>
        <strong style="font-size: 0.9rem; letter-spacing: 0.5px; text-transform: uppercase;">¡Transmisión en Vivo!</strong>
    </div>
    
    <span id="textoBanner" style="font-size: 0.95rem; font-weight: 500; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
        <?= $live ? htmlspecialchars($live['titulo']) : '' ?>
    </span>
    
    <a href="<?= URL ?>trasmisionPublica" class="btn-ver-vivo" style="background: #ffffff; color: #ef4444; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 700; text-align: center; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); transition: all 0.2s ease-in-out;">
        <i class="fa-solid fa-play" style="margin-right: 5px;"></i> Ver Transmisión
    </a>
</div>



<?php 
include __DIR__ . '/componentes/nav.php'; 
?>

<section class="hero-slider">
    <div class="slide active">
        <img src="<?= URL ?>public/web/imagenes/1.png" class="slide-img">
        <div class="overlay"></div>
        <div class="slide-content">
            <h1>Iglesia del Nazareno</h1>
            <h2>Bagua</h2>
            <p>Llamados a Santidad</p>
        </div>
    </div>

    <div class="slide">
        <img src="<?= URL ?>public/web/imagenes/2.png" class="slide-img">
        <div class="overlay"></div>
        <div class="slide-content">
            <h1>Una Familia en Cristo</h1>
            <p>Unidos en amor y fe</p>
        </div>
    </div>

    <div class="slide">
        <img src="<?= URL ?>public/web/imagenes/3.png" class="slide-img">
        <div class="overlay"></div>
        <div class="slide-content">
            <h1>Bienvenido</h1>
            <p>Este es tu hogar</p>
        </div>
    </div>
</section>

<section class="card-section">
    <div class="card-salvacion">
        <h2>¿ERES SALVO?</h2>
        <p>Descubre lo que la Biblia enseña sobre la salvación y cómo tener una relación con Dios.</p>
        <a href="<?= URL ?>Car_salvacion" class="btn-descubre">Descúbrelo</a>
    </div>
</section>

<section class="info-section">
    <h2 class="titulo-seccion">Conócenos</h2>
    <div class="cards-container">
        <a href="<?= URL ?>fe" class="card card-fe">
            <div class="overlay"></div>
            <div class="card-content">
                <h3>Artículos de Fe</h3>
                <p>Descubre en qué creemos como iglesia.</p>
            </div>
        </a>

        <a href="<?= URL ?>mision" class="card card-mision">
             <div class="overlay"></div>
            <div class="card-content">
                <h3>Misión</h3>
                <p>Nuestra razón de ser y propósito.</p>
            </div>
        </a>

        <a href="<?= URL ?>valores" class="card card-valores">
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

    <?php
    $stmtNoticias = $db->query("SELECT * FROM noticias WHERE estado = 1 ORDER BY fecha_creacion DESC");
    $noticiasPublicas = $stmtNoticias->fetchAll(PDO::FETCH_ASSOC);
    $totalNoticias = count($noticiasPublicas);
    ?>

    <?php if(empty($noticiasPublicas)): ?>
        <p style="text-align:center; color:#64748b; padding:40px 0;">
            No hay noticias disponibles por el momento.
        </p>
    <?php else: ?>

    <!-- CARRUSEL -->
    <div class="carrusel-wrapper">
        <button class="carrusel-btn prev" onclick="moverCarrusel(-1)">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="carrusel-track-container">
            <div class="carrusel-track" id="carrusel-track">
                <?php foreach($noticiasPublicas as $np): ?>
                <div class="carrusel-item">
                    <div class="noticia-card">
                        <?php if(!empty($np['imagen_portada'])): ?>
                            <img src="<?= URL ?><?= htmlspecialchars($np['imagen_portada']) ?>" alt="<?= htmlspecialchars($np['titulo']) ?>">
                        <?php else: ?>
                            <img src="<?= URL ?>public/web/imagenes/noticia2.webp" alt="Noticia">
                        <?php endif; ?>
                        <div class="noticia-content">
                            <span class="noticia-fecha">
                                <i class="fa-regular fa-calendar"></i>
                                <?= date("d/m/Y", strtotime($np['fecha_creacion'])) ?>
                            </span>
                            <h3><?= htmlspecialchars($np['titulo']) ?></h3>
                            <p><?= htmlspecialchars(mb_substr($np['resumen'], 0, 80, 'UTF-8')) ?>...</p>
<a href="<?= URL ?>public/index.php?vista=noticia&id=<?= $np['id'] ?>" class="btn-leer">Leer más →</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button class="carrusel-btn next" onclick="moverCarrusel(1)">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>

    <!-- PUNTOS INDICADORES -->
    <div class="carrusel-dots" id="carrusel-dots">
        <?php foreach($noticiasPublicas as $idx => $np): ?>
        <span class="dot <?= $idx === 0 ? 'activo' : '' ?>" onclick="irASlide(<?= $idx ?>)"></span>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</section>

<?php 
include __DIR__ . '/componentes/footer.php'; 
?>

<script src="<?= URL ?>public/web/js/index.js"></script>
<script src="<?= URL ?>public/web/js/notificaciones_vivo.js"></script>

</body>
</html>
<?php
// Usamos el Capsule de Eloquent para las consultas rápidas en la vista
use Illuminate\Database\Capsule\Manager as DB;

// Buscamos si hay un vivo activo usando Eloquent
$live = DB::table('transmisiones')->where('estado_id', 1)->first();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iglesia del Nazareno</title>
    <link rel="stylesheet" href="<?= URL ?>public/web/css/inicio.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/cards_conocenos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/carrusel_noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        /* Desplazamiento suave para la navegación por anclas */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>

<div id="bannerTransmision" class="banner-vivo" style="<?= $live ? 'display:flex;' : 'display:none;' ?>; position: fixed; bottom: 25px; right: 25px; width: 320px; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color: white; padding: 18px; border-radius: 12px; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.35); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; z-index: 999999; flex-direction: column; gap: 12px; box-sizing: border-box; border: 1px solid rgba(255,255,255,0.1); animation: slideUpFloat 0.4s ease-out;">
    
    <div style="display: flex; align-items: center; gap: 10px;">
        <span class="dot-alerta" style="height: 10px; width: 10px; background-color: #ffffff; border-radius: 50%; display: inline-block; animation: parpadeoAlerta 1.2s infinite; box-shadow: 0 0 8px #ffffff;"></span>
        <strong style="font-size: 0.9rem; letter-spacing: 0.5px; text-transform: uppercase;">¡Transmisión en Vivo!</strong>
    </div>
    
    <span id="textoBanner" style="font-size: 0.95rem; font-weight: 500; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
        <?= $live ? htmlspecialchars($live->titulo) : '' ?>
    </span>
    
    <a href="<?= URL ?>trasmisionPublica" class="btn-ver-vivo" style="background: #ffffff; color: #ef4444; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 700; text-align: center; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); transition: all 0.2s ease-in-out;">
        <i class="fa-solid fa-play" style="margin-right: 5px;"></i> Ver Transmisión
    </a>
</div>

<?php 
include __DIR__ . '/componentes/nav.php'; 
include __DIR__ . '/componentes/hero_carrusel.php'; 
?>

<section class="card-section">
    <div class="card-salvacion" style="background-image: url('<?= URL ?>public/web/imagenes/TU_IMAGEN.jpg');">
        
        <div class="salvacion-overlay"></div>

        <div class="salvacion-content">
            <!-- Lado Izquierdo: Información principal -->
            <div class="salvacion-left">
                <span class="badge-subtitulo">
                    <i class="fa-solid fa-heart"></i> LA PREGUNTA MÁS IMPORTANTE
                </span>
                <h2>¿Eres salvo?</h2>
                <p>
                    Esta es la pregunta más importante de tu vida. No se trata de religión, 
                    se trata de una relación personal con Jesucristo. Descubre la buena noticia 
                    que puede transformar tu eternidad.
                </p>
                <div class="salvacion-acciones">
                    <a href="<?= URL ?>Car_salvacion" class="btn-evangelio">
                        <i class="fa-solid fa-heart"></i> Conocer el Evangelio
                    </a>
                    <!-- BOTÓN AGREGADO -->
                    <a href="#conocenos" class="btn-iglesia">
                        <i class="fa-solid fa-church"></i> Conocer la iglesia
                    </a>
                </div>
            </div>

            <!-- Lado Derecho: Tarjeta del versículo -->
            <div class="salvacion-right">
                <div class="versiculo-card">
                    <div class="icono-corazon">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <blockquote>
                        "Porque de tal manera amó Dios al mundo, que ha dado a su Hijo unigénito, para que todo aquel que en Él cree no se pierda, mas tenga vida eterna."
                    </blockquote>
                    <cite>— Juan 3:16</cite>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECCIÓN CONÓCENOS (ENVOLTORIO CON ID) -->
<div id="conocenos">
    <?php include __DIR__ . '/cards_conocenos.php'; ?>
</div>

<!-- SECCIÓN NOTICIAS NAZARENAS -->
<section class="noticias-section" id="noticias">
    <div class="noticias-container">
        
        <header class="noticias-header">
            <span class="badge-tag">• ACTUALIDAD</span>
            <h2 class="titulo-seccion-noticias">Noticias Nazarenas</h2>
        </header>

        <?php
        $noticiasPublicas = DB::table('noticias')
                            ->where('estado', 1)
                            ->orderBy('fecha_creacion', 'DESC')
                            ->get(); 
        $totalNoticias = count($noticiasPublicas);
        ?>

        <?php if($totalNoticias === 0): ?>
            <p class="noticias-vacio">No hay noticias disponibles por el momento.</p>
        <?php else: ?>

        <div class="ticker-wrapper" id="tickerWrapper">
            <div class="ticker-track">
                
                <?php foreach($noticiasPublicas as $np): ?>
                <article class="noticia-card">
                    <div class="noticia-img-wrap">
                        <?php if(!empty($np->imagen_portada)): ?>
                            <img src="<?= URL ?><?= htmlspecialchars($np->imagen_portada) ?>" alt="<?= htmlspecialchars($np->titulo) ?>" loading="lazy">
                        <?php else: ?>
                            <img src="<?= URL ?>public/web/imagenes/noticia2.webp" alt="Noticia" loading="lazy">
                        <?php endif; ?>
                    </div>
                    
                    <div class="noticia-content">
                        <span class="noticia-fecha">
                            <i class="fa-regular fa-calendar"></i>
                            <?= date("d/m/Y", strtotime($np->fecha_creacion)) ?>
                        </span>
                        <h3><?= htmlspecialchars($np->titulo) ?></h3>
                        <p><?= htmlspecialchars(mb_substr($np->resumen, 0, 75, 'UTF-8')) ?>...</p>
                        
                        <a href="<?= URL ?>public/index.php?vista=noticia&id=<?= $np->id ?>&origen=web" class="btn-leer-mas">
                            Leer más <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>

                <?php foreach($noticiasPublicas as $np): ?>
                <article class="noticia-card" aria-hidden="true">
                    <div class="noticia-img-wrap">
                        <?php if(!empty($np->imagen_portada)): ?>
                            <img src="<?= URL ?><?= htmlspecialchars($np->imagen_portada) ?>" alt="<?= htmlspecialchars($np->titulo) ?>" loading="lazy">
                        <?php else: ?>
                            <img src="<?= URL ?>public/web/imagenes/noticia2.webp" alt="Noticia" loading="lazy">
                        <?php endif; ?>
                    </div>
                    
                    <div class="noticia-content">
                        <span class="noticia-fecha">
                            <i class="fa-regular fa-calendar"></i>
                            <?= date("d/m/Y", strtotime($np->fecha_creacion)) ?>
                        </span>
                        <h3><?= htmlspecialchars($np->titulo) ?></h3>
                        <p><?= htmlspecialchars(mb_substr($np->resumen, 0, 75, 'UTF-8')) ?>...</p>
                        
                        <a href="<?= URL ?>public/index.php?vista=noticia&id=<?= $np->id ?>&origen=web" class="btn-leer-mas">
                            Leer más <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>

            </div>
        </div>

        <?php endif; ?>

    </div>
</section>

<?php 
include __DIR__ . '/componentes/footer.php'; 
?>

<script src="<?= URL ?>public/web/js/noticias_carrusel.js"></script>
<script src="<?= URL ?>public/web/js/notificaciones_vivo.js"></script>

</body>
</html>
<?php
use Illuminate\Database\Capsule\Manager as DB;

// 1. Sanitizar y limpiar la entrada
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Limitar longitud de búsqueda para prevenir abusos
if (mb_strlen($busqueda, 'UTF-8') > 100) {
    $busqueda = mb_substr($busqueda, 0, 100, 'UTF-8');
}

// 2. Consulta a la base de datos
$query = DB::table('noticias')->where('estado', 1);

if ($busqueda !== '') {
    // Escapar % y _ para evitar vulneración de comodines LIKE
    $busquedaEscapada = addcslashes($busqueda, '%_\\');

    $query->where(function($q) use ($busquedaEscapada) {
        $q->where('titulo', 'LIKE', "%{$busquedaEscapada}%")
          ->orWhere('resumen', 'LIKE', "%{$busquedaEscapada}%");
    });
}

$noticias = $query->orderBy('fecha_creacion', 'DESC')->get();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todas las Noticias - Iglesia del Nazareno</title>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/todas_noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="pagina-noticias">

<?php include __DIR__ . '/componentes/nav.php'; ?>

<section class="not-hero">
    <div class="not-hero-inner">

        <div class="not-eyebrow rev d1">
            <span class="not-punto"></span>
            Boletín de novedades
        </div>

        <h1 class="not-titulo rev d2">
            Noticias &<br>
            <em>comunicados,</em><br>
            al día.
        </h1>

        <p class="not-subtitulo rev d3">
            Mantente informado con las últimas actividades, eventos y acontecimientos de nuestra comunidad de fe.
        </p>

        <div class="not-features rev d4">
            <div class="not-feature">
                <i class="fa-solid fa-newspaper"></i>
                <span>INFORMACIÓN ACTUALIZADA</span>
            </div>
            <div class="not-feature">
                <i class="fa-solid fa-bullhorn"></i>
                <span>EVENTOS OFICIALES</span>
            </div>
            <div class="not-feature">
                <i class="fa-solid fa-users"></i>
                <span>COMUNIDAD NAZARENA</span>
            </div>
        </div>

    </div>
</section>

<?php
$items = ['ACTUALIDAD', 'EVENTOS', 'TESTIMONIOS', 'COMUNIDAD', 'NOTICIAS NAZARENAS', 'ANUNCIOS OFICIALES'];$all   = array_merge($items,$items, $items,$items);
?>
<div class="not-marquee-wrap" aria-hidden="true">
    <div class="not-marquee-track">
        <?php foreach ($all as$item): ?>
            <span class="not-marquee-item"><b>◆</b> <?= $item ?></span>
        <?php endforeach; ?>
    </div>
</div>

<section class="not-controles-section">
    <div class="not-controles-container">
        <form action="" method="GET" class="not-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" placeholder="Buscar noticia por título o palabra clave..." value="<?= htmlspecialchars($busqueda) ?>"/>
            <?php if(!empty($busqueda)): ?>
                <a href="<?= URL ?>Todas_noticias" class="not-btn-clear" title="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
            <button type="submit" class="not-btn-search">Buscar</button>
        </form>
    </div>
</section>

<section class="grid-noticias-section">
    <div class="container-noticias">

        <?php if(count($noticias) === 0): ?>
            <div class="noticias-empty">
                <i class="fa-regular fa-folder-open"></i>
                <h3>No se encontraron noticias</h3>
                <p>No hay publicaciones disponibles <?= !empty($busqueda) ? 'para tu búsqueda "' . htmlspecialchars($busqueda) . '"' : '' ?>.</p>
                <?php if(!empty($busqueda)): ?>
                    <a href="<?= URL ?>Todas_noticias" class="btn-restablecer">Ver todas las noticias</a>
                <?php endif; ?>
            </div>
        <?php else: ?>

            <div class="noticias-grid">
                <?php foreach($noticias as $np): ?>
                <article class="card-noticia-item rev-s">
                    <div class="card-img-container">
                        <?php if(!empty($np->imagen_portada)): ?>
                            <img src="<?= URL ?><?= htmlspecialchars($np->imagen_portada) ?>" alt="<?= htmlspecialchars($np->titulo) ?>" loading="lazy">
                        <?php else: ?>
                            <img src="<?= URL ?>public/web/imagenes/noticia2.webp" alt="Noticia" loading="lazy">
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <span class="card-fecha">
                            <i class="fa-regular fa-calendar"></i>
                            <?= date("d/m/Y", strtotime($np->fecha_creacion)) ?>
                        </span>
                        
                        <h2 class="card-titulo"><?= htmlspecialchars($np->titulo) ?></h2>
                        
                        <p class="card-resumen">
                            <?= htmlspecialchars(mb_substr($np->resumen, 0, 110, 'UTF-8')) ?>...
                        </p>
                        
                        <a href="<?= URL ?>public/index.php?vista=noticia&id=<?= $np->id ?>&origen=web" class="card-btn">
                            Leer noticia completa <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/componentes/footer.php'; ?>

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
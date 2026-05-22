<?php
require_once __DIR__ . '/../../../aplicacion/config/Conexion.php';
use aplicacion\config\Conexion;

$db  = Conexion::conectar();
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: " . URL . "public/index.php?vista=inicio");
    exit;
}

$stmt = $db->prepare("SELECT * FROM noticias WHERE id = :id AND estado = 1 LIMIT 1");
$stmt->execute([':id' => $id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$noticia) {
    header("Location: " . URL . "public/index.php?vista=inicio");
    exit;
}

// Imágenes de galería
$stmtImg = $db->prepare("SELECT imagen as ruta FROM noticia_imagenes WHERE noticia_id = :id");
$stmtImg->execute([':id' => $id]);
$galeria = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

// Noticias relacionadas 
$stmtRel = $db->prepare("SELECT id, titulo, resumen, imagen_portada, fecha_creacion 
                          FROM noticias 
                          WHERE estado = 1 AND id != :id 
                          ORDER BY fecha_creacion DESC LIMIT 3");
$stmtRel->execute([':id' => $id]);
$relacionadas = $stmtRel->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($noticia['titulo']) ?> — Iglesia del Nazareno</title>
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/noticia.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/componentes/nav.php'; ?>


<div class="noticia-hero" <?php if (!empty($noticia['imagen_portada'])): ?>
     style="background-image: url('<?= URL ?><?= htmlspecialchars($noticia['imagen_portada']) ?>')"
     <?php else: ?>
     style="background-image: url('<?= URL ?>public/web/imagenes/noticia2.webp')"
     <?php endif; ?>>
    <div class="noticia-hero-overlay"></div>
    <div class="noticia-hero-content">
       <?php
$origen = isset($_GET['origen']) ? $_GET['origen'] : 'web';
$urlVolver = ($origen === 'admin')
    ? URL . "index.php?vista=dashboard&seccion=noticias"
    : URL . "index.php?vista=inicio#noticias-section";
?>
<a href="<?= $urlVolver ?>" class="btn-volver">
    <i class="fa-solid fa-arrow-left"></i> Volver a Noticias
</a>
        </a>
        <div class="noticia-hero-meta">
            <span class="noticia-hero-fecha">
                <i class="fa-regular fa-calendar"></i>
                <?= date("d \d\e F \d\e Y", strtotime($noticia['fecha_creacion'])) ?>
            </span>
        </div>
        <h1 class="noticia-hero-titulo"><?= htmlspecialchars($noticia['titulo']) ?></h1>
    </div>
</div>

<!-- CONTENIDO PRINCIPAL -->
<main class="noticia-main">
    <article class="noticia-articulo">

        <!-- RESUMEN DESTACADO -->
        <?php if (!empty($noticia['resumen'])): ?>
        <p class="noticia-resumen-destacado">
            <?= htmlspecialchars($noticia['resumen']) ?>
        </p>
        <?php endif; ?>

        <hr class="noticia-divisor">

        <!-- CONTENIDO COMPLETO -->
        <div class="noticia-cuerpo">
            <?= nl2br(htmlspecialchars($noticia['contenido'])) ?>
        </div>

        <!-- VIDEO EMBEBIDO -->
        <?php if (!empty($noticia['video_link'])): ?>
        <div class="noticia-video">
            <h3><i class="fa-brands fa-youtube"></i> Video relacionado</h3>
            <?php
            $videoUrl = trim($noticia['video_link']);
$videoId  = null;

// youtube.com/watch?v=ID (con o sin &si= u otros parámetros)
if (preg_match('/youtube\.com\/watch\?(?:[^#]*&)?v=([a-zA-Z0-9_-]{11})/', $videoUrl, $m)) {
    $videoId = $m[1];
}
// youtu.be/ID (links cortos compartidos desde móvil con ?si=...)
elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $videoUrl, $m)) {
    $videoId = $m[1];
}
// youtube.com/embed/ID (ya está en formato embed)
elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $videoUrl, $m)) {
    $videoId = $m[1];
}
// youtube.com/shorts/ID
elseif (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/', $videoUrl, $m)) {
    $videoId = $m[1];
}

$embedUrl = $videoId
    ? "https://www.youtube.com/embed/{$videoId}"
    : $videoUrl; // fallback para Vimeo u otro
            ?>
            <div class="video-wrapper">
                <iframe src="<?= htmlspecialchars($embedUrl) ?>" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                </iframe>
            </div>
        </div>
        <?php endif; ?>

        <!-- GALERÍA DE IMÁGENES -->
        <?php if (!empty($galeria)): ?>
        <div class="noticia-galeria">
            <h3><i class="fa-regular fa-images"></i> Galería</h3>
            <div class="galeria-grid">
                <?php foreach($galeria as $img): ?>
                <div class="galeria-item" onclick="abrirLightbox('<?= URL ?><?= htmlspecialchars($img['ruta']) ?>')">
                    <img src="<?= URL ?><?= htmlspecialchars($img['ruta']) ?>" alt="Imagen de galería">
                    <div class="galeria-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </article>
</main>

<?php if (!empty($relacionadas)): ?>
<section class="noticias-relacionadas">
    <div class="relacionadas-inner">
        <h2 class="relacionadas-titulo">Más Noticias</h2>
        <div class="relacionadas-grid">
            <?php foreach($relacionadas as $rel): ?>
            <a href="<?= URL ?>public/index.php?vista=noticia&id=<?= $rel['id'] ?>" class="rel-card">
                <div class="rel-card-img">
                    <?php if(!empty($rel['imagen_portada'])): ?>
                        <img src="<?= URL ?><?= htmlspecialchars($rel['imagen_portada']) ?>" alt="<?= htmlspecialchars($rel['titulo']) ?>">
                    <?php else: ?>
                        <img src="<?= URL ?>public/web/imagenes/noticia2.webp" alt="Noticia">
                    <?php endif; ?>
                </div>
                <div class="rel-card-body">
                    <span class="rel-card-fecha">
                        <i class="fa-regular fa-calendar"></i>
                        <?= date("d/m/Y", strtotime($rel['fecha_creacion'])) ?>
                    </span>
                    <h4><?= htmlspecialchars($rel['titulo']) ?></h4>
                    <p><?= htmlspecialchars(mb_substr($rel['resumen'], 0, 70, 'UTF-8')) ?>...</p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<div class="lightbox" id="lightbox" onclick="cerrarLightbox()">
    <button class="lightbox-close" onclick="cerrarLightbox()"><i class="fa-solid fa-xmark"></i></button>
    <img src="" id="lightbox-img" alt="Imagen ampliada">
</div>

<?php include __DIR__ . '/componentes/footer.php'; ?>

<script>
function abrirLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('activo');
    document.body.style.overflow = 'hidden';
}
function cerrarLightbox() {
    document.getElementById('lightbox').classList.remove('activo');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarLightbox(); });
</script>
</body>
</html>
<?php
use aplicacion\core\QueryBuilder;
use aplicacion\dao\RecursoDAO;
use aplicacion\modelos\Recurso;

$dao = new RecursoDAO();

if (!empty($_GET['descargar'])) {
    $recurso = $dao->obtenerPorId((int)$_GET['descargar']);
    if ($recurso) {
        $dao->incrementarDescargas((int)$_GET['descargar']);
        if (!empty($recurso['enlace_youtube'])) {
            header('Location: ' . $recurso['enlace_youtube']);
            exit;
        }
        if (!empty($recurso['ruta_archivo'])) {
            $ruta_abs = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . $recurso['ruta_archivo'];
            if (file_exists($ruta_abs)) {
                while (ob_get_level() > 0) ob_end_clean();
                $mime = mime_content_type($ruta_abs) ?: 'application/octet-stream';
                header('Content-Type: ' . $mime);
                header('Content-Disposition: attachment; filename="' . basename($ruta_abs) . '"');
                header('Content-Length: ' . filesize($ruta_abs));
                header('Cache-Control: no-cache, must-revalidate');
                readfile($ruta_abs);
                exit;
            }
        }
    }
    header('Location: ' . URL . 'recursos');
    exit;
}

$recursos  = $dao->listar();

$pendientes = array_filter($recursos, fn($r) => $r['ruta_thumb'] === null);
if (!empty($pendientes)) {
    foreach ($pendientes as $r) {
        $dao->generarYGuardarThumb(
            (int)$r['id'],
            $r['ruta_archivo']   ?? '',
            $r['tipo']           ?? 'doc',
            $r['enlace_youtube'] ?? ''
        );
    }
    $recursos = $dao->listar();
}

// Total de descargas — agregado SQL en lugar de PHP
$total_des = Recurso::sum('descargas');

// Conteo por categoría — GROUP BY en BD en lugar de foreach en PHP
$cats_raw   = (new QueryBuilder())
    ->table('recursos')
    ->select('categoria, COUNT(*) AS total')
    ->groupBy('categoria')
    ->get();
$categorias = array_column($cats_raw, 'total', 'categoria');

$icono_tipo  = ['pdf' => '📄', 'img' => '🖼️', 'vid' => '🎬', 'doc' => '📝'];
$clase_slab  = ['pdf' => 'slab-pdf', 'img' => 'slab-img', 'vid' => 'slab-vid', 'doc' => 'slab-doc'];
$label_tipo  = ['pdf' => 'PDF', 'img' => 'IMG', 'vid' => 'VIDEO', 'doc' => 'DOC'];
$label_btn   = ['vid' => 'Ver recurso'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recursos · Bagua</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/recursos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="pagina-recursos">

<?php include __DIR__ . '/componentes/nav.php'; ?>


<section class="rec-hero">
    <div class="rec-hero-inner">

        <div class="rec-eyebrow rev d1">
            <span class="rec-punto"></span>
            Biblioteca de recursos
        </div>

        <h1 class="rec-titulo rev d2">
            Todo lo que<br>
            <em>necesitas,</em><br>
            aquí.
        </h1>

        <p class="rec-subtitulo rev d3">
            Materiales para predicar, enseñar y servir — disponibles para toda la comunidad.
        </p>

        <div class="rec-features rev d4">
            <div class="rec-feature">
                <i class="fa-solid fa-lock-open"></i>
                <span>Acceso libre</span>
            </div>
            <div class="rec-feature">
                <i class="fa-solid fa-download"></i>
                <span>Descarga directa</span>
            </div>
            <div class="rec-feature">
                <i class="fa-solid fa-rotate"></i>
                <span>Siempre actualizado</span>
            </div>
            <div class="rec-feature">
                <i class="fa-solid fa-users"></i>
                <span>Para toda la comunidad</span>
            </div>
        </div>

    </div>

    <div class="rec-hero-deco" aria-hidden="true">
        <?php for ($i = 0; $i < 30; $i++): ?>
            <span></span>
        <?php endfor; ?>
    </div>
</section>


<?php
$items = ['PREDICAR', 'ENSEÑAR', 'SANAR', 'LIBRE ACCESO', 'RECURSOS NAZARENOS', 'COMPARTE EL CONOCIMIENTO', 'PARA TODA LA COMUNIDAD', 'EDUCA TU FE'];
$all   = array_merge($items, $items, $items, $items);
?>
<div class="rec-marquee-wrap" aria-hidden="true">
    <div class="rec-marquee-track">
        <?php foreach ($all as $item): ?>
            <span class="rec-marquee-item"><b>◆</b> <?= $item ?></span>
        <?php endforeach; ?>
    </div>
</div>


<div class="rec-controles">
    <div class="rec-pills" id="recPills">
        <button class="rec-pill activa" data-cat="todos"
                onclick="filtrarRec('todos',this)">
            Todos · <?= count($recursos) ?>
        </button>
        <?php foreach ($categorias as $cat => $cnt): ?>
            <button class="rec-pill"
                    data-cat="<?= htmlspecialchars($cat, ENT_QUOTES) ?>"
                    onclick="filtrarRec('<?= htmlspecialchars($cat, ENT_QUOTES) ?>',this)">
                <?= htmlspecialchars(ucfirst($cat)) ?> · <?= $cnt ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="rec-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="recInput"
               placeholder="Buscar recurso…"
               oninput="buscarRec(this.value)"/>
    </div>
</div>


<div class="rec-grid" id="recGrid">

    <?php if (empty($recursos)): ?>

        <div class="rec-vacio" style="grid-column:1/-1">
            <i class="fa-solid fa-folder-open"></i>
            <p>Aún no hay recursos publicados.</p>
            <span>Vuelve pronto — hay materiales en camino.</span>
        </div>

    <?php else: ?>

        <?php foreach ($recursos as $idx => $r):
            $tipo    = $r['tipo'] ?? 'doc';
            $icono   = $icono_tipo[$tipo]  ?? '📁';
            $slab    = $clase_slab[$tipo]  ?? 'slab-doc';
            $label   = $label_tipo[$tipo]  ?? strtoupper($tipo);
            $txt_btn = $label_btn[$tipo]   ?? 'Descargar';
            $descs   = number_format($r['descargas'] ?? 0, 0, ',', '.');
            $cat_s   = htmlspecialchars($r['categoria'] ?? '', ENT_QUOTES);
            $tit_low = htmlspecialchars(mb_strtolower($r['titulo']), ENT_QUOTES);

            $delay_class = 'd' . min($idx + 1, 5);
        ?>

        <article class="rec-tarjeta rev-s <?= $delay_class ?>"
                 data-cat="<?= $cat_s ?>"
                 data-tit="<?= $tit_low ?>">

            <?php
                $thumb     = $r['ruta_thumb'] ?? '';
                $thumb_url = '';
                if ($thumb !== '') {
                    $thumb_url = (str_starts_with($thumb, 'http://') || str_starts_with($thumb, 'https://'))
                        ? $thumb
                        : URL . $thumb;
                }
            ?>
            <div class="rec-slab <?= $slab ?>">
                <?php if ($thumb_url !== ''): ?>
                    <img class="rec-slab-preview"
                         src="<?= htmlspecialchars($thumb_url) ?>"
                         alt=""
                         loading="lazy"
                         onerror="this.style.display='none'">
                <?php endif; ?>
                <span class="rec-slab-badge"><?= $label ?></span>
                <span class="rec-slab-icono" aria-hidden="true"><?php if ($thumb_url === '') echo $icono; ?></span>
            </div>

            <div class="rec-body">
                <h3 class="rec-card-titulo">
                    <?= htmlspecialchars($r['titulo']) ?>
                </h3>

                <?php if (!empty($r['descripcion'])): ?>
                    <p class="rec-card-desc">
                        <?= htmlspecialchars($r['descripcion']) ?>
                    </p>
                <?php endif; ?>

                <div class="rec-card-meta">
                    <?php if (!empty($r['categoria'])): ?>
                        <span>
                            <i class="fa-solid fa-tag"></i>
                            <?= htmlspecialchars(ucfirst($r['categoria'])) ?>
                        </span>
                    <?php endif; ?>
                    <span>
                        <i class="fa-solid fa-download"></i>
                        <?= $descs ?>
                    </span>
                </div>

                <a href="<?= URL ?>recursos?descargar=<?= (int)$r['id'] ?>"
                   class="rec-btn">
                    <i class="fa-solid <?= ($tipo === 'vid') ? 'fa-play' : 'fa-download' ?>"></i>
                    <?= $txt_btn ?>
                </a>
            </div>

        </article>

        <?php endforeach; ?>
    <?php endif; ?>

</div>


<?php include __DIR__ . '/componentes/footer.php'; ?>

<input type="hidden" id="_recCat" value="todos"/>

<script>
function filtrarRec(cat, el) {
    document.querySelectorAll('.rec-pill').forEach(p => p.classList.remove('activa'));
    if (el) el.classList.add('activa');
    document.getElementById('_recCat').value = cat;
    _aplicar(cat, document.getElementById('recInput').value);
}
function buscarRec(val) {
    const cat = document.getElementById('_recCat').value;
    _aplicar(cat, val);
}
function _aplicar(cat, val) {
    const t = val.trim().toLowerCase();
    document.querySelectorAll('.rec-tarjeta').forEach(c => {
        const catOk = cat === 'todos' || c.dataset.cat === cat;
        const txtOk = !t || c.dataset.tit.includes(t);
        const show  = catOk && txtOk;
        c.style.display = show ? '' : 'none';
        if (show) {
            c.classList.remove('in');
            requestAnimationFrame(() => c.classList.add('in'));
        }
    });
}

const revObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('in');
            revObs.unobserve(e.target);
        }
    });
}, { threshold: 0.12 });

document.querySelectorAll('.rev, .rev-l, .rev-s').forEach(el => revObs.observe(el));
</script>

</body>
</html>
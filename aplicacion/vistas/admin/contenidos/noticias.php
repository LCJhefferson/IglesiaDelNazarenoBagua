<?php
use aplicacion\controladores\NoticiaController;
$controller = new NoticiaController();

if(isset($_GET['eliminar_foto'])){
    if($controller->eliminarFotoGaleria($_GET['eliminar_foto'])) {
        echo "ok";
    } else {
        echo "error";
    }
    exit; 
}

if(isset($_GET['eliminar'])){
    $controller->eliminarNoticia($_GET['eliminar']);
}

if(isset($_POST['guardar'])){
    $controller->guardarNoticia(); 
}

$noticias = $controller->mostrarNoticias();
$total = count($noticias);
$fecha_actual = date("Y-m-d\TH:i");
?>

<link rel="stylesheet" href="css/noticias.css">

<!-- TOP BAR -->
<div class="top-bar">
    <div class="top-bar-left">
        <h2><i class="fa-solid fa-newspaper"></i> Panel de Noticias</h2>
        <span class="badge-contador" id="badge-total">0</span>
        <span class="badge-total-real" style="display:none"><?= $total ?></span>
    </div>
    <div class="top-bar-right">
        <button class="btn-tema" id="btn-tema" onclick="toggleTema()" title="Cambiar tema">
            <i class="fa-solid fa-moon" id="icono-tema"></i>
        </button>
        <button class="btn-nuevo" onclick="abrirModal()">
            <i class="fa-solid fa-plus"></i> Nueva Noticia
        </button>
    </div>
</div>

<!-- CONTENEDOR PRINCIPAL -->
<div class="contenedor">
    <div class="tabla-container-wrapper">
        <div class="tabla-header">
            <div class="buscador-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="buscar-noticia" placeholder="Buscar noticia por título o resumen..." onkeyup="filtrarNoticias()">
            </div>
        </div>

        <!-- SKELETON LOADER -->
        <div class="cards-grid" id="skeleton-grid">
            <?php for($i = 0; $i < 3; $i++): ?>
            <div class="skeleton-card">
                <div class="skeleton-img shimmer"></div>
                <div class="skeleton-body">
                    <div class="skeleton-line short shimmer"></div>
                    <div class="skeleton-line shimmer"></div>
                    <div class="skeleton-line medium shimmer"></div>
                </div>
                <div class="skeleton-footer shimmer"></div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- GRID DE CARDS -->
        <div class="cards-grid" id="contenedor-noticias" style="display:none;">

            <div class="sin-resultados-busqueda" id="msg-sin-busqueda" style="display:none;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <p>No se encontraron noticias</p>
                <span>Intenta con otras palabras</span>
            </div>

            <?php if(empty($noticias)): ?>
                <div class="sin-resultados">
                    <i class="fa-solid fa-inbox"></i>
                    <p>No hay noticias registradas.</p>
                    <button class="btn-nuevo" onclick="abrirModal()" style="margin-top:16px;">
                        <i class="fa-solid fa-plus"></i> Crear primera noticia
                    </button>
                </div>
            <?php endif; ?>

            <?php foreach($noticias as $i => $n): ?>
            <div class="noticia-card"
                 data-titulo="<?= htmlspecialchars(strtolower($n['titulo'])) ?>"
                 data-resumen="<?= htmlspecialchars(strtolower($n['resumen'])) ?>"
                 data-id="<?= $n['id'] ?>"
                 style="animation-delay: <?= $i * 0.07 ?>s"
                 onclick='seleccionarCard(this, <?= json_encode($n) ?>)'>

                <div class="card-imagen">
                    <?php if(!empty($n['imagen_portada'])): ?>
                        <img src="/IglesiaDelNazarenoBagua/<?= htmlspecialchars($n['imagen_portada']) ?>" alt="Portada">
                    <?php else: ?>
                        <div class="card-imagen-placeholder">
                            <i class="fa-solid fa-image"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-overlay"></div>
                    <span class="card-badge <?= $n['estado'] == 1 ? 'publicado' : 'borrador' ?>">
                        <?= $n['estado'] == 1 ? 'Publicado' : 'Borrador' ?>
                    </span>
                </div>

                <div class="card-body">
                    <span class="card-fecha">
                        <i class="fa-regular fa-calendar"></i>
                        <?= date("d/m/Y H:i", strtotime($n['fecha_creacion'])) ?>
                    </span>
                    <h3 class="card-titulo"><?= htmlspecialchars($n['titulo']) ?></h3>
                    <p class="card-resumen"><?= htmlspecialchars(mb_substr($n['resumen'], 0, 90, 'UTF-8')) ?>...</p>
                </div>

                <div class="card-acciones" onclick="event.stopPropagation()">
                    <button class="btn-accion editar" onclick='editarNoticia(<?= json_encode($n) ?>)'>
                        <i class="fa-solid fa-pen"></i> Editar
                        <span class="tooltip">Editar noticia</span>
                    </button>
                    <button class="btn-accion eliminar"
                        onclick="event.stopPropagation(); confirmarEliminar(<?= $n['id'] ?>, '<?= htmlspecialchars($n['titulo'], ENT_QUOTES) ?>')">
                        <i class="fa-solid fa-trash"></i> Eliminar
                        <span class="tooltip">Eliminar noticia</span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PREVIEW LATERAL -->
    <div class="preview">
        <div class="card-preview">
            <img id="preview-img" src="https://via.placeholder.com/400x200">
            <div class="card-body-preview">
                <h3 id="preview-titulo">Selecciona una noticia</h3>
                <p id="preview-resumen">Aquí aparecerá el resumen de la noticia seleccionada.</p>
                <button class="btn-leer">Ir a la noticia completa</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINAR -->
<div class="modal" id="modal-confirmar" style="display:none;">
    <div class="modal-box confirmar-box">
        <div class="confirmar-icono">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="confirmar-titulo">¿Eliminar noticia?</h3>
        <p class="confirmar-texto">Estás a punto de eliminar: <br><strong id="confirmar-nombre"></strong></p>
        <div class="confirmar-acciones">
            <button class="cancelar" onclick="cerrarConfirmar()">Cancelar</button>
            <button class="btn-confirmar-eliminar" id="btn-confirmar-ok">
                <i class="fa-solid fa-trash"></i> Sí, eliminar
            </button>
        </div>
    </div>
</div>

<!-- MODAL CREAR / EDITAR -->
<div class="modal" id="modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modal-titulo"><i class="fa-solid fa-plus"></i> Nueva Noticia</h3>
            <button type="button" class="cerrar" onclick="cerrarModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="form-noticia">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
            <input type="hidden" name="id" id="id_noticia">
            <input type="hidden" name="imagen_actual" id="imagen_actual">

            <div class="form-grid">
                <div class="field">
                    <label for="f-titulo">Título de la noticia</label>
                    <input type="text" name="titulo" id="f-titulo" placeholder="Ej: Evento de jóvenes" required>
                </div>

                <div class="field">
                    <label for="f-fecha">Fecha de publicación</label>
                    <input type="datetime-local" name="fecha" id="f-fecha" value="<?= $fecha_actual ?>">
                </div>

                <div class="field full">
                    <label for="f-resumen">
                        Resumen corto
                        <span class="char-contador" id="char-resumen">0 / 150</span>
                    </label>
                    <textarea name="resumen" id="f-resumen" rows="2"
                              placeholder="Escribe un breve resumen..."
                              maxlength="150"
                              oninput="contarCaracteres('f-resumen','char-resumen',150)"></textarea>
                </div>

                <div class="field full">
                    <label>Imagen de portada</label>
                    <div id="contenedor-portada-edit" style="display:none; margin-bottom:10px; position:relative; width:fit-content;">
                        <img id="img-edit-preview" src="" style="width:150px; border-radius:8px; border:1px solid #ddd;">
                        <button type="button" onclick="quitarImagenActual()" style="position:absolute; top:-5px; right:-5px; background:var(--rojo); color:white; border-radius:50%; border:none; width:25px; height:25px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-weight:bold;">&times;</button>
                    </div>
                    <label class="upload-box" for="imagen" id="label-upload"
                           ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="dropImagen(event)">
                        <input type="file" name="imagen" id="imagen" accept="image/*" hidden>
                        <i class="fa-solid fa-cloud-arrow-up" id="icono-upload"></i>
                        <span id="txt-imagen">Arrastra una imagen aquí o haz clic para subir</span>
                    </label>
                </div>

                <div class="field full">
                    <label for="f-contenido">Contenido extendido</label>
                    <textarea name="contenido" id="f-contenido" rows="4" placeholder="Escribe el cuerpo de la noticia..."></textarea>
                </div>

                <div class="field full">
                    <label for="f-video">Enlace de video (YouTube/Vimeo)</label>
                    <input type="text" name="video" id="f-video" placeholder="https://youtube.com/...">
                </div>

                <div class="field full">
                    <label>Galería de imágenes adicionales</label>
                    <label class="upload-box multi" for="imagenes"
                           ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="dropGaleria(event)">
                        <input type="file" name="imagenes[]" id="imagenes" multiple hidden>
                        <i class="fa-solid fa-images"></i>
                        <span id="txt-multi">Arrastra imágenes aquí o haz clic para añadir</span>
                    </label>
                    <ul id="lista-imagenes" class="lista-adjuntos"></ul>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="guardar" name="guardar" id="btn-submit-noticia">
                    <i class="fa-solid fa-save"></i> <span>Guardar Publicación</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TOAST NOTIFICATIONS -->
<div id="toast-container"></div>

<script src="js/noticias.js"></script>
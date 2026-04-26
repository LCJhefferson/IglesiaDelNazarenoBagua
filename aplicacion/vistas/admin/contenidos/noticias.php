<?php
use controladores\NoticiaController;
$controller = new NoticiaController();

if(isset($_GET['eliminar_foto'])){
    if($controller->eliminarFotoGaleria($_GET['eliminar_foto'])) {
        echo "ok"; // Respuesta para el fetch
    } else {
        echo "error";
    }
    exit; 
}
// Detectar eliminación ANTES de mostrar la lista
if(isset($_GET['eliminar'])){
    $controller->eliminarNoticia($_GET['eliminar']);
}

// Si se envía el formulario (Guardar/Actualizar)
if(isset($_POST['guardar'])){
    $controller->guardarNoticia(); 
}

$noticias = $controller->mostrarNoticias();
$fecha_actual = date("Y-m-d\TH:i");
?>


<link rel="stylesheet" href="css/noticias.css">
<div class="top-bar">
    <div class="top-bar-left">
        <h2><i class="fa-solid fa-newspaper"></i> Panel de Noticias</h2>
    </div>
    <div class="top-bar-right">
        <button class="btn-nuevo" onclick="abrirModal()">
            <i class="fa-solid fa-plus"></i> Nueva Noticia
        </button>
    </div>
</div>

<div class="contenedor">
    <div class="tabla-container-wrapper">
        <div class="tabla-header">
            <div class="buscador-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="buscar-noticia" placeholder="Buscar noticia por título o resumen..." onkeyup="filtrarNoticias()">
            </div>
        </div>

        <div class="tabla-box">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Resumen</th>
                        <th>Fecha de Publicación</th> <th style="text-align:right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($noticias)): ?>
                        <tr><td colspan="4" style="text-align:center; padding:30px; color:#94a3b8;">No hay noticias registradas en la base de datos.</td></tr>
                    <?php endif; ?>
                    <?php foreach($noticias as $n): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($n['titulo']) ?></strong></td>
                        <td><?= htmlspecialchars(substr($n['resumen'], 0, 70)) ?>...</td>
                        <td><?= date("d/m/Y H:i", strtotime($n['fecha_creacion'])) ?></td> 
                      
                            <td style="text-align:right">
                                <div class="acciones">
                                    <button class="btn ver" onclick='verNoticia(<?= json_encode($n) ?>)' title="Vista Rápida">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button class="btn editar" onclick='editarNoticia(<?= json_encode($n) ?>)' title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                        <button class="btn eliminar" 
                                                onclick="if(confirm('¿Seguro que deseas eliminar esta noticia?')) 
                                                window.location.href='/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=noticias&eliminar=<?= $n['id'] ?>'" 
                                                title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    
                                    <button class="btn link" title="Ver en Web">
                                        <i class="fa-solid fa-share-from-square"></i>
                                    </button>
                                </div>
                            </td>
                        </td>
                    
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="preview">
        <div class="card-preview">
            <img id="preview-img" src="https://via.placeholder.com/400x200">
            <div class="card-body">
                <h3 id="preview-titulo">Selecciona una noticia</h3>
                <p id="preview-resumen">Aquí aparecerá el resumen de la noticia seleccionada para una vista rápida.</p>
                <button class="btn-leer">Ir a la noticia completa</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modal-titulo"><i class="fa-solid fa-plus"></i> Nueva Noticia</h3>
            <button type="button" class="cerrar" onclick="cerrarModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="form-noticia">
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
                    <label for="f-resumen">Resumen corto</label>
                    <textarea name="resumen" id="f-resumen" rows="2" placeholder="Escribe un breve resumen..."></textarea>
                </div>

                <div class="field full">
                    <label>Imagen de portada</label>
                    <div id="contenedor-portada-edit" style="display:none; margin-bottom:10px; position:relative; width:fit-content;">
                        <img id="img-edit-preview" src="" style="width:150px; border-radius:8px; border:1px solid #ddd;">
                        <button type="button" onclick="quitarImagenActual()" style="position:absolute; top:-5px; right:-5px; background:var(--rojo); color:white; border-radius:50%; border:none; width:25px; height:25px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-weight:bold;">&times;</button>
                    </div>
                    
                    <label class="upload-box" for="imagen" id="label-upload">
                        <input type="file" name="imagen" id="imagen" accept="image/*" hidden>
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span id="txt-imagen">Hacer clic para subir la imagen principal</span>
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
                    <label class="upload-box multi" for="imagenes">
                        <input type="file" name="imagenes[]" id="imagenes" multiple hidden>
                        <i class="fa-solid fa-images"></i>
                        <span id="txt-multi">Hacer clic para añadir varias fotos</span>
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

<script src="js/noticias.js"></script>
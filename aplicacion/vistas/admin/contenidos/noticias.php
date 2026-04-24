<?php
$archivo = "noticias.json";

/* ===== FUNCIONES DE DATOS ===== */
function leerDatos($archivo){
    if (!file_exists($archivo)) {
        file_put_contents($archivo, json_encode([]));
    }
    return json_decode(file_get_contents($archivo), true);
}

function guardarDatos($archivo, $datos){
    file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT));
}

$noticias = leerDatos($archivo);

/* ===== CARPETA DE SUBIDAS ===== */
$carpeta = __DIR__ . "/uploads/";
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

/* ===== PROCESAR FORMULARIO (GUARDAR/EDITAR) ===== */
if(isset($_POST['guardar'])){
    $id = $_POST['id'];

    if(empty($id)){
        $id = !empty($noticias) ? max(array_column($noticias,'id')) + 1 : 1;
    } else {
        $noticias = array_filter($noticias, function($n) use ($id) {
            return $n['id'] != $id;
        });
    }

    // Procesar Portada
    $portada = $_POST['imagen_actual'] ?? "";
    if(!empty($_FILES['imagen']['name'])){
        $nombre = time() . "_" . $_FILES['imagen']['name'];
        move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta.$nombre);
        $portada = "uploads/".$nombre;
    }

    // Procesar Galería (Múltiple)
    $imagenes = []; 
    if(!empty($_FILES['imagenes']['name'][0])){
        foreach($_FILES['imagenes']['name'] as $k=>$n){
            if($_FILES['imagenes']['error'][$k]==0){
                $nombre = time()."_".$k."_".$n;
                move_uploaded_file($_FILES['imagenes']['tmp_name'][$k], $carpeta.$nombre);
                $imagenes[] = "uploads/".$nombre;
            }
        }
    }

    $noticias[] = [
        "id" => (int)$id,
        "titulo" => $_POST['titulo'],
        "resumen" => $_POST['resumen'],
        "contenido" => $_POST['contenido'],
        "fecha" => $_POST['fecha'],
        "imagen" => $portada,
        "imagenes" => $imagenes,
        "video" => $_POST['video']
    ];

    guardarDatos($archivo, $noticias);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$fecha_actual = date("Y-m-d\TH:i");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Noticias</title>
    <link rel="stylesheet" href="css/noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

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
                        <th>Portada</th>
                        <th>Título</th>
                        <th>Resumen</th>
                        <th style="text-align:right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($noticias)): ?>
                        <tr><td colspan="4" style="text-align:center; padding:30px; color:#94a3b8;">No hay noticias registradas.</td></tr>
                    <?php endif; ?>
                    <?php foreach($noticias as $n): ?>
                    <tr>
                        <td>
                            <img src="<?= $n['imagen'] ?: 'https://via.placeholder.com/50' ?>" width="55" height="40" style="border-radius:6px; object-fit:cover;">
                        </td>
                        <td><strong><?= htmlspecialchars($n['titulo']) ?></strong></td>
                        <td><?= htmlspecialchars(substr($n['resumen'], 0, 70)) ?>...</td>
                        <td style="text-align:right">
                            <div class="acciones">
                                <button class="btn ver" onclick='verNoticia(<?= json_encode($n) ?>)' title="Vista Rápida"><i class="fa-solid fa-eye"></i></button>
                                <button class="btn editar" onclick='editarNoticia(<?= json_encode($n) ?>)' title="Editar"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn eliminar" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                                <button class="btn link" title="Ver en Web"><i class="fa-solid fa-share-from-square"></i></button>
                            </div>
                        </td>
                    </tr>
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
                    <input type="text" name="titulo" id="f-titulo" placeholder="Ej: Sacamos 20 en el proyecto :)" required>
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
                    <label class="upload-box" for="imagen">
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
                        <span id="txt-multi">Hacer clic para añadir varias fotos a la galería</span>
                    </label>
                    <ul id="lista-imagenes" class="lista-adjuntos"></ul>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="guardar" name="guardar">
                    <i class="fa-solid fa-save"></i> Guardar Publicación
                </button>
            </div>
        </form>
    </div>
</div>

<script src="js/noticias.js"></script>

</body>
</html>
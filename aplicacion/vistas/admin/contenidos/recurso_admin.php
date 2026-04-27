<?php
use aplicacion\controladores\RecursoController;

$controller = new RecursoController();

// ── PROCESAR ACCIONES ──
if (isset($_GET['eliminar']))            $controller->eliminar((int)$_GET['eliminar']);
if (isset($_GET['restaurar']))           $controller->restaurar((int)$_GET['restaurar']);
if (isset($_GET['eliminar_definitivo'])) $controller->eliminarDefinitivo((int)$_GET['eliminar_definitivo']);
if (isset($_GET['descargar']))           $controller->descargar((int)$_GET['descargar']);
if (isset($_POST['guardar']))            $controller->guardar();

// ── OBTENER DATOS DE LA BD ──
$archivos = $controller->listar();
$papelera = $controller->listarPapelera();

// ── CONTADORES ──
$total_archivos = count($archivos);
$total_imagenes = count(array_filter($archivos, fn($a) => $a['tipo'] === 'img'));
$total_pdfs     = count(array_filter($archivos, fn($a) => $a['tipo'] === 'pdf'));
$total_videos   = count(array_filter($archivos, fn($a) => $a['tipo'] === 'vid'));

// ── MAPAS ──
$icono_tipo    = ['pdf' => '📄', 'img' => '🖼️', 'vid' => '🎬', 'doc' => '📝'];
$clase_tipo    = ['pdf' => 'pdf', 'img' => 'img', 'vid' => 'vid', 'doc' => 'doc'];
$etiqueta_tipo = ['pdf' => 'PDF', 'img' => 'IMG', 'vid' => 'VIDEO', 'doc' => 'DOC'];

// ── MENSAJE DE ÉXITO ──
$mensajes_exito = [
    1 => 'Archivo guardado correctamente.',
    2 => 'Archivo movido a la papelera.',
    3 => 'Archivo restaurado correctamente.',
    4 => 'Archivo eliminado definitivamente.',
    5 => 'Descarga iniciada.',
];
$msg_exito = isset($_GET['exito']) ? ($mensajes_exito[(int)$_GET['exito']] ?? '') : '';

// ── TARJETA ADMIN ──
function tarjeta_archivo(array $archivo): string {
    global $icono_tipo, $clase_tipo, $etiqueta_tipo;
    $tipo     = $archivo['tipo'];
    $icono    = $icono_tipo[$tipo]    ?? '📁';
    $clase    = $clase_tipo[$tipo]    ?? '';
    $etiqueta = $etiqueta_tipo[$tipo] ?? strtoupper($tipo);
    $id       = (int)$archivo['id'];

    // Datos para el modal de edición (escapados para JS)
    $js_titulo      = addslashes(htmlspecialchars($archivo['titulo'],      ENT_QUOTES));
    $js_descripcion = addslashes(htmlspecialchars($archivo['descripcion'], ENT_QUOTES));
    $js_categoria   = addslashes(htmlspecialchars($archivo['categoria'],   ENT_QUOTES));
    $js_tipo        = addslashes($archivo['tipo']);
    $js_ruta        = addslashes($archivo['ruta_archivo']   ?? '');
    $js_youtube     = addslashes($archivo['enlace_youtube'] ?? '');

    return '
    <div class="tarjeta-archivo">
        <div class="miniatura-archivo ' . $clase . '">
            ' . $icono . '
            <span class="etiqueta-archivo etiqueta-' . $tipo . '">' . $etiqueta . '</span>
        </div>
        <div class="info-archivo">
            <div class="nombre-archivo">' . htmlspecialchars($archivo['titulo']) . '</div>
            <div class="meta-archivo">' . htmlspecialchars($archivo['categoria']) . ' · ' . htmlspecialchars($archivo['fecha_creacion']) . '</div>
            <div class="acciones-archivo">
<a href="/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=recurso_admin&descargar=' . $id . '"                   class="boton boton-contorno" title="Descargar">
                    <i class="fa-solid fa-download"></i>
                </a>
                

                <button class="boton boton-primario" title="Editar"
                    onclick="abrirModalEditar(
                        ' . $id . ',
                        \'' . $js_titulo . '\',
                        \'' . $js_descripcion . '\',
                        \'' . $js_categoria . '\',
                        \'' . $js_tipo . '\',
                        \'' . $js_ruta . '\',
                        \'' . $js_youtube . '\'
                    )">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="boton boton-peligro" title="Mover a papelera"
                    onclick="confirmarEliminar(' . $id . ', \'' . $js_titulo . '\')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    </div>';
}

// ── TARJETA PÚBLICA ──
function tarjeta_publica(array $archivo): string {
    global $icono_tipo, $clase_tipo;
    $tipo          = $archivo['tipo'];
    $icono         = $icono_tipo[$tipo] ?? '📁';
    $clase         = $clase_tipo[$tipo] ?? '';
    $es_video      = $tipo === 'vid';
    $descargas_fmt = number_format($archivo['descargas'], 0, ',', '.');
    $superposicion = $es_video
        ? '<div class="superposicion-play"><div class="boton-play"><i class="fa-solid fa-play"></i></div></div>'
        : '';

    return '
    <div class="tarjeta-publica">
        <div class="miniatura-publica ' . $clase . '">' . $icono . $superposicion . '</div>
        <div class="cuerpo-publico">
            <div class="nombre-publico">'     . htmlspecialchars($archivo['titulo'])      . '</div>
            <div class="descripcion-publica">' . htmlspecialchars($archivo['descripcion']) . '</div>
            <div class="meta-publica">
                <span>⬇ ' . $descargas_fmt . ' descargas</span>
            </div>
            <a href="/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=recurso_admin&descargar=' . (int)$archivo['id'] . '" class="boton-descarga">

            <i class="fa-solid fa-download"></i> DESCARGAR
            </a>
        </div>
    </div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Gestor de Archivos</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  
</head>
<body>

<?php if ($msg_exito): ?>
<script>
    window.addEventListener('DOMContentLoaded', () => mostrarAviso(<?= json_encode($msg_exito) ?>, 'exito'));
</script>
<?php endif; ?>

<button class="boton-hamburguesa" id="btnHamburguesa" onclick="alternarMenu()" aria-label="Abrir menú">
    <span class="raya-menu"></span>
    <span class="raya-menu"></span>
    <span class="raya-menu"></span>
</button>

<div class="fondo-oscuro" id="fondoOscuro" onclick="cerrarMenu()"></div>

<nav class="panel-menu" id="panelMenu">
    <div class="etiqueta-seccion">Principal</div>
    <button class="opcion-menu activo" id="op-publico" onclick="mostrarPagina('publico')">
        <i class="fa-solid fa-globe"></i> Vista Pública
    </button>
    <div class="etiqueta-seccion">Administración</div>
    <button class="opcion-menu" id="op-subir" onclick="mostrarPagina('subir')">
        <i class="fa-solid fa-cloud-arrow-up"></i> Subir Archivo
    </button>
    <button class="opcion-menu" id="op-archivos" onclick="mostrarPagina('archivos')">
        <i class="fa-solid fa-folder-open"></i> Mis Archivos
    </button>
    <button class="opcion-menu" id="op-papelera" onclick="mostrarPagina('papelera')">
        <i class="fa-solid fa-trash-can"></i> Papelera
        <?php if (count($papelera) > 0): ?>
            <span style="background:var(--peligro);color:#fff;border-radius:20px;padding:1px 7px;font-size:.68rem;margin-left:auto;"><?= count($papelera) ?></span>
        <?php endif; ?>
    </button>
</nav>

<header class="barra-superior">
    <div class="barra-izquierda">
        <button class="boton boton-primario" onclick="mostrarPagina('subir')">
            <i class="fa-solid fa-plus"></i> Subir archivo
        </button>
        <h1 id="tituloPagina">Vista Pública</h1>
    </div>
</header>

<main class="area-contenido">

    <!-- ── VISTA PÚBLICA ── -->
    <div class="pagina activa" id="pagina-publico">
        <div class="cabecera-publica">
            <div class="logo-publico"><i class="fa-solid fa-dove"></i></div>
            <div class="titulo-publico">
                <h2>Recursos Disponibles</h2>
                <p>Descarga materiales y recursos compartidos</p>
            </div>
        </div>
        <div class="cuadricula-publica">
            <?php foreach ($archivos as $archivo): ?>
                <?= tarjeta_publica($archivo) ?>
            <?php endforeach; ?>
            <?php if (empty($archivos)): ?>
                <p style="color:var(--texto-suave);font-size:.9rem;">No hay recursos publicados aún.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── MIS ARCHIVOS ── -->
    <div class="pagina" id="pagina-archivos">
        <div class="barra-busqueda">
            <input type="text" placeholder="🔍 Buscar archivos..." oninput="filtrarArchivos(this.value)"/>
            <select class="selector-filtro" onchange="filtrarPorTipo(this.value)">
                <option value="todos">Todos los tipos</option>
                <option value="pdf">PDF</option>
                <option value="img">Imágenes</option>
                <option value="vid">Videos</option>
                <option value="doc">Documentos</option>
            </select>
        </div>
        <div class="cuadricula-archivos" id="todosArchivos">
            <?php foreach ($archivos as $archivo): ?>
                <?= tarjeta_archivo($archivo) ?>
            <?php endforeach; ?>
            <?php if (empty($archivos)): ?>
                <p style="color:var(--texto-suave);font-size:.9rem;">No hay archivos registrados.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── SUBIR ARCHIVO ── -->
    <div class="pagina" id="pagina-subir">
        <div class="contenedor-subida">
            <form class="tarjeta-formulario" method="POST" enctype="multipart/form-data">
                <h2 id="tituloFormulario">📤 Subir nuevo archivo</h2>
                <input type="hidden" name="id"           id="campoId">
                <input type="hidden" name="ruta_actual"  id="campoRutaActual">
                <input type="hidden" name="tipo_actual"  id="campoTipoActual">

                <div class="grupo-formulario">
                    <label>Título del archivo</label>
                    <input type="text" name="titulo" id="campoTitulo" placeholder="Ingrese el título..." oninput="actualizarPrevista()"/>
                </div>
                <div class="grupo-formulario">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="campoDescripcion" placeholder="Ingrese una descripción..." oninput="actualizarPrevista()"></textarea>
                </div>
                <div class="grupo-formulario">
                    <label>Categoría</label>
                    <select name="categoria" id="campoCategoria">
                        <option value="">Seleccionar categoría</option>
                        <option value="documentos">Documentos</option>
                        <option value="imagenes">Imágenes</option>
                        <option value="videos">Videos</option>
                        <option value="recursos">Recursos</option>
                    </select>
                </div>
                <div class="grupo-formulario">
                    <label>Archivo principal <span style="font-size:.75rem;color:var(--texto-suave);">(deja vacío para mantener el actual al editar)</span></label>
                    <div class="zona-arrastre" id="zonaArrastre"
                         ondragover="event.preventDefault(); this.classList.add('arrastrando')"
                         ondragleave="this.classList.remove('arrastrando')"
                         ondrop="manejarSoltado(event)"
                         onclick="document.getElementById('campoPrincipal').click()">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p>Arrastra un archivo aquí o <span>selecciona uno</span></p>
                        <p style="margin-top:6px;font-size:.75rem">PDF, imágenes, videos — Máx. 50MB</p>
                        <input type="file" id="campoPrincipal" name="archivo_principal" style="display:none" onchange="manejarSeleccion(this)"/>
                    </div>
                </div>
                <div class="grupo-formulario">
                    <label>Video (link YouTube)</label>
                    <input type="text" name="enlace_youtube" id="campoYoutube" placeholder="https://youtube.com/..."/>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" name="guardar" class="boton boton-primario" style="flex:1;justify-content:center;padding:13px">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span id="textoBotonGuardar">Publicar archivo</span>
                    </button>
                    <button type="button" class="boton boton-contorno" onclick="limpiarFormulario()" title="Limpiar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </form>

            <div class="tarjeta-previsualizar">
                <h3>👁 Vista previa</h3>
                <div class="miniatura-previa"><i class="fa-solid fa-file"></i></div>
                <div class="cuerpo-previsualizar">
                    <div class="titulo-previo" id="tituloPrevio">Título del archivo</div>
                    <div class="descripcion-previa" id="descripcionPrevia">La descripción aparecerá aquí...</div>
                </div>
                <div class="boton-previo"> DESCARGAR →</div>
            </div>
        </div>
    </div>

    <!-- ── PAPELERA ── -->
    <div class="pagina" id="pagina-papelera">
        <?php if (empty($papelera)): ?>
            <div class="papelera-vacia">
                <i class="fa-solid fa-trash-can"></i>
                <p style="font-size:1rem;font-weight:600">Papelera vacía</p>
                <p style="font-size:.85rem;margin-top:6px">Los archivos eliminados aparecerán aquí.</p>
            </div>
        <?php else: ?>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <p style="font-size:.85rem;color:var(--texto-suave);">
                    <i class="fa-solid fa-info-circle"></i>
                    <?= count($papelera) ?> archivo(s) en la papelera
                </p>
            </div>
            <div class="cuadricula-papelera">
                <?php foreach ($papelera as $item): 
                    $tipo_p  = $item['tipo'] ?? 'doc';
                    $icono_p = $icono_tipo[$tipo_p] ?? '📁';
                    $js_nombre_p = addslashes(htmlspecialchars($item['titulo'], ENT_QUOTES));
                ?>
                    <div class="tarjeta-papelera">
                        <div class="icono-papelera"><?= $icono_p ?></div>
                        <div class="info-papelera">
                            <div class="nombre-papelera"><?= htmlspecialchars($item['titulo']) ?></div>
                            <div class="meta-papelera">
                                <?= htmlspecialchars($item['categoria'] ?? '') ?><br>
                                <i class="fa-solid fa-clock" style="font-size:.68rem"></i>
                                Eliminado: <?= htmlspecialchars($item['fecha_eliminacion']) ?>
                            </div>
                            <div class="acciones-papelera">
                                <a href="/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=recurso_admin&restaurar=<?= (int)$item['id'] ?>"
                                    class="boton boton-exito" title="Restaurar">
                                        <i class="fa-solid fa-rotate-left"></i> Restaurar
                                    </a>
                                <button class="boton boton-peligro" title="Eliminar definitivamente"
                                    onclick="confirmarEliminarDefinitivo(<?= (int)$item['id'] ?>, '<?= $js_nombre_p ?>')">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </button>
                                   
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- ── MODAL EDITAR ── -->
<div class="superposicion-modal" id="modalEditar">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalEditar()">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <h3>✏️ Editar archivo</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id"          id="editarId">
            <input type="hidden" name="ruta_actual" id="editarRuta">
            <input type="hidden" name="tipo_actual" id="editarTipoActual">

            <div class="grupo-formulario">
                <label>Título</label>
                <input type="text" name="titulo" id="editarTitulo" required/>
            </div>
            <div class="grupo-formulario">
                <label>Descripción</label>
                <textarea name="descripcion" id="editarDescripcion"></textarea>
            </div>
            <div class="grupo-formulario">
                <label>Categoría</label>
                <select name="categoria" id="editarCategoria">
                    <option value="documentos">Documentos</option>
                    <option value="imagenes">Imágenes</option>
                    <option value="videos">Videos</option>
                    <option value="recursos">Recursos</option>
                </select>
            </div>
            <div class="grupo-formulario">
                <label>Reemplazar archivo <span style="font-size:.75rem;color:var(--texto-suave);">(opcional)</span></label>
                <input type="file" name="archivo_principal" style="padding:8px;border-radius:8px;border:1.5px solid var(--borde);background:var(--fondo);width:100%;cursor:pointer;"/>
            </div>
            <div class="grupo-formulario">
                <label>Enlace YouTube</label>
                <input type="text" name="enlace_youtube" id="editarYoutube" placeholder="https://youtube.com/..."/>
            </div>
            <div class="fila-botones-modal">
                <button type="button" class="boton boton-contorno" onclick="cerrarModalEditar()">Cancelar</button>
                <button type="submit" name="guardar" class="boton boton-primario">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── MODAL CONFIRMACIÓN ELIMINAR (papelera) ── -->
<div class="superposicion-modal" id="modalConfirmarEliminar">
    <div class="caja-modal-confirm">
        <div class="icono-confirm">🗑️</div>
        <h3>¿Mover a la papelera?</h3>
        <p id="textoConfirmarEliminar">El archivo se moverá a la papelera y podrás restaurarlo después.</p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button class="boton boton-contorno" onclick="cerrarModalConfirmar()">Cancelar</button>
            <a id="enlaceConfirmarEliminar" href="#" class="boton boton-peligro">
                <i class="fa-solid fa-trash"></i> Mover a papelera
            </a>
        </div>
    </div>
</div>

<!-- ── MODAL CONFIRMACIÓN ELIMINAR DEFINITIVO ── -->
<div class="superposicion-modal" id="modalConfirmarDefinitivo">
    <div class="caja-modal-confirm">
        <div class="icono-confirm">⚠️</div>
        <h3>¿Eliminar definitivamente?</h3>
        <p id="textoConfirmarDefinitivo">Esta acción <strong>no se puede deshacer</strong>. El archivo se eliminará de forma permanente.</p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button class="boton boton-contorno" onclick="cerrarModalDefinitivo()">Cancelar</button>
            <a id="enlaceConfirmarDefinitivo" href="#" class="boton boton-peligro">
                <i class="fa-solid fa-trash"></i> Eliminar para siempre
            </a>
        </div>
    </div>
</div>

<!-- ── AVISO TOAST ── -->
<div class="aviso" id="aviso">
    <i class="fa-solid fa-circle-check"></i>
    <span id="mensajeAviso">Acción completada</span>
</div>


    
</body>
</html>
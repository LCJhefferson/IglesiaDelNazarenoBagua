<?php
use aplicacion\controladores\RecursoController;

$controller = new RecursoController();

// ── PROCESAR ACCIONES ──
if (isset($_GET['eliminar']))            $controller->eliminar((int)$_GET['eliminar']);
if (isset($_GET['restaurar']))           $controller->restaurar((int)$_GET['restaurar']);
if (isset($_GET['eliminar_definitivo'])) $controller->eliminarDefinitivo((int)$_GET['eliminar_definitivo']);
if (isset($_GET['vaciar_papelera']))     $controller->vaciarPapelera();
if (isset($_GET['descargar']))           $controller->descargar((int)$_GET['descargar']);
if (isset($_POST['guardar']))            $controller->guardar();

// ── OBTENER DATOS DE LA BD ──
$archivos = $controller->listar();
$papelera = $controller->listarPapelera();

// ── CONTADORES PARA EL HERO ──
$total_archivos = count($archivos);
$total_descargas = 0;
$descargas_semana = 0;
$contribuidores  = [];
$tiempo_semana   = strtotime('-7 days');

foreach ($archivos as $a) {
    $total_descargas += (int)($a['descargas'] ?? 0);
    if (isset($a['fecha_creacion']) && strtotime($a['fecha_creacion']) >= $tiempo_semana) {
        $descargas_semana++;
    }
    if (!empty($a['autor'])) $contribuidores[$a['autor']] = true;
}
$total_contribuidores = count($contribuidores);
// Si no hay campo "autor", al menos mostramos 1
if ($total_contribuidores === 0 && $total_archivos > 0) $total_contribuidores = 1;

// ── CATEGORÍAS ÚNICAS (para las pills) ──
$categorias_encontradas = [];
foreach ($archivos as $a) {
    $cat = $a['categoria'] ?? '';
    if ($cat === '') continue;
    if (!isset($categorias_encontradas[$cat])) $categorias_encontradas[$cat] = 0;
    $categorias_encontradas[$cat]++;
}

// ── MAPAS DE TIPO ──
$icono_tipo    = ['pdf' => '📄', 'img' => '🖼️', 'vid' => '🎬', 'doc' => '📝'];
$clase_tipo    = ['pdf' => 'pdf', 'img' => 'img', 'vid' => 'vid', 'doc' => 'doc'];
$etiqueta_tipo = ['pdf' => 'PDF', 'img' => 'IMG', 'vid' => 'VIDEO', 'doc' => 'DOC'];
$etiqueta_slab = ['pdf' => 'PDF', 'img' => 'IMAGEN', 'vid' => 'VIDEO', 'doc' => 'DOCUMENTO'];

// ── MENSAJE DE ÉXITO ──
$mensajes_exito = [
    1 => 'Archivo guardado correctamente.',
    2 => 'Archivo movido a la papelera.',
    3 => 'Archivo restaurado correctamente.',
    4 => 'Archivo eliminado definitivamente.',
    5 => 'Papelera vaciada correctamente.',
];
$msg_exito = isset($_GET['exito']) ? ($mensajes_exito[(int)$_GET['exito']] ?? '') : '';

// ── PÁGINA ACTIVA tras redirección ──
$_paginas_validas = ['publico', 'archivos', 'subir', 'papelera'];
$pagina_activa    = in_array($_GET['pagina'] ?? '', $_paginas_validas) ? $_GET['pagina'] : 'publico';

// ── RUTA BASE para enlaces ──
$ruta_base = '/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?seccion=recurso_admin';

// ── TARJETA ADMIN (Mis Archivos) ──
function tarjeta_archivo(array $archivo, string $ruta_base): string {
    global $icono_tipo, $clase_tipo, $etiqueta_tipo;
    $tipo     = $archivo['tipo'];
    $icono    = $icono_tipo[$tipo]    ?? '📁';
    $clase    = $clase_tipo[$tipo]    ?? 'doc';
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
    <div class="tarjeta-archivo" data-tipo="' . htmlspecialchars($tipo) . '">
        <div class="miniatura-archivo ' . $clase . '">
            ' . $icono . '
            <span class="etiqueta-archivo etiqueta-' . $tipo . '">' . $etiqueta . '</span>
        </div>
        <div class="info-archivo">
            <div>
                <div class="nombre-archivo">' . htmlspecialchars($archivo['titulo']) . '</div>
                <div class="meta-archivo">' . htmlspecialchars($archivo['categoria']) . ' · ' . htmlspecialchars($archivo['fecha_creacion']) . '</div>
            </div>
            <div class="acciones-archivo">
                <a href="' . $ruta_base . '&descargar=' . $id . '"
                   class="boton boton-contorno" title="Descargar">
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

// ── TARJETA PÚBLICA (estilo V2 con slab superior) ──
function tarjeta_publica(array $archivo, string $ruta_base): string {
    global $icono_tipo, $clase_tipo, $etiqueta_slab;
    $tipo          = $archivo['tipo'];
    $icono         = $icono_tipo[$tipo] ?? '📁';
    $clase         = $clase_tipo[$tipo] ?? 'por-defecto';
    $etiqueta      = $etiqueta_slab[$tipo] ?? strtoupper($tipo);
    $es_video      = $tipo === 'vid';
    $descargas_fmt = number_format($archivo['descargas'] ?? 0, 0, ',', '.');
    $categoria_attr = htmlspecialchars($archivo['categoria'] ?? '', ENT_QUOTES);

    $superposicion = $es_video
        ? '<div class="superposicion-play"><div class="boton-play"><i class="fa-solid fa-play"></i></div></div>'
        : '';

    return '
    <div class="tarjeta-publica" data-categoria="' . $categoria_attr . '">
        <div class="slab-publico ' . $clase . '">
            <span class="etiqueta-slab ' . $tipo . '">' . $etiqueta . '</span>
            ' . $icono . '
            ' . $superposicion . '
        </div>
        <div class="cuerpo-publico">
            <div class="titulo-publico">' . htmlspecialchars($archivo['titulo']) . '</div>
            <div class="descripcion-publica">' . htmlspecialchars($archivo['descripcion']) . '</div>
            <div class="meta-publica">
                <span>' . htmlspecialchars($archivo['categoria'] ?? '') . '</span>
                <span class="separador"></span>
                <span><i class="fa-solid fa-download"></i> ' . $descargas_fmt . '</span>
            </div>
            <a href="' . $ruta_base . '&descargar=' . (int)$archivo['id'] . '" class="boton-descarga-publica">
                <i class="fa-solid fa-download"></i> Descargar
            </a>
        </div>
    </div>';
}
?>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600&display=swap" rel="stylesheet"/>
<script>var RUTA_RECURSOS = <?= json_encode($ruta_base) ?>;</script>
<?php if ($pagina_activa !== 'publico'): ?>
<script>
    window.addEventListener('DOMContentLoaded', () => mostrarPagina(<?= json_encode($pagina_activa) ?>));
</script>
<?php endif; ?>

<?php if ($msg_exito): ?>
<script>
    window.addEventListener('DOMContentLoaded', () => mostrarAviso(<?= json_encode($msg_exito) ?>, 'exito'));
</script>
<?php endif; ?>

<header class="barra-superior">
    <div class="eyebrow" id="eyebrowPagina">Comunidad · Recursos</div>
    <div class="relleno"></div>
    <button class="disparador-paleta" onclick="abrirPaleta()" aria-label="Abrir menú de comandos">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>Buscar o navegar…</span>
        <div style="flex:1"></div>
        <kbd class="tecla"></kbd>
    </button>
    <button class="boton boton-primario" onclick="mostrarPagina('subir')">
        <i class="fa-solid fa-plus"></i>
        Subir archivo
    </button>
</header>

<main class="area-contenido">

    <div class="pagina activa" id="pagina-publico">


        <div class="envoltorio-hero">
            <div class="hero-editorial">

               
                <svg class="hero-paloma" viewBox="0 0 64 64" fill="none">
                    <path d="M52 16c-3 0-6 1.5-8 4-2 2-3 5-3 8 0 2 .5 4 1.5 5.5L30 46l-10-2-8 8 12-2 4 6 8-8-2-10 12-13.5C48 23 49 21 49 19c1.5-.5 3-1.5 3-3z" fill="#125680ff"/>
                </svg>
                <div class="hero-glow"></div>

                <div class="hero-contenido">
                    <div class="hero-eyebrow">
                        <span class="punto"></span>
                        Comunidad Parroquial
                    </div>
                    <h1 class="hero-titulo">Recursos Disponibles</h1>
                    <p class="hero-subtitulo">
                        Descarga materiales y recursos compartidos por la comunidad.
                        Catequesis, liturgia, música y documentación institucional en un solo lugar.
                    </p>

                    <div class="hero-stats">
                        <div class="stat-tile">
                            <div class="etiqueta">Recursos totales</div>
                            <div class="valor"><?= $total_archivos ?></div>
                        </div>
                        <div class="stat-divisor"></div>
                        <div class="stat-tile">
                            <div class="etiqueta">Descargas</div>
                            <div class="valor"><?= number_format($total_descargas, 0, ',', '.') ?></div>
                        </div>
                        <div class="stat-divisor"></div>
                        <div class="stat-tile acento">
                            <div class="etiqueta">Esta semana</div>
                            <div class="valor">+<?= $descargas_semana ?></div>
                        </div>
                        <div class="stat-divisor"></div>
                        <div class="stat-tile">
                            <div class="etiqueta">Contribuidores</div>
                            <div class="valor"><?= $total_contribuidores ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="barra-pills">
            <button class="pill activa" onclick="filtrarPorCategoria('todos', this)">
                Todos · <?= $total_archivos ?>
            </button>
            <?php foreach ($categorias_encontradas as $cat => $cnt): ?>
                <button class="pill" onclick="filtrarPorCategoria('<?= htmlspecialchars($cat, ENT_QUOTES) ?>', this)">
                    <?= htmlspecialchars(ucfirst($cat)) ?> · <?= $cnt ?>
                </button>
            <?php endforeach; ?>
            <div class="ordenar">
                Ordenar: <strong>Más recientes</strong>
            </div>
        </div>

        <div class="cuadricula-publica">
            <?php foreach ($archivos as $archivo): ?>
                <?= tarjeta_publica($archivo, $ruta_base) ?>
            <?php endforeach; ?>
            <?php if (empty($archivos)): ?>
                <p style="color:var(--texto-suave);font-size:.9rem;grid-column:1/-1;text-align:center;padding:40px;">
                    No hay recursos publicados aún.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="pagina" id="pagina-archivos">
        <div class="envoltorio-hero">
            <div class="hero-editorial">
                <svg class="hero-paloma" viewBox="0 0 64 64" fill="none">
                    <path d="M52 16c-3 0-6 1.5-8 4-2 2-3 5-3 8 0 2 .5 4 1.5 5.5L30 46l-10-2-8 8 12-2 4 6 8-8-2-10 12-13.5C48 23 49 21 49 19c1.5-.5 3-1.5 3-3z" fill="#E5B567"/>
                </svg>
                <div class="hero-glow"></div>
                <div class="hero-contenido">
                    <div class="hero-eyebrow">
                        <span class="punto"></span>
                        Administración
                    </div>
                    <h1 class="hero-titulo">Mis Archivos</h1>
                    <p class="hero-subtitulo">
                        Gestiona los recursos que has cargado. Edita, descarga o elimina con un clic.
                    </p>
                </div>
            </div>
        </div>

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
                <?= tarjeta_archivo($archivo, $ruta_base) ?>
            <?php endforeach; ?>
            <?php if (empty($archivos)): ?>
                <p style="color:var(--texto-suave);font-size:.9rem;grid-column:1/-1;text-align:center;padding:40px;">
                    No hay archivos registrados.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="pagina" id="pagina-subir">
        <div class="envoltorio-hero">
            <div class="hero-editorial">
                <svg class="hero-paloma" viewBox="0 0 64 64" fill="none">
                    <path d="M52 16c-3 0-6 1.5-8 4-2 2-3 5-3 8 0 2 .5 4 1.5 5.5L30 46l-10-2-8 8 12-2 4 6 8-8-2-10 12-13.5C48 23 49 21 49 19c1.5-.5 3-1.5 3-3z" fill="#E5B567"/>
                </svg>
                <div class="hero-glow"></div>
                <div class="hero-contenido">
                    <div class="hero-eyebrow">
                        <span class="punto"></span>
                        Administración
                    </div>
                    <h1 class="hero-titulo">Subir Archivo</h1>
                    <p class="hero-subtitulo">
                        Carga un nuevo recurso para la comunidad parroquial.
                    </p>
                </div>
            </div>
        </div>

        <div class="contenedor-subida">
            <form class="tarjeta-formulario" method="POST" enctype="multipart/form-data">
                <h2 id="tituloFormulario">📤 Subir nuevo archivo</h2>
                <input type="hidden" name="id"           id="campoId">
                <input type="hidden" name="ruta_actual"  id="campoRutaActual">
                <input type="hidden" name="tipo_actual"  id="campoTipoActual">

                <div class="grupo-formulario">
                    <label>Título del archivo</label>
                    <input type="text" name="titulo" id="campoTitulo"
                           placeholder="Ingrese el título..." oninput="actualizarPrevista()"/>
                </div>

                <div class="grupo-formulario">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="campoDescripcion"
                              placeholder="Ingrese una descripción..." oninput="actualizarPrevista()"></textarea>
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
                    <label>
                        Archivo principal
                        <span style="font-size:.75rem;color:var(--texto-suave);text-transform:none;letter-spacing:0;font-weight:400;">
                            (deja vacío para mantener el actual al editar)
                        </span>
                    </label>
                    <div class="zona-arrastre" id="zonaArrastre"
                         ondragover="event.preventDefault(); this.classList.add('arrastrando')"
                         ondragleave="this.classList.remove('arrastrando')"
                         ondrop="manejarSoltado(event)"
                         onclick="document.getElementById('campoPrincipal').click()">
                        <div class="icono-circulo">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <p>Arrastra un archivo aquí</p>
                        <p class="ayuda">
                            o <span class="enlace">selecciona uno desde tu dispositivo</span>
                        </p>
                        <p class="ayuda" style="margin-top:8px;">PDF · DOCX · IMAGEN · VIDEO — hasta 50 MB</p>
                        <input type="file" id="campoPrincipal" name="archivo_principal"
                               style="display:none" onchange="manejarSeleccion(this)"/>
                    </div>
                </div>

                <div class="grupo-formulario">
                    <label>Video (link YouTube)</label>
                    <input type="text" name="enlace_youtube" id="campoYoutube"
                           placeholder="https://youtube.com/..."/>
                </div>

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" name="guardar" class="boton boton-primario"
                            style="flex:1;justify-content:center;padding:13px;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span id="textoBotonGuardar">Publicar archivo</span>
                    </button>
                    <button type="button" class="boton boton-contorno"
                            onclick="limpiarFormulario()" title="Limpiar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </form>

            <div class="tarjeta-previsualizar">
                <h3>👁 Vista previa</h3>
                <div class="miniatura-previa"><i class="fa-solid fa-file"></i></div>
                <div class="titulo-previo" id="tituloPrevio">Título del archivo</div>
                <div class="descripcion-previa" id="descripcionPrevia">La descripción aparecerá aquí...</div>
                <div class="boton-previo">
                    <i class="fa-solid fa-download" style="margin-right:6px;"></i> DESCARGAR
                </div>
            </div>
        </div>
    </div>

    <div class="pagina" id="pagina-papelera">
        <div class="envoltorio-hero">
            <div class="hero-editorial">
                <svg class="hero-paloma" viewBox="0 0 64 64" fill="none">
                    <path d="M52 16c-3 0-6 1.5-8 4-2 2-3 5-3 8 0 2 .5 4 1.5 5.5L30 46l-10-2-8 8 12-2 4 6 8-8-2-10 12-13.5C48 23 49 21 49 19c1.5-.5 3-1.5 3-3z" fill="#E5B567"/>
                </svg>
                <div class="hero-glow"></div>
                <div class="hero-contenido">
                    <div class="hero-eyebrow">
                        <span class="punto"></span>
                        Administración
                    </div>
                    <h1 class="hero-titulo">Papelera</h1>
                    <p class="hero-subtitulo">
                        Los archivos eliminados se conservan aquí. Puedes restaurarlos o borrarlos permanentemente.
                    </p>
                </div>
            </div>
        </div>

        <div class="contenedor-papelera">
            <?php if (empty($papelera)): ?>
                <div class="papelera-vacia">
                    <i class="fa-solid fa-trash-can"></i>
                    <p style="font-size:1rem;font-weight:600;color:var(--texto);">Papelera vacía</p>
                    <p style="font-size:.85rem;margin-top:6px;">Los archivos eliminados aparecerán aquí.</p>
                </div>
            <?php else: ?>
                <div class="banner-aviso">
                    <div class="icono">!</div>
                    <div style="flex:1;">
                        <div class="texto-fuerte">
                            <?= count($papelera) ?> archivo(s) en la papelera
                        </div>
                        <div class="texto-debil">
                            Puedes restaurar cualquier recurso antes de su eliminación definitiva.
                        </div>
                    </div>
                    <button class="boton boton-peligro" onclick="confirmarVaciarPapelera()" title="Eliminar todos los archivos de la papelera">
                        <i class="fa-solid fa-trash-can"></i> Vaciar papelera
                    </button>
                </div>

                <div class="cuadricula-papelera">
                    <?php foreach ($papelera as $item):
                        $tipo_p  = $item['tipo'] ?? 'doc';
                        $icono_p = $icono_tipo[$tipo_p] ?? '📁';
                        $js_nombre_p = addslashes(htmlspecialchars($item['titulo'], ENT_QUOTES));
                    ?>
                        <div class="tarjeta-papelera">
                            <div class="icono-papelera"><?= $icono_p ?></div>
                            <div>
                                <div class="nombre-papelera"><?= htmlspecialchars($item['titulo']) ?></div>
                                <div class="meta-papelera">
                                    <?= htmlspecialchars($item['categoria'] ?? '') ?><br>
                                    <i class="fa-solid fa-clock"></i>
                                    Eliminado: <?= htmlspecialchars($item['fecha_eliminacion'] ?? '') ?>
                                </div>
                            </div>
                            <div class="acciones-papelera">
                                <a href="<?= $ruta_base ?>&restaurar=<?= (int)$item['id'] ?>"
                                   class="boton boton-exito" title="Restaurar">
                                    <i class="fa-solid fa-rotate-left"></i> Restaurar
                                </a>
                                <button class="boton boton-peligro" title="Eliminar definitivamente"
                                    onclick="confirmarEliminarDefinitivo(<?= (int)$item['id'] ?>, '<?= $js_nombre_p ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<div class="fondo-paleta" id="fondoPaleta" onclick="if(event.target===this) cerrarPaleta()">
    <div class="caja-paleta">
        <div class="fila-busqueda-paleta">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="inputPaleta" placeholder="Buscar acciones, recursos o navegar…" autocomplete="off"/>
            <kbd>esc</kbd>
        </div>

        <div class="lista-paleta">
            <div class="seccion-paleta">
                <div class="titulo-seccion-paleta">Navegación</div>
                <button class="item-paleta" data-label="Vista Pública" data-hint="Página principal de recursos"
                        onclick="mostrarPagina('publico')">
                    <div class="item-icono"><i class="fa-solid fa-globe"></i></div>
                    <div class="item-cuerpo">
                        <div class="item-label">Vista Pública</div>
                        <div class="item-hint">Página principal de recursos</div>
                    </div>
                    <div class="item-teclas">
                    </div>
                </button>
            </div>

            <div class="seccion-paleta">
                <div class="titulo-seccion-paleta">Administración</div>
                <button class="item-paleta" data-label="Subir Archivo" data-hint="Cargar un nuevo recurso a la comunidad"
                        onclick="mostrarPagina('subir')">
                    <div class="item-icono"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <div class="item-cuerpo">
                        <div class="item-label">Subir Archivo</div>
                        <div class="item-hint">Cargar un nuevo recurso a la comunidad</div>
                    </div>
                    <div class="item-teclas">
                    </div>
                </button>
                <button class="item-paleta" data-label="Mis Archivos" data-hint="Recursos cargados por ti"
                        onclick="mostrarPagina('archivos')">
                    <div class="item-icono"><i class="fa-solid fa-folder-open"></i></div>
                    <div class="item-cuerpo">
                        <div class="item-label">Mis Archivos</div>
                        <div class="item-hint"><?= $total_archivos ?> recursos cargados</div>
                    </div>
                    <div class="item-teclas">
                    </div>
                </button>
                <button class="item-paleta" data-label="Papelera" data-hint="Archivos eliminados recientemente"
                        onclick="mostrarPagina('papelera')">
                    <div class="item-icono"><i class="fa-solid fa-trash-can"></i></div>
                    <div class="item-cuerpo">
                        <div class="item-label">Papelera</div>
                        <div class="item-hint">Archivos eliminados recientemente</div>
                    </div>
                    <?php if (count($papelera) > 0): ?>
                        <span class="item-badge"><?= count($papelera) ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <div class="paleta-vacia" id="paletaVacia" style="display:none;">
                Sin resultados para tu búsqueda.
            </div>
        </div>


    </div>
</div>

<div class="superposicion-modal" id="modalEditar" onclick="if(event.target===this) cerrarModalEditar()">
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
                <label>
                    Reemplazar archivo
                    <span style="font-size:.75rem;color:var(--texto-suave);text-transform:none;letter-spacing:0;font-weight:400;">(opcional)</span>
                </label>
                <input type="file" name="archivo_principal"
                       style="padding:10px;border-radius:9px;border:1px solid var(--borde);background:var(--fondo-suave);width:100%;cursor:pointer;font-family:var(--sans);font-size:14px;"/>
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

<div class="superposicion-modal" id="modalConfirmarEliminar" onclick="if(event.target===this) cerrarModalConfirmar()">
    <div class="caja-modal-confirm">
        <div class="icono-confirm">🗑️</div>
        <h3>¿Mover a la papelera?</h3>
        <p id="textoConfirmarEliminar">El archivo se moverá a la papelera y podrás restaurarlo después.</p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button class="boton boton-contorno" onclick="cerrarModalConfirmar()">Cancelar</button>
            <a id="enlaceConfirmarEliminar" href="#" class="boton boton-peligro-solido">
                <i class="fa-solid fa-trash"></i> Mover a papelera
            </a>
        </div>
    </div>
</div>

<div class="superposicion-modal" id="modalConfirmarDefinitivo" onclick="if(event.target===this) cerrarModalDefinitivo()">
    <div class="caja-modal-confirm">
        <div class="icono-confirm">⚠️</div>
        <h3>¿Eliminar definitivamente?</h3>
        <p id="textoConfirmarDefinitivo">
            Esta acción <strong>no se puede deshacer</strong>. El archivo se eliminará de forma permanente.
        </p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button class="boton boton-contorno" onclick="cerrarModalDefinitivo()">Cancelar</button>
            <a id="enlaceConfirmarDefinitivo" href="#" class="boton boton-peligro-solido">
                <i class="fa-solid fa-trash"></i> Eliminar para siempre
            </a>
        </div>
    </div>
</div>

<div class="superposicion-modal" id="modalVaciarPapelera" onclick="if(event.target===this) cerrarModalVaciarPapelera()">
    <div class="caja-modal-confirm">
        <div class="icono-confirm">⚠️</div>
        <h3>¿Vaciar la papelera?</h3>
        <p>Esta acción <strong>no se puede deshacer</strong>. Todos los archivos de la papelera se eliminarán permanentemente.</p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button class="boton boton-contorno" onclick="cerrarModalVaciarPapelera()">Cancelar</button>
            <a id="enlaceVaciarPapelera" href="#" class="boton boton-peligro-solido">
                <i class="fa-solid fa-trash-can"></i> Vaciar todo
            </a>
        </div>
    </div>
</div>

<div class="aviso" id="aviso">
    <i class="fa-solid fa-circle-check"></i>
    <span id="mensajeAviso">Acción completada</span>
</div>


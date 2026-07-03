<?php
use aplicacion\core\Middleware;

// Genera el token CSRF seguro para las peticiones asíncronas
$csrfToken = Middleware::csrfGenerate();

// Las operaciones de borrado, guardado, etc. ahora se manejan vía Fetch API (recurso_admin.js) y RecursoApiController

$coleccion_archivos = \aplicacion\modelos\Recurso::listar();
$archivos = is_object($coleccion_archivos) && method_exists($coleccion_archivos, 'toArray') 
    ? $coleccion_archivos->toArray() 
    : $coleccion_archivos;

$coleccion_papelera = \aplicacion\modelos\RecursoPapelera::listar();
$papelera = is_object($coleccion_papelera) && method_exists($coleccion_papelera, 'toArray') 
    ? $coleccion_papelera->toArray() 
    : $coleccion_papelera;

// Mantenemos la regeneración automática de miniaturas pendientes en background
$pendientes = array_filter($archivos, fn($a) => $a['ruta_thumb'] === null);
if (!empty($pendientes)) {
    foreach ($pendientes as $a) {
        \aplicacion\services\RecursoThumbService::generar(
            (int)$a['id'],
            $a['ruta_archivo']   ?? '',
            $a['tipo']           ?? 'doc',
            $a['enlace_youtube'] ?? ''
        );
    }
    $archivos = \aplicacion\modelos\Recurso::listar();
    $archivos = is_object($archivos) && method_exists($archivos, 'toArray') ? $archivos->toArray() : $archivos;
}

// Estadísticas de arranque para la carga inicial
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
    if (!empty($a['autor_nombre'])) {
        $contribuidores[$a['autor_nombre']] = true;
    }
}
$total_contribuidores = count($contribuidores);
if ($total_contribuidores === 0 && $total_archivos > 0) $total_contribuidores = 1;

$categorias_encontradas = [];
foreach ($archivos as $a) {
    $cat = $a['categoria'] ?? '';
    if ($cat === '') continue;
    if (!isset($categorias_encontradas[$cat])) $categorias_encontradas[$cat] = 0;
    $categorias_encontradas[$cat]++;
}

$_paginas_validas = ['archivos', 'subir', 'papelera'];
$pagina_activa    = in_array($_GET['pagina'] ?? '', $_paginas_validas) ? $_GET['pagina'] : 'archivos';
$ruta_base = '/IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin';
?>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600&display=swap" rel="stylesheet"/>

<script>
    var RUTA_RECURSOS = <?= json_encode($ruta_base) ?>;
    window.addEventListener('DOMContentLoaded', () => mostrarPagina(<?= json_encode($pagina_activa) ?>));
</script>

<script>
var ARCHIVOS_DATA = <?= json_encode(array_map(fn($a) => [
    'id'        => (int)$a['id'],
    'titulo'    => $a['titulo'],
    'tipo'      => $a['tipo'],
    'categoria' => $a['categoria'] ?? '',
], $archivos)) ?>;
</script>

<header class="barra-superior_recursos">
    <div class="eyebrow" id="eyebrowPagina">Comunidad · Recursos</div>
    <div class="relleno"></div>

    <div class="wrap-busqueda" id="wrapBusqueda">
        <div class="barra-busqueda-header">
            <i class="fa-solid fa-magnifying-glass busq-icono"></i>
            <input
                type="text"
                id="inputBusqueda"
                class="input-busqueda-header"
                placeholder="Buscar archivos…"
                autocomplete="off"
                oninput="buscarRecursos(this.value)"
                onkeydown="teclasBusqueda(event)"
            />
        </div>
        <div class="dropdown-busqueda" id="dropdownBusqueda" style="display:none;"></div>
    </div>

    <div class="nav-iconos">
        <button class="nav-btn" data-pagina="archivos" onclick="mostrarPagina('archivos')" title="Mis Archivos">
            <i class="fa-solid fa-folder-open"></i>
        </button>
        <button class="nav-btn" data-pagina="papelera" onclick="mostrarPagina('papelera')" title="Papelera">
            <i class="fa-solid fa-trash-can"></i>
            <?php if (count($papelera) > 0): ?>
                <span class="nav-badge" id="badgePapeleraConteo"><?= count($papelera) ?></span>
            <?php endif; ?>
        </button>
        <button class="nav-btn nav-btn-primario" onclick="abrirModalSubir()" title="Subir archivo">
            <i class="fa-solid fa-cloud-arrow-up"></i>
        </button>
    </div>
</header>

<main class="area-contenido">

    <div class="pagina" id="pagina-publico" style="display:none;">
        <div class="envoltorio-hero">
            <div class="hero-editorial">
               <svg class="hero-paloma" viewBox="0 0 64 64" fill="none">
                    <path d="M52 16c-3 0-6 1.5-8 4-2 2-3 5-3 8 0 2 .5 4 1.5 5.5L30 46l-10-2-8 8 12-2 4 6 8-8-2-10 12-13.5C48 23 49 21 49 19c1.5-.5 3-1.5 3-3z" fill="#125680ff"/>
                </svg>
                <div class="hero-glow"></div>

                <div class="hero-contenido">
                    <div class="hero-eyebrow">
                        <span class="punto"></span>
                        Iglesia Del Nazareno
                    </div>
                    <h1 class="hero-titulo">Recursos Disponibles</h1>
                    <p class="hero-subtitulo">
                       Recursos compartidos con la congregación
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

        <div class="cuadricula-publica" id="contenedorPublico">
            </div>
    </div>

    <div class="pagina activa" id="pagina-archivos">
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
            <div class="banner-aviso" id="bannerPapelera" style="display:none;">
                <div class="icono">!</div>
                <div style="flex:1;">
                    <div class="texto-fuerte" id="textoCantidadPapelera"></div>
                    <div class="texto-debil">
                        Puedes restaurar cualquier recurso antes de su eliminación definitiva.
                    </div>
                </div>
                <button class="boton boton-peligro" onclick="confirmarVaciarPapelera()" title="Eliminar todos los archivos de la papelera">
                    <i class="fa-solid fa-trash-can"></i> Vaciar papelera
                </button>
            </div>

            <div class="lista-papelera" id="contenedorPapelera">
                </div>
        </div>
    </div>

</main>

<div class="overlay-subir" id="overlaySubir" onclick="if(event.target===this) cerrarModalSubir()">
    <div class="modal-subir" id="modalSubir" role="dialog" aria-modal="true" aria-labelledby="tituloModalSubir">

        <div class="modal-subir-header">
            <svg class="modal-subir-paloma" viewBox="0 0 64 64" fill="none">
                <path d="M52 16c-3 0-6 1.5-8 4-2 2-3 5-3 8 0 2 .5 4 1.5 5.5L30 46l-10-2-8 8 12-2 4 6 8-8-2-10 12-13.5C48 23 49 21 49 19c1.5-.5 3-1.5 3-3z" fill="#E5B567"/>
            </svg>
            <div class="modal-subir-header-texto">
                <div class="modal-subir-eyebrow">ADMINISTRACIÓN</div>
                <div class="modal-subir-titulo" id="tituloModalSubir">Subir Archivo</div>
            </div>
            <button class="modal-subir-cerrar" onclick="cerrarModalSubir()" title="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form class="modal-subir-body" method="POST" enctype="multipart/form-data" id="formSubir">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
            <input type="hidden" name="id"          id="subir_campoId">
            <input type="hidden" name="ruta_actual" id="subir_campoRutaActual">
            <input type="hidden" name="tipo_actual" id="subir_campoTipoActual">

            <div class="subir-dropzone" id="subir_zonaArrastre"
                 ondragover="event.preventDefault(); this.classList.add('arrastrando')"
                 ondragleave="this.classList.remove('arrastrando')"
                 ondrop="manejarSoltadoSubir(event)"
                 onclick="document.getElementById('subir_campoPrincipal').click()">
                <div class="subir-dropzone-circulo">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <p class="subir-dropzone-titulo">Arrastra archivos aquí</p>
                <p class="subir-dropzone-sub">o <span class="subir-enlace">elige desde tu dispositivo</span></p>
                <p class="subir-dropzone-hint">PDF · DOCX · MP3 · ZIP · IMAGEN · hasta 100 MB</p>
                <input type="file" id="subir_campoPrincipal" name="archivo_principal"
                       style="display:none" onchange="seleccionarArchivoSubir(this)"/>
            </div>

            <div class="subir-archivo-sel" id="subir_archivoSel" style="display:none;">
                <i class="fa-solid fa-file subir-archivo-icono"></i>
                <div class="subir-archivo-info">
                    <div class="subir-archivo-nombre" id="subir_archivoNombre">archivo.pdf</div>
                    <div class="subir-barra-wrap">
                        <div class="subir-barra-prog" id="subir_barraProg" style="width:0%"></div>
                    </div>
                </div>
            </div>

            <div class="subir-campos">
                <div class="subir-grupo">
                    <label>Título</label>
                    <input type="text" name="titulo" id="subir_titulo" placeholder="Ingresa el título del recurso…" required/>
                </div>
                <div class="subir-fila-2">
                    <div class="subir-grupo">
                        <label>Categoría</label>
                        <select name="categoria" id="subir_categoria" required>
                            <option value="">Seleccionar…</option>
                            <option value="documentos">Documentos</option>
                            <option value="imagenes">Imágenes</option>
                            <option value="videos">Videos</option>
                            <option value="recursos">Recursos</option>
                        </select>
                    </div>
                    <div class="subir-grupo">
                        <label>Enlace YouTube</label>
                        <input type="text" name="enlace_youtube" id="subir_youtube" placeholder="https://youtube.com/…"/>
                    </div>
                </div>
                <div class="subir-grupo">
                    <label>Descripción <span style="font-weight:400;font-size:10px;letter-spacing:0;text-transform:none;color:var(--texto-muy-suave);">(opcional)</span></label>
                    <textarea name="descripcion" id="subir_descripcion" placeholder="Describe el contenido del recurso…"></textarea>
                </div>
            </div>
        </form>

        <div class="modal-subir-footer">
            <span class="modal-subir-nota">
                <i class="fa-solid fa-bell" style="color:var(--gold);font-size:11px;"></i>
                Se publicará para toda la comunidad
            </span>
            <div style="display:flex;gap:10px;">
                <button type="button" class="boton boton-contorno" onclick="cerrarModalSubir()">Cancelar</button>
                <button type="submit" form="formSubir" class="boton boton-primario">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Publicar recurso
                </button>
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
        <form method="POST" enctype="multipart/form-data" id="formEditar">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
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
                <button type="submit" class="boton boton-primario">
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
            <button id="btnConfirmarEliminar" class="boton boton-peligro-solido">
                <i class="fa-solid fa-trash"></i> Mover a papelera
            </button>
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
            <button id="btnConfirmarDefinitivo" class="boton boton-peligro-solido">
                <i class="fa-solid fa-trash"></i> Eliminar para siempre
            </button>
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
            <button id="btnVaciarPapelera" class="boton boton-peligro-solido" onclick="vaciarPapelera()">
                <i class="fa-solid fa-trash-can"></i> Vaciar todo
            </button>
        </div>
    </div>
</div> 

<div class="aviso" id="aviso">
    <i class="fa-solid fa-circle-check"></i>
    <span id="mensajeAviso">Acción completada</span>
</div>

<div class="superposicion-modal" id="modalErrorExtension" onclick="if(event.target===this) this.style.display='none'" style="display:none; z-index: 9999;">
    <div class="caja-modal-confirm" style="border-top: 4px solid var(--rojo);">
        <div class="icono-confirm" style="color: var(--rojo);">🚫</div>
        <h3 style="color: var(--rojo);">Subida bloqueada por seguridad</h3>
        <p>
            El sistema ha rechazado el archivo porque tiene una extensión no permitida. 
            <br><br>
            <strong>Podría tratarse de un script dañino, ejecutable (.exe) o malware.</strong>
            Solo se permiten formatos seguros (PDF, Word, Imágenes y Videos).
        </p>
        <div style="display:flex;gap:10px;justify-content:center;margin-top:15px;">
            <button class="boton boton-peligro-solido" onclick="document.getElementById('modalErrorExtension').style.display='none'">Entendido</button>
        </div>
    </div>
</div>
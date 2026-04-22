<?php
// ══════════════════════════════════════════════════
//  gestor.php — Gestor de Archivos
// ══════════════════════════════════════════════════

$archivos = [
    ['id'=>1,'nombre'=>'Guía 40 días de oración','descripcion'=>'Material de oración para la temporada de pentecostés.','tipo'=>'pdf','tamano'=>'13.23 MB','fecha'=>'10 Abr 2026','descargas'=>1588],
    ['id'=>2,'nombre'=>'Gráficos Cuaresma Redes','descripcion'=>'Gráficos para redes sociales, domingo, jueves y viernes.','tipo'=>'img','tamano'=>'8.04 MB','fecha'=>'05 Abr 2026','descargas'=>1073],
    ['id'=>3,'nombre'=>'Semana Mundial de Oración','descripcion'=>'Recursos y video para la semana mundial de oración 2026.','tipo'=>'vid','tamano'=>'72.09 MB','fecha'=>'01 Mar 2026','descargas'=>2292],
    ['id'=>4,'nombre'=>'Manual de Liderazgo','descripcion'=>'Guía completa para líderes de célula y ministerios.','tipo'=>'pdf','tamano'=>'5.2 MB','fecha'=>'20 Feb 2026','descargas'=>845],
    ['id'=>5,'nombre'=>'Fotografías Evento Anual','descripcion'=>'Galería fotográfica del encuentro regional anual.','tipo'=>'img','tamano'=>'120 MB','fecha'=>'15 Ene 2026','descargas'=>430],
    ['id'=>6,'nombre'=>'Sermón Domingo de Ramos','descripcion'=>'Video del sermón especial para domingo de ramos.','tipo'=>'vid','tamano'=>'340 MB','fecha'=>'12 Abr 2026','descargas'=>670],
];

$total_archivos = count($archivos);
$total_imagenes = count(array_filter($archivos, fn($a) => $a['tipo'] === 'img'));
$total_pdfs     = count(array_filter($archivos, fn($a) => $a['tipo'] === 'pdf'));
$total_videos   = count(array_filter($archivos, fn($a) => $a['tipo'] === 'vid'));

$icono_tipo    = ['pdf'=>'📄','img'=>'🖼️','vid'=>'🎬','doc'=>'📝'];
$clase_tipo    = ['pdf'=>'pdf','img'=>'img','vid'=>'vid','doc'=>'doc'];
$etiqueta_tipo = ['pdf'=>'PDF','img'=>'IMG','vid'=>'VIDEO','doc'=>'DOC'];

function tarjeta_archivo(array $archivo, bool $mostrar_admin = true): string {
    global $icono_tipo, $clase_tipo, $etiqueta_tipo;
    $tipo=$archivo['tipo']; $icono=$icono_tipo[$tipo]??'📁';
    $clase=$clase_tipo[$tipo]??''; $etiqueta=$etiqueta_tipo[$tipo]??strtoupper($tipo);
    $botones_admin = $mostrar_admin ? '
        <button class="boton boton-primario" onclick="abrirModalEditar('.$archivo['id'].')" title="Editar"><i class="fa-solid fa-pen"></i></button>
        <button class="boton boton-peligro" onclick="mostrarAviso(\'Archivo eliminado\',\'error\')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>' : '';
    return '<div class="tarjeta-archivo">
        <div class="miniatura-archivo '.$clase.'">
            '.$icono.'<span class="etiqueta-archivo etiqueta-'.$tipo.'">'.$etiqueta.'</span>
        </div>
        <div class="info-archivo">
            <div class="nombre-archivo">'.htmlspecialchars($archivo['nombre']).'</div>
            <div class="meta-archivo">'.htmlspecialchars($archivo['tamano']).' · '.htmlspecialchars($archivo['fecha']).'</div>
            <div class="acciones-archivo">
                <button class="boton boton-contorno" onclick="mostrarAviso(\'Descargando...\',\'exito\')" title="Descargar"><i class="fa-solid fa-download"></i></button>
                '.$botones_admin.'
            </div>
        </div>
    </div>';
}

function tarjeta_publica(array $archivo): string {
    global $icono_tipo, $clase_tipo;
    $tipo=$archivo['tipo']; $icono=$icono_tipo[$tipo]??'📁';
    $clase=$clase_tipo[$tipo]??''; $es_video=$tipo==='vid';
    $descargas_fmt=number_format($archivo['descargas'],0,',','.');
    $superposicion_video = $es_video ? '<div class="superposicion-play"><div class="boton-play"><i class="fa-solid fa-play"></i></div></div>' : '';
    return '<div class="tarjeta-publica">
        <div class="miniatura-publica '.$clase.'">'.$icono.$superposicion_video.'</div>
        <div class="cuerpo-publico">
            <div class="nombre-publico">'.htmlspecialchars($archivo['nombre']).'</div>
            <div class="descripcion-publica">'.htmlspecialchars($archivo['descripcion']).'</div>
            <div class="meta-publica">
                <span>💾 '.htmlspecialchars($archivo['tamano']).'</span>
                <span>⬇ '.$descargas_fmt.' descargas</span>
            </div>
            <button class="boton-descarga" onclick="mostrarAviso(\'Iniciando descarga...\',\'exito\')">
                <i class="fa-solid fa-download"></i> MÁS INFORMACIÓN Y DESCARGA
            </button>
        </div>
    </div>';
}

$archivos_recientes = array_slice($archivos, 0, 4);
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

<!-- BOTÓN HAMBURGUESA — esquina superior DERECHA -->
<button class="boton-hamburguesa" id="btnHamburguesa" onclick="alternarMenu()" aria-label="Abrir menú">
    <span class="raya-menu"></span>
    <span class="raya-menu"></span>
    <span class="raya-menu"></span>
</button>

<!-- Fondo oscuro al abrir el menú -->
<div class="fondo-oscuro" id="fondoOscuro" onclick="cerrarMenu()"></div>

<!-- PANEL DEL MENÚ — desliza desde la DERECHA, tamaño compacto -->
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
    </button>

    <div class="etiqueta-seccion">Sistema</div>
    <button class="opcion-menu" id="op-usuarios" onclick="mostrarAviso('Módulo de usuarios próximamente','exito')">
        <i class="fa-solid fa-users"></i> Usuarios
    </button>
    <button class="opcion-menu" onclick="mostrarAviso('Configuración próximamente','exito')">
        <i class="fa-solid fa-gear"></i> Configuración
    </button>
</nav>

<!-- BARRA SUPERIOR: botón subir a la IZQUIERDA, hamburguesa a la derecha -->
<header class="barra-superior">
    <div class="barra-izquierda">
        <!-- Botón subir archivo — lado izquierdo -->
        <button class="boton boton-primario" onclick="mostrarPagina('subir')">
            <i class="fa-solid fa-plus"></i> Subir archivo
        </button>
        <h1 id="tituloPagina">Vista Pública</h1>
    </div>
    <!-- El botón hamburguesa está fijo en la esquina derecha, no necesita espacio aquí -->
</header>

<!-- CONTENIDO PRINCIPAL -->
<main class="area-contenido">

    <!-- VISTA PÚBLICA -->
    <div class="pagina activa" id="pagina-publico">
        <div class="cabecera-publica">
            <div class="logo-publico"><i class="fa-solid fa-dove"></i></div>
            <div class="titulo-publico">
                <h2>Recursos Disponibles</h2>
                <p>Descarga materiales y recursos compartidos</p>
            </div>
        </div>

        <div class="barra-busqueda">
            <input type="text" placeholder="🔍  Buscar recursos..." style="max-width:400px"/>
            <select class="selector-filtro">
                <option>Todas las categorías</option>
                <option>Documentos</option>
                <option>Imágenes</option>
                <option>Videos</option>
            </select>
            <select class="selector-filtro">
                <option>Más recientes</option>
                <option>Más descargados</option>
                <option>Nombre A-Z</option>
            </select>
        </div>

        <div class="filtros-publicos">
            <div class="chip-filtro activo">Todos</div>
            <div class="chip-filtro">📄 PDF</div>
            <div class="chip-filtro">🖼 Imágenes</div>
            <div class="chip-filtro">🎬 Videos</div>
        </div>

        <div class="cuadricula-publica">
            <?php foreach ($archivos as $archivo): ?>
                <?= tarjeta_publica($archivo) ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- MIS ARCHIVOS -->
    <div class="pagina" id="pagina-archivos">
        <div class="barra-busqueda">
            <input type="text" placeholder="🔍  Buscar archivos..." oninput="filtrarArchivos(this.value)"/>
            <select class="selector-filtro" onchange="filtrarPorTipo(this.value)">
                <option value="todos">Todos los tipos</option>
                <option value="pdf">PDF</option>
                <option value="img">Imágenes</option>
                <option value="vid">Videos</option>
            </select>
        </div>
        <div class="cuadricula-archivos" id="todosArchivos">
            <?php foreach ($archivos as $archivo): ?>
                <?= tarjeta_archivo($archivo) ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SUBIR ARCHIVO -->
    <div class="pagina" id="pagina-subir">
        <div class="contenedor-subida">
            <form class="tarjeta-formulario" action="subir.php" method="POST" enctype="multipart/form-data">
                <h2>📤 Subir nuevo archivo</h2>

                <div class="grupo-formulario">
                    <label for="campoTitulo">Título del archivo</label>
                    <input type="text" id="campoTitulo" name="titulo" placeholder="Ingrese el título..." oninput="actualizarPrevista()"/>
                </div>
                <div class="grupo-formulario">
                    <label for="campoDescripcion">Descripción</label>
                    <textarea id="campoDescripcion" name="descripcion" placeholder="Ingrese una descripción..." oninput="actualizarPrevista()"></textarea>
                </div>
                <div class="grupo-formulario">
                    <label for="campoCategoria">Categoría</label>
                    <select id="campoCategoria" name="categoria">
                        <option value="">Seleccionar categoría</option>
                        <option value="documentos">Documentos</option>
                        <option value="imagenes">Imágenes</option>
                        <option value="videos">Videos</option>
                        <option value="recursos">Recursos</option>
                    </select>
                </div>
                <div class="grupo-formulario">
                    <label>Archivo principal</label>
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
                    <label>Adjuntar archivos adicionales</label>
                    <div class="cuadricula-adjuntos">
                        <div class="caja-adjunto" onclick="document.getElementById('campoImagenes').click()">
                            <i class="fa-regular fa-image"></i>
                            <span>Adjuntar imágenes</span>
                            <button type="button" class="boton boton-contorno" style="margin-top:8px;padding:6px 14px;font-size:.78rem">Elegir</button>
                            <input type="file" id="campoImagenes" name="imagenes[]" accept="image/*" multiple style="display:none"/>
                        </div>
                        <div class="caja-adjunto">
                            <i class="fa-brands fa-youtube"></i>
                            <span>Video (link YouTube)</span>
                            <input type="text" name="enlace_youtube" placeholder="https://youtube.com/..." style="margin-top:8px;width:100%;padding:7px 10px;border-radius:6px;border:1.5px solid var(--borde);font-size:.8rem;outline:none;font-family:'DM Sans'"/>
                        </div>
                    </div>
                </div>
                <button type="submit" class="boton boton-primario" style="width:100%;justify-content:center;padding:13px">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Publicar archivo
                </button>
            </form>

            <div class="tarjeta-previsualizar">
                <h3>👁 Vista previa</h3>
                <div class="miniatura-previa"><i class="fa-solid fa-file"></i></div>
                <div class="cuerpo-previsualizar">
                    <div class="titulo-previo" id="tituloPrevio">Título del archivo</div>
                    <div class="descripcion-previa" id="descripcionPrevia">La descripción aparecerá aquí...</div>
                </div>
                <div class="boton-previo">MÁS INFORMACIÓN Y DESCARGA →</div>
            </div>
        </div>
    </div>

    <!-- PAPELERA -->
    <div class="pagina" id="pagina-papelera">
        <div style="text-align:center;padding:60px 20px;color:var(--texto-suave)">
            <i class="fa-solid fa-trash-can" style="font-size:3rem;margin-bottom:16px;display:block;opacity:.3"></i>
            <p style="font-size:1rem;font-weight:600">Papelera vacía</p>
            <p style="font-size:.85rem;margin-top:6px">Los archivos eliminados aparecerán aquí.</p>
        </div>
    </div>

</main>

<!-- MODAL EDITAR -->
<div class="superposicion-modal" id="modalEditar">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalEditar()">✕</button>
        <h3>✏️ Modificar archivo</h3>
        <div class="grupo-formulario">
            <label for="editarNombre">Nombre del archivo</label>
            <input type="text" id="editarNombre" placeholder="Nombre del archivo"/>
        </div>
        <div class="grupo-formulario">
            <label for="editarDescripcion">Descripción</label>
            <textarea id="editarDescripcion" placeholder="Descripción del archivo..."></textarea>
        </div>
        <div class="grupo-formulario">
            <label for="editarCategoria">Categoría</label>
            <select id="editarCategoria">
                <option value="documentos">Documentos</option>
                <option value="imagenes">Imágenes</option>
                <option value="videos">Videos</option>
                <option value="recursos">Recursos</option>
            </select>
        </div>
        <div class="grupo-formulario">
            <label for="editarFecha">Fecha</label>
            <input type="text" id="editarFecha" placeholder="Ej: 10 Abr 2026"/>
        </div>
        <div class="fila-botones-modal">
            <button class="boton boton-contorno" onclick="cerrarModalEditar()">Cancelar</button>
            <button class="boton boton-primario" onclick="guardarCambios()">
                <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
            </button>
        </div>
    </div>
</div>

<!-- AVISO TOAST -->
<div class="aviso" id="aviso">
    <i class="fa-solid fa-circle-check"></i>
    <span id="mensajeAviso">Acción completada</span>
</div>

</body>
</html>
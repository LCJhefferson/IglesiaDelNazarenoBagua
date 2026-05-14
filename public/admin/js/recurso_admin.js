/* ============================================================
   paneRecursos · Rediseño V2 — Lógica de cliente
   Command Palette ⌘K + Modales + Toast + Filtros + Form helpers
   ============================================================ */

/* ------------------------------------------------------------
   NAVEGACIÓN ENTRE PÁGINAS
   ------------------------------------------------------------ */
const TITULOS_PAGINA = {
    publico:   { titulo: 'Vista Pública',  eyebrow: 'Comunidad · Recursos' },
    archivos:  { titulo: 'Mis Archivos',   eyebrow: 'Administración' },
    subir:     { titulo: 'Subir Archivo',  eyebrow: 'Administración' },
    papelera:  { titulo: 'Papelera',       eyebrow: 'Administración' },
};

function mostrarPagina(nombre) {
    // Ocultar todas
    document.querySelectorAll('.pagina').forEach(p => p.classList.remove('activa'));
    const objetivo = document.getElementById('pagina-' + nombre);
    if (objetivo) objetivo.classList.add('activa');

    // Actualizar eyebrow de la barra superior
    const eyebrow = document.getElementById('eyebrowPagina');
    if (eyebrow && TITULOS_PAGINA[nombre]) {
        eyebrow.textContent = TITULOS_PAGINA[nombre].eyebrow;
    }

    // Cerrar paleta si estuviera abierta
    cerrarPaleta();
    // Scroll al inicio
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ------------------------------------------------------------
   COMMAND PALETTE ⌘K
   ------------------------------------------------------------ */
const paleta = {
    fondo:      null,
    input:      null,
    lista:      null,
    items:      [],   // referencias DOM
    visibles:   [],   // ítems actualmente visibles tras filtrar
    indiceSel:  0,
};

function abrirPaleta() {
    if (!paleta.fondo) paleta.fondo = document.getElementById('fondoPaleta');
    if (!paleta.input) paleta.input = document.getElementById('inputPaleta');
    if (!paleta.lista) paleta.lista = document.getElementById('listaPaleta');

    paleta.fondo.classList.add('activo');
    paleta.input.value = '';
    filtrarPaleta('');
    setTimeout(() => paleta.input.focus(), 50);
}

function cerrarPaleta() {
    const fondo = document.getElementById('fondoPaleta');
    if (fondo) fondo.classList.remove('activo');
}

function filtrarPaleta(termino) {
    const t = (termino || '').toLowerCase().trim();
    paleta.visibles = [];

    paleta.items.forEach(item => {
        const texto = (item.dataset.label + ' ' + (item.dataset.hint || '')).toLowerCase();
        const visible = !t || texto.includes(t);
        item.style.display = visible ? '' : 'none';
        if (visible) paleta.visibles.push(item);
    });

    // Ocultar secciones que quedaron vacías
    document.querySelectorAll('.seccion-paleta').forEach(s => {
        const tieneVisibles = Array.from(s.querySelectorAll('.item-paleta'))
            .some(i => i.style.display !== 'none');
        s.style.display = tieneVisibles ? '' : 'none';
    });

    // Estado vacío
    const vacio = document.getElementById('paletaVacia');
    if (vacio) vacio.style.display = paleta.visibles.length === 0 ? 'block' : 'none';

    paleta.indiceSel = 0;
    aplicarSeleccionPaleta();
}

function aplicarSeleccionPaleta() {
    paleta.items.forEach(i => i.classList.remove('seleccionado'));
    if (paleta.visibles[paleta.indiceSel]) {
        paleta.visibles[paleta.indiceSel].classList.add('seleccionado');
        paleta.visibles[paleta.indiceSel].scrollIntoView({ block: 'nearest' });
    }
}

function moverSeleccionPaleta(delta) {
    if (paleta.visibles.length === 0) return;
    paleta.indiceSel = (paleta.indiceSel + delta + paleta.visibles.length) % paleta.visibles.length;
    aplicarSeleccionPaleta();
}

function ejecutarSeleccionPaleta() {
    const item = paleta.visibles[paleta.indiceSel];
    if (!item) return;
    item.click();
}

function inicializarPaleta() {
    paleta.items = Array.from(document.querySelectorAll('.item-paleta'));
    paleta.visibles = paleta.items.slice();

    if (paleta.input) {
        paleta.input.addEventListener('input', e => filtrarPaleta(e.target.value));
    }
}

/* ------------------------------------------------------------
   ATAJOS DE TECLADO GLOBALES
   ------------------------------------------------------------ */
document.addEventListener('keydown', (e) => {
    const paletaAbierta = document.getElementById('fondoPaleta')?.classList.contains('activo');

    // ⌘K / Ctrl+K — abrir paleta
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        paletaAbierta ? cerrarPaleta() : abrirPaleta();
        return;
    }

    // Esc — cerrar todo modal/paleta
    if (e.key === 'Escape') {
        cerrarPaleta();
        cerrarModalEditar();
        cerrarModalConfirmar();
        cerrarModalDefinitivo();
        cerrarModalVaciarPapelera();
        return;
    }

    // Navegación dentro de la paleta
    if (paletaAbierta) {
        if (e.key === 'ArrowDown') { e.preventDefault(); moverSeleccionPaleta(1); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); moverSeleccionPaleta(-1); }
        if (e.key === 'Enter')     { e.preventDefault(); ejecutarSeleccionPaleta(); }
    }
});

/* ------------------------------------------------------------
   MODAL: EDITAR ARCHIVO
   ------------------------------------------------------------ */
function abrirModalEditar(id, titulo, descripcion, categoria, tipo, ruta, youtube) {
    document.getElementById('editarId').value          = id;
    document.getElementById('editarTitulo').value      = titulo;
    document.getElementById('editarDescripcion').value = descripcion;
    document.getElementById('editarCategoria').value   = categoria;
    document.getElementById('editarTipoActual').value  = tipo;
    document.getElementById('editarRuta').value        = ruta;
    document.getElementById('editarYoutube').value     = youtube || '';

    document.getElementById('modalEditar').classList.add('activo');
}
function cerrarModalEditar() {
    document.getElementById('modalEditar')?.classList.remove('activo');
}

/* ------------------------------------------------------------
   MODALES DE CONFIRMACIÓN
   ------------------------------------------------------------ */
function confirmarEliminar(id, nombre) {
    document.getElementById('textoConfirmarEliminar').innerHTML =
        '"<strong>' + nombre + '</strong>" se moverá a la papelera y podrás restaurarlo después.';
    document.getElementById('enlaceConfirmarEliminar').href =
        RUTA_RECURSOS + '&eliminar=' + id;
    document.getElementById('modalConfirmarEliminar').classList.add('activo');
}
function cerrarModalConfirmar() {
    document.getElementById('modalConfirmarEliminar')?.classList.remove('activo');
}

function confirmarEliminarDefinitivo(id, nombre) {
    document.getElementById('textoConfirmarDefinitivo').innerHTML =
        'Esta acción <strong>no se puede deshacer</strong>. "' + nombre + '" se eliminará de forma permanente.';
    document.getElementById('enlaceConfirmarDefinitivo').href =
        RUTA_RECURSOS + '&eliminar_definitivo=' + id;
    document.getElementById('modalConfirmarDefinitivo').classList.add('activo');
}
function cerrarModalDefinitivo() {
    document.getElementById('modalConfirmarDefinitivo')?.classList.remove('activo');
}

function confirmarVaciarPapelera() {
    document.getElementById('enlaceVaciarPapelera').href = RUTA_RECURSOS + '&vaciar_papelera=1';
    document.getElementById('modalVaciarPapelera').classList.add('activo');
}
function cerrarModalVaciarPapelera() {
    document.getElementById('modalVaciarPapelera')?.classList.remove('activo');
}

/* ------------------------------------------------------------
   TOAST AVISO
   ------------------------------------------------------------ */
function mostrarAviso(mensaje, tipo) {
    const aviso = document.getElementById('aviso');
    const texto = document.getElementById('mensajeAviso');
    if (!aviso || !texto) return;

    texto.textContent = mensaje;
    aviso.className = 'aviso ' + (tipo || 'exito');

    // Forzar reflow para reiniciar animación
    void aviso.offsetWidth;
    aviso.classList.add('visible');

    clearTimeout(window._timerAviso);
    window._timerAviso = setTimeout(() => {
        aviso.classList.remove('visible');
    }, 3500);
}

/* ------------------------------------------------------------
   DROPZONE: arrastrar y seleccionar
   ------------------------------------------------------------ */
function manejarSoltado(event) {
    event.preventDefault();
    const zona = event.currentTarget;
    zona.classList.remove('arrastrando');

    if (event.dataTransfer.files.length > 0) {
        const input = document.getElementById('campoPrincipal');
        input.files = event.dataTransfer.files;
        actualizarPrevista();
    }
}

function manejarSeleccion(input) {
    if (input.files.length > 0) {
        actualizarPrevista();
    }
}

/* ------------------------------------------------------------
   PREVIEW DEL FORMULARIO
   ------------------------------------------------------------ */
function actualizarPrevista() {
    const titulo      = document.getElementById('campoTitulo')?.value      || 'Título del archivo';
    const descripcion = document.getElementById('campoDescripcion')?.value || 'La descripción aparecerá aquí...';

    const tp = document.getElementById('tituloPrevio');
    const dp = document.getElementById('descripcionPrevia');
    if (tp) tp.textContent = titulo;
    if (dp) dp.textContent = descripcion;
}

function limpiarFormulario() {
    document.getElementById('campoId').value           = '';
    document.getElementById('campoTitulo').value       = '';
    document.getElementById('campoDescripcion').value  = '';
    document.getElementById('campoCategoria').value    = '';
    document.getElementById('campoYoutube').value      = '';
    document.getElementById('campoPrincipal').value    = '';
    document.getElementById('campoRutaActual').value   = '';
    document.getElementById('campoTipoActual').value   = '';

    const titulo = document.getElementById('tituloFormulario');
    const boton  = document.getElementById('textoBotonGuardar');
    if (titulo) titulo.textContent = '📤 Subir nuevo archivo';
    if (boton)  boton.textContent  = 'Publicar archivo';

    actualizarPrevista();
}

/* ------------------------------------------------------------
   FILTROS DE MIS ARCHIVOS
   ------------------------------------------------------------ */
let filtroTexto = '';
let filtroTipo  = 'todos';

function aplicarFiltros() {
    const tarjetas = document.querySelectorAll('#todosArchivos .tarjeta-archivo');
    tarjetas.forEach(t => {
        const nombre = (t.querySelector('.nombre-archivo')?.textContent || '').toLowerCase();
        const tipo   = t.dataset.tipo || '';

        const coincideTexto = !filtroTexto || nombre.includes(filtroTexto);
        const coincideTipo  = filtroTipo === 'todos' || tipo === filtroTipo;

        t.style.display = (coincideTexto && coincideTipo) ? '' : 'none';
    });
}

function filtrarArchivos(valor) {
    filtroTexto = (valor || '').toLowerCase().trim();
    aplicarFiltros();
}

function filtrarPorTipo(valor) {
    filtroTipo = valor;
    aplicarFiltros();
}

/* ------------------------------------------------------------
   FILTRO POR PILLS (categoría)
   ------------------------------------------------------------ */
function filtrarPorCategoria(categoria, elemento) {
    // Marcar pill activa
    document.querySelectorAll('.barra-pills .pill').forEach(p => p.classList.remove('activa'));
    if (elemento) elemento.classList.add('activa');

    document.querySelectorAll('.cuadricula-publica .tarjeta-publica').forEach(t => {
        const cat = t.dataset.categoria || '';
        t.style.display = (categoria === 'todos' || cat === categoria) ? '' : 'none';
    });
}

/* ------------------------------------------------------------
   INICIALIZACIÓN
   ------------------------------------------------------------ */
document.addEventListener('DOMContentLoaded', () => {
    inicializarPaleta();
    actualizarPrevista();
});
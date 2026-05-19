const TITULOS_PAGINA = {
    publico:  { eyebrow: 'Comunidad · Recursos' },
    archivos: { eyebrow: 'Administración' },
    papelera: { eyebrow: 'Administración' },
};

function mostrarPagina(nombre) {
    document.querySelectorAll('.pagina').forEach(p => p.classList.remove('activa'));
    const objetivo = document.getElementById('pagina-' + nombre);
    if (objetivo) objetivo.classList.add('activa');

    const eyebrow = document.getElementById('eyebrowPagina');
    if (eyebrow && TITULOS_PAGINA[nombre]) {
        eyebrow.textContent = TITULOS_PAGINA[nombre].eyebrow;
    }

    document.querySelectorAll('.nav-btn[data-pagina]').forEach(b => b.classList.remove('activo'));
    const navBtn = document.querySelector('.nav-btn[data-pagina="' + nombre + '"]');
    if (navBtn) navBtn.classList.add('activo');

    cerrarDropdownBusqueda();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarModalEditar();
        cerrarModalConfirmar();
        cerrarModalDefinitivo();
        cerrarModalVaciarPapelera();
        cerrarModalSubir();
        cerrarDropdownBusqueda();
    }
});

let _busqIndice = -1;
let _busqTermino = '';

function buscarRecursos(valor) {
    _busqTermino = (valor || '').trim();
    const dropdown = document.getElementById('dropdownBusqueda');
    if (!dropdown) return;

    if (!_busqTermino) {
        dropdown.style.display = 'none';
        return;
    }

    const t    = _busqTermino.toLowerCase();
    const datos = (typeof ARCHIVOS_DATA !== 'undefined') ? ARCHIVOS_DATA : [];
    const hits  = datos.filter(a =>
        a.titulo.toLowerCase().includes(t) || (a.categoria || '').toLowerCase().includes(t)
    );

    if (hits.length === 0) {
        dropdown.innerHTML = '<div class="drop-vacio">Sin resultados para "<strong>' + _esc(_busqTermino) + '</strong>"</div>';
        dropdown.style.display = 'block';
        _busqIndice = -1;
        return;
    }

    const iconos = { pdf: '📄', img: '🖼️', vid: '🎬', doc: '📝' };
    const max    = Math.min(hits.length, 5);
    let html = '';
    for (let i = 0; i < max; i++) {
        const a = hits[i];
        html += `<div class="drop-item" data-i="${i}" onmousedown="verTodosResultados('${_esc(a.titulo)}')">
            <span class="drop-icono">${iconos[a.tipo] || '📁'}</span>
            <div class="drop-info">
                <div class="drop-titulo">${_resaltar(a.titulo, _busqTermino)}</div>
                <div class="drop-cat">${_esc(a.categoria || '')}</div>
            </div>
        </div>`;
    }
    if (hits.length > max) {
        html += `<div class="drop-footer" onmousedown="verTodosResultados()">Ver los ${hits.length} resultados →</div>`;
    }
    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
    _busqIndice = -1;
}

function _resaltar(texto, termino) {
    const e   = _esc(texto);
    const tE  = termino.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return e.replace(new RegExp('(' + tE + ')', 'gi'), '<mark class="drop-match">$1</mark>');
}

function _esc(str) {
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function teclasBusqueda(e) {
    const dropdown = document.getElementById('dropdownBusqueda');
    if (!dropdown || dropdown.style.display === 'none') return;
    const items = dropdown.querySelectorAll('.drop-item');

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        _busqIndice = Math.min(_busqIndice + 1, items.length - 1);
        _marcarBusq(items);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        _busqIndice = Math.max(_busqIndice - 1, -1);
        _marcarBusq(items);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        verTodosResultados();
    } else if (e.key === 'Escape') {
        cerrarDropdownBusqueda();
    }
}

function _marcarBusq(items) {
    items.forEach((el, i) => el.classList.toggle('activo', i === _busqIndice));
    if (_busqIndice >= 0) items[_busqIndice]?.scrollIntoView({ block: 'nearest' });
}

function cerrarDropdownBusqueda() {
    const dd = document.getElementById('dropdownBusqueda');
    if (dd) dd.style.display = 'none';
    const inp = document.getElementById('inputBusqueda');
    if (inp) { inp.value = ''; _busqTermino = ''; }
}

function verTodosResultados(titulo) {
    const termino = titulo || _busqTermino;
    cerrarDropdownBusqueda();
    mostrarPagina('archivos');
    filtrarArchivos(termino);
    const inp = document.querySelector('.barra-busqueda input[type="text"]');
    if (inp) inp.value = termino;
}

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

function confirmarEliminar(id, nombre) {
    document.getElementById('textoConfirmarEliminar').innerHTML =
        '"<strong>' + nombre + '</strong>" se moverá a la papelera y podrás restaurarlo después.';
    document.getElementById('enlaceConfirmarEliminar').href = RUTA_RECURSOS + '&eliminar=' + id;
    document.getElementById('modalConfirmarEliminar').classList.add('activo');
}
function cerrarModalConfirmar() {
    document.getElementById('modalConfirmarEliminar')?.classList.remove('activo');
}

function confirmarEliminarDefinitivo(id, nombre) {
    document.getElementById('textoConfirmarDefinitivo').innerHTML =
        'Esta acción <strong>no se puede deshacer</strong>. "' + nombre + '" se eliminará de forma permanente.';
    document.getElementById('enlaceConfirmarDefinitivo').href = RUTA_RECURSOS + '&eliminar_definitivo=' + id;
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

function mostrarAviso(mensaje, tipo) {
    const aviso = document.getElementById('aviso');
    const texto = document.getElementById('mensajeAviso');
    if (!aviso || !texto) return;
    texto.textContent = mensaje;
    aviso.className = 'aviso ' + (tipo || 'exito');
    void aviso.offsetWidth;
    aviso.classList.add('visible');
    clearTimeout(window._timerAviso);
    window._timerAviso = setTimeout(() => aviso.classList.remove('visible'), 3500);
}

let _focusAnteriorSubir = null;

function abrirModalSubir() {
    _focusAnteriorSubir = document.activeElement;
    const overlay = document.getElementById('overlaySubir');
    if (!overlay) return;
    overlay.classList.add('activo');
    document.querySelector('.barra-superior')?.classList.add('borrosa');
    document.querySelector('.area-contenido')?.classList.add('borrosa');
    limpiarFormSubir();
    setTimeout(() => document.getElementById('subir_titulo')?.focus(), 60);
}

function cerrarModalSubir() {
    document.getElementById('overlaySubir')?.classList.remove('activo');
    document.querySelector('.barra-superior')?.classList.remove('borrosa');
    document.querySelector('.area-contenido')?.classList.remove('borrosa');
    limpiarFormSubir();
    _focusAnteriorSubir?.focus();
}

function limpiarFormSubir() {
    ['subir_campoId','subir_campoRutaActual','subir_campoTipoActual',
     'subir_titulo','subir_descripcion','subir_youtube'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const sel = document.getElementById('subir_categoria');
    if (sel) sel.value = '';
    const camp = document.getElementById('subir_campoPrincipal');
    if (camp) camp.value = '';
    const archivoSel = document.getElementById('subir_archivoSel');
    if (archivoSel) archivoSel.style.display = 'none';
}

function manejarSoltadoSubir(event) {
    event.preventDefault();
    document.getElementById('subir_zonaArrastre')?.classList.remove('arrastrando');
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('subir_campoPrincipal').files = files;
        _mostrarArchivoSel(files[0]);
    }
}

function seleccionarArchivoSubir(input) {
    if (input.files.length > 0) _mostrarArchivoSel(input.files[0]);
}

function _mostrarArchivoSel(file) {
    document.getElementById('subir_archivoNombre').textContent = file.name;
    document.getElementById('subir_archivoSel').style.display = 'flex';
    const barra = document.getElementById('subir_barraProg');
    if (!barra) return;
    barra.style.width = '0%';
    let prog = 0;
    const iv = setInterval(() => {
        prog = Math.min(prog + Math.random() * 18, 88);
        barra.style.width = prog + '%';
        if (prog >= 88) clearInterval(iv);
    }, 90);
    setTimeout(() => { barra.style.width = '100%'; }, 1100);
}

let filtroTexto = '';
let filtroTipo  = 'todos';

function aplicarFiltros() {
    document.querySelectorAll('#todosArchivos .tarjeta-archivo').forEach(t => {
        const nombre = (t.querySelector('.nombre-archivo')?.textContent || '').toLowerCase();
        const tipo   = t.dataset.tipo || '';
        t.style.display = (
            (!filtroTexto || nombre.includes(filtroTexto)) &&
            (filtroTipo === 'todos' || tipo === filtroTipo)
        ) ? '' : 'none';
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

function filtrarPorCategoria(categoria, elemento) {
    document.querySelectorAll('.barra-pills .pill').forEach(p => p.classList.remove('activa'));
    if (elemento) elemento.classList.add('activa');
    document.querySelectorAll('.cuadricula-publica .tarjeta-publica').forEach(t => {
        t.style.display = (categoria === 'todos' || t.dataset.categoria === categoria) ? '' : 'none';
    });
}

function _trapTab(e, modalEl) {
    if (e.key !== 'Tab') return;
    const foc = modalEl.querySelectorAll('button, input, select, textarea, a[href], [tabindex]:not([tabindex="-1"])');
    if (foc.length === 0) return;
    const first = foc[0], last = foc[foc.length - 1];
    if (e.shiftKey) {
        if (document.activeElement === first) { e.preventDefault(); last.focus(); }
    } else {
        if (document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (e) => {
        const wrap = document.getElementById('wrapBusqueda');
        if (wrap && !wrap.contains(e.target)) cerrarDropdownBusqueda();
    });

    const modalSubir = document.getElementById('modalSubir');
    if (modalSubir) modalSubir.addEventListener('keydown', e => _trapTab(e, modalSubir));
});

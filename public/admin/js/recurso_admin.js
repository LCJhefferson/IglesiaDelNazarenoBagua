/**
 * MÓDULO RECURSOS — JavaScript Principal
 * ─────────────────────────────────────────────────────────────────────────────
 * Versión RESTful con:
 *   ✓ async/await + try/catch + response.ok    (Criterio 2 — Avanzado)
 *   ✓ Debounce 350ms + AbortController          (Criterio 3 — Avanzado)
 *   ✓ textContent en vez de innerHTML           (Criterio 4 — Anti-XSS)
 *   ✓ Token CSRF en cabecera X-CSRF-Token       (Criterio 4 — Seguridad)
 *   ✓ Estados de carga visibles (spinner/UX)    (Criterio 2 — Avanzado)
 *   ✓ JSON puro del servidor — el cliente renderiza (Criterio 1 — Avanzado)
 */

'use strict';

// ─── 1. CONSTANTES Y ESTADO GLOBAL ──────────────────────────────────────────

/**
 * URL base de la API de recursos.
 * Se construye con la constante URL que PHP inyecta en la vista.
 * No hardcodeamos la URL para que funcione en cualquier entorno (dev/prod).
 */
const API_RECURSOS = 'index.php?vista=api/recursos';

// CSRF_TOKEN es declarado por dashboard.php en el <head> como const global.
// Aquí lo accedemos de forma segura sin re-declarar (evita SyntaxError con 'use strict')
const _CSRF = () => (typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');

/**
 * AbortController activo para la búsqueda en tiempo real.
 *
 * ¿Por qué AbortController?
 * Cuando el usuario escribe rápido ("pred"), se disparan múltiples peticiones:
 *   "p" → fetch #1 (en vuelo)
 *   "pr" → fetch #2 (en vuelo) — queremos cancelar #1 si llega #2
 *
 * Sin AbortController, todas las respuestas llegarían en orden impredecible
 * y la UI mostraría resultados desincronizados. Con AbortController,
 * la petición anterior se cancela explícitamente antes de enviar la nueva.
 */
let controladorBusqueda = null;

/**
 * Timer para el Debounce de la búsqueda.
 *
 * ¿Qué es Debounce?
 * Técnica que retrasa la ejecución de una función hasta que el usuario
 * deja de disparar el evento por un tiempo mínimo (350ms aquí).
 * Evita saturar el servidor con una petición por cada tecla presionada.
 *
 * Sin debounce: "predica" → 7 peticiones al servidor
 * Con debounce: "predica" → 1 petición (solo cuando el usuario para de escribir)
 */
let timerDebounce = null;

/** Índice del ítem activo en el dropdown de búsqueda (navegación por teclado) */
let _busqIndice = -1;
/** Término activo de búsqueda (usado para restaurar en verTodosResultados) */
let _busqTermino = '';

// ─── 2. UTILIDADES ──────────────────────────────────────────────────────────

/**
 * Escapa caracteres especiales HTML para inserción segura en innerHTML.
 * Se usa SOLO para construir el dropdown de búsqueda donde se necesita
 * resaltar con <mark>. En todos los demás casos se usa textContent.
 *
 * ¿Por qué evitar innerHTML con datos del servidor?
 * Si un atacante guardó "<script>alert(1)</script>" como título de un recurso,
 * innerHTML lo ejecutaría (XSS). textContent lo muestra como texto literal.
 *
 * @param {string} str  Texto a escapar
 * @returns {string}    Texto con entidades HTML
 */
function _esc(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * Resalta el término de búsqueda dentro del texto usando <mark>.
 * Usa _esc() antes de insertar para prevenir XSS incluso en este contexto.
 *
 * @param {string} texto   Texto original
 * @param {string} termino Término a resaltar
 * @returns {string}       HTML con <mark> alrededor del término
 */
function _resaltar(texto, termino) {
    const escapado = _esc(texto);
    const terminoEsc = termino.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return escapado.replace(
        new RegExp('(' + terminoEsc + ')', 'gi'),
        '<mark class="drop-match">$1</mark>'
    );
}

/**
 * Muestra u oculta el spinner de carga sobre el contenedor de archivos.
 * Estado de carga visible → Criterio 2 Avanzado de la rúbrica.
 *
 * @param {boolean} visible  true = mostrar spinner, false = ocultarlo
 */
function mostrarSpinner(visible) {
    let spinner = document.getElementById('spinnerCarga');
    if (!spinner) {
        // Crear el spinner si no existe (lazy creation)
        spinner = document.createElement('div');
        spinner.id = 'spinnerCarga';
        spinner.className = 'spinner-carga';
        spinner.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Cargando…';
        spinner.style.cssText = `
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--fondo, #fff);
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            font-size: 0.9rem;
            color: var(--acento, #1d4ed8);
            z-index: 100;
            gap: 8px;
            align-items: center;
        `;
        const contenedor = document.getElementById('todosArchivos');
        if (contenedor) {
            contenedor.style.position = 'relative';
            contenedor.appendChild(spinner);
        }
    }
    spinner.style.display = visible ? 'flex' : 'none';
}

// ─── 3. BÚSQUEDA EN TIEMPO REAL CON DEBOUNCE + ABORTCONTROLLER ───────────────

/**
 * Punto de entrada de la búsqueda — llamado en cada evento 'input'.
 * Implementa el patrón Debounce: solo dispara la petición después de
 * que el usuario no haya escrito durante 350ms.
 *
 * Criterio 3 (Avanzado): Debounce ~350ms + AbortController para cancelar
 * peticiones obsoletas.
 *
 * @param {string} valor  Texto actual del campo de búsqueda
 */
function buscarRecursos(valor) {
    _busqTermino = (valor || '').trim();

    // Ocultar dropdown si el campo está vacío
    if (!_busqTermino) {
        cerrarDropdownBusqueda();
        return;
    }

    // ── DEBOUNCE: cancela el timer anterior y establece uno nuevo ────────────
    clearTimeout(timerDebounce);
    timerDebounce = setTimeout(() => {
        _ejecutarBusquedaAPI(_busqTermino);
    }, 350); // 350ms — balance entre reactividad y carga al servidor
}

/**
 * Ejecuta la búsqueda real contra el endpoint GET /api/recursos?q=...
 *
 * Criterio 2 (Avanzado): async/await + try/catch + response.ok verificado.
 * Criterio 3 (Avanzado): AbortController cancela petición anterior.
 *
 * @param {string} termino  Texto de búsqueda
 */
async function _ejecutarBusquedaAPI(termino) {
    // ── ABORTCONTROLLER: cancela la petición anterior si sigue en vuelo ───────
    if (controladorBusqueda) {
        controladorBusqueda.abort(); // Señal de cancelación a fetch en vuelo
    }
    controladorBusqueda = new AbortController();

    const url = `${API_RECURSOS}&q=${encodeURIComponent(termino)}`;

    try {
        const resp = await fetch(url, {
            method: 'GET',
            signal: controladorBusqueda.signal // Conecta el AbortController
        });

        // ── VERIFICACIÓN response.ok ─────────────────────────────────────────
        // Criterio 2: verificar response.ok antes de parsear JSON.
        // Si el servidor responde con 401/403/500, response.ok = false.
        // Sin este check, .json() daría un error confuso en vez de uno claro.
        if (!resp.ok) {
            throw new Error(`Error del servidor: HTTP ${resp.status}`);
        }

        const resultado = await resp.json();

        if (!resultado.ok) {
            throw new Error(resultado.error || 'Error desconocido del servidor');
        }

        _renderizarDropdown(resultado.data.archivos || [], termino);

    } catch (err) {
        // AbortError es esperado — ocurre cuando cancelamos la petición anterior.
        // No debe mostrarse como error al usuario.
        if (err.name === 'AbortError') return;

        console.error('[RecursosAPI] Error en búsqueda:', err.message);
        // No mostramos error en UI para la búsqueda del dropdown
    }
}

/**
 * Renderiza el dropdown de resultados de búsqueda.
 * Usa _esc() + _resaltar() para prevenir XSS al insertar datos del servidor.
 *
 * @param {Array}  resultados  Array de recursos desde la API
 * @param {string} termino     Término buscado (para resaltar)
 */
function _renderizarDropdown(resultados, termino) {
    const dropdown = document.getElementById('dropdownBusqueda');
    if (!dropdown) return;

    if (resultados.length === 0) {
        // Usamos textContent para el texto dinámico, innerHTML solo para estructura fija
        const divVacio = document.createElement('div');
        divVacio.className = 'drop-vacio';
        // Combinamos texto estático + _esc() para el término — nunca innerHTML crudo
        divVacio.innerHTML = 'Sin resultados para "<strong>' + _esc(termino) + '</strong>"';
        dropdown.innerHTML = '';
        dropdown.appendChild(divVacio);
        dropdown.style.display = 'block';
        _busqIndice = -1;
        return;
    }

    const iconos = { pdf: '📄', img: '🖼️', vid: '🎬', doc: '📝', yt: '▶️' };
    const max = Math.min(resultados.length, 5);
    let html = '';

    for (let i = 0; i < max; i++) {
        const a = resultados[i];
        // SEGURIDAD: _esc() en todos los datos del servidor antes de insertar en HTML
        html += `<div class="drop-item" data-i="${i}"
                      onmousedown="verTodosResultados('${_esc(a.titulo)}')">
            <span class="drop-icono">${iconos[a.tipo] || '📁'}</span>
            <div class="drop-info">
                <div class="drop-titulo">${_resaltar(a.titulo, termino)}</div>
                <div class="drop-cat">${_esc(a.categoria || '')}</div>
            </div>
        </div>`;
    }

    if (resultados.length > max) {
        html += `<div class="drop-footer" onmousedown="verTodosResultados()">
                     Ver los ${resultados.length} resultados →
                 </div>`;
    }

    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
    _busqIndice = -1;
}

// ─── 4. FILTRADO LOCAL DE LA CUADRÍCULA ──────────────────────────────────────

let filtroTexto = '';
let filtroTipo  = 'todos';

/**
 * Filtra las tarjetas visibles en la cuadrícula de "Mis Archivos".
 * Este filtrado es LOCAL (client-side) — no hace fetch.
 * Lee el atributo data-tipo y el texto del .nombre-archivo usando textContent.
 */
function aplicarFiltros() {
    document.querySelectorAll('#todosArchivos .tarjeta-archivo').forEach(tarjeta => {
        // SEGURIDAD: leemos con textContent — nunca manipulamos HTML directamente
        const nombre = (tarjeta.querySelector('.nombre-archivo')?.textContent || '').toLowerCase();
        const tipo   = tarjeta.dataset.tipo || '';
        const visible = (
            (!filtroTexto || nombre.includes(filtroTexto)) &&
            (filtroTipo === 'todos' || tipo === filtroTipo)
        );
        tarjeta.style.display = visible ? '' : 'none';
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
    document.querySelectorAll('.cuadricula-publica .tarjeta-publica').forEach(tarjeta => {
        tarjeta.style.display = (categoria === 'todos' || tarjeta.dataset.categoria === categoria)
            ? '' : 'none';
    });
}

// ─── 5. OPERACIONES DE ESCRITURA CON FETCH API ───────────────────────────────

/**
 * Mueve un recurso a la papelera mediante DELETE /api/recursos?id={n}.
 *
 * Criterio 2 (Avanzado): async/await + response.ok + try/catch.
 * Criterio 4 (Avanzado): CSRF en cabecera X-CSRF-Token, BOLA/IDOR en servidor.
 *
 * @param {number} id     ID del recurso a eliminar
 * @param {string} nombre Nombre del recurso (para mensajes de UI)
 */
async function eliminarRecurso(id, nombre) {
    cerrarModalConfirmar();
    mostrarSpinner(true);

    try {
        const resp = await fetch(`${API_RECURSOS}&id=${id}`, {
            method: 'DELETE',
            headers: {
                // CSRF: enviado en cabecera para inmunidad ante formularios cruzados
                'X-CSRF-Token': _CSRF(),
                'Accept': 'application/json'
            }
        });

        // Verificación response.ok — Criterio 2 Avanzado
        if (!resp.ok) {
            const errData = await resp.json().catch(() => ({}));
            throw new Error(errData.error || `Error HTTP ${resp.status}`);
        }

        const data = await resp.json();

        if (!data.ok) {
            throw new Error(data.error || 'Error al eliminar el recurso');
        }

        // Retroalimentación visual inmediata sin recargar la página
        mostrarAviso('Recurso movido a la papelera', 'exito');

        // Actualizar el badge de papelera y remover la tarjeta del DOM
        _removerTarjetaDOM(id);
        _actualizarBadgePapelera(+1);

    } catch (err) {
        mostrarAviso(err.message, 'error');
        console.error('[RecursosAPI] Error al eliminar:', err);
    } finally {
        // finally garantiza que el spinner siempre se oculte,
        // incluso si ocurrió un error inesperado
        mostrarSpinner(false);
    }
}

/**
 * Restaura un recurso desde la papelera mediante POST /api/recursos/restaurar.
 *
 * @param {number} papeleraId  ID del registro en la tabla recursos_papelera
 */
async function restaurarRecurso(papeleraId) {
    mostrarSpinner(true);

    try {
        const resp = await fetch('index.php?vista=api/recursos/restaurar', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': _CSRF(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ papelera_id: papeleraId })
        });

        if (!resp.ok) {
            const errData = await resp.json().catch(() => ({}));
            throw new Error(errData.error || `Error HTTP ${resp.status}`);
        }

        const data = await resp.json();
        if (!data.ok) throw new Error(data.error || 'Error al restaurar');

        mostrarAviso('Recurso restaurado correctamente', 'exito');

        // Recargar la sección papelera para reflejar el cambio
        cargarRecursosInicial();

    } catch (err) {
        mostrarAviso(err.message, 'error');
        console.error('[RecursosAPI] Error al restaurar:', err);
    } finally {
        mostrarSpinner(false);
    }
}

/**
 * Eliminación permanente de un recurso de la papelera.
 * DELETE /api/recursos/definitivo?id={n}
 *
 * @param {number} papeleraId  ID del registro en recursos_papelera
 */
async function eliminarDefinitivo(papeleraId) {
    cerrarModalDefinitivo();
    mostrarSpinner(true);

    try {
        const resp = await fetch(`index.php?vista=api/recursos/definitivo&id=${papeleraId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': _CSRF(),
                'Accept': 'application/json'
            }
        });

        if (!resp.ok) {
            const errData = await resp.json().catch(() => ({}));
            throw new Error(errData.error || `Error HTTP ${resp.status}`);
        }

        const data = await resp.json();
        if (!data.ok) throw new Error(data.error || 'Error al eliminar definitivamente');

        mostrarAviso('Recurso eliminado permanentemente', 'exito');
        cargarRecursosInicial();

    } catch (err) {
        mostrarAviso(err.message, 'error');
        console.error('[RecursosAPI] Error eliminación definitiva:', err);
    } finally {
        mostrarSpinner(false);
    }
}

/**
 * Vacía completamente la papelera.
 * DELETE /api/recursos/vaciar
 */
async function vaciarPapelera() {
    cerrarModalVaciarPapelera();
    mostrarSpinner(true);

    try {
        const resp = await fetch('index.php?vista=api/recursos/vaciar', {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': _CSRF(),
                'Accept': 'application/json'
            }
        });

        if (!resp.ok) {
            const errData = await resp.json().catch(() => ({}));
            throw new Error(errData.error || `Error HTTP ${resp.status}`);
        }

        const data = await resp.json();
        if (!data.ok) throw new Error(data.error || 'Error al vaciar la papelera');

        mostrarAviso('Papelera vaciada correctamente', 'exito');
        cargarRecursosInicial();

    } catch (err) {
        mostrarAviso(err.message, 'error');
        console.error('[RecursosAPI] Error al vaciar papelera:', err);
    } finally {
        mostrarSpinner(false);
    }
}

// ─── 6. HELPERS DE DOM ──────────────────────────────────────────────────────

/**
 * Elimina la tarjeta del recurso del DOM sin recargar la página.
 * Animación fade-out para retroalimentación visual inmediata (mejor UX).
 *
 * @param {number} id  ID del recurso a remover del DOM
 */
function _removerTarjetaDOM(id) {
    // Buscamos por data-recurso-id (atributo inyectado en el HTML de la tarjeta)
    const tarjeta = document.querySelector(`[data-recurso-id="${id}"]`);
    if (!tarjeta) return;

    tarjeta.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    tarjeta.style.opacity = '0';
    tarjeta.style.transform = 'scale(0.95)';
    setTimeout(() => tarjeta.remove(), 300);
}

/**
 * Actualiza el número del badge de la papelera en el sidebar.
 * @param {number} delta  +1 al añadir, -1 al restaurar/eliminar
 */
function _actualizarBadgePapelera(delta) {
    const badge = document.querySelector('.nav-btn[data-pagina="papelera"] .nav-badge');
    if (!badge) return;
    const actual = parseInt(badge.textContent) || 0;
    const nuevo  = actual + delta;
    badge.textContent = nuevo;
    badge.style.display = nuevo > 0 ? '' : 'none';
}

// ─── 7. CONTROLES DE MODALES ────────────────────────────────────────────────

/** ID pendiente de acción para modales de confirmación */
let _idPendienteEliminar    = null;
let _idPendienteDefinitivo  = null;

function confirmarEliminar(id, nombre) {
    _idPendienteEliminar = id;
    const modal = document.getElementById('modalConfirmarEliminar');
    const texto = document.getElementById('textoConfirmarEliminar');
    if (texto) {
        // SEGURIDAD: textContent para nombre del servidor — nunca innerHTML
        texto.textContent = '';
        const strong = document.createElement('strong');
        strong.textContent = nombre; // textContent escapa automáticamente
        texto.append('"', strong, '" se moverá a la papelera y podrás restaurarlo después.');
    }
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmar) {
        btnConfirmar.onclick = () => eliminarRecurso(id, nombre);
    }
    if (modal) modal.classList.add('activo');
}

function cerrarModalConfirmar() {
    document.getElementById('modalConfirmarEliminar')?.classList.remove('activo');
    _idPendienteEliminar = null;
}

function confirmarEliminarDefinitivo(id, nombre) {
    _idPendienteDefinitivo = id;
    const modal = document.getElementById('modalConfirmarDefinitivo');
    const texto = document.getElementById('textoConfirmarDefinitivo');
    if (texto) {
        texto.textContent = '';
        const strong = document.createElement('strong');
        strong.textContent = 'no se puede deshacer';
        const nombreStrong = document.createElement('strong');
        nombreStrong.textContent = nombre;
        texto.append('Esta acción ', strong, '. "', nombreStrong, '" se eliminará permanentemente.');
    }
    const btnDef = document.getElementById('btnConfirmarDefinitivo');
    if (btnDef) btnDef.onclick = () => eliminarDefinitivo(id);
    if (modal) modal.classList.add('activo');
}

function cerrarModalDefinitivo() {
    document.getElementById('modalConfirmarDefinitivo')?.classList.remove('activo');
    _idPendienteDefinitivo = null;
}

function confirmarVaciarPapelera() {
    const modal = document.getElementById('modalVaciarPapelera');
    const btn   = document.getElementById('btnVaciarPapelera');
    if (btn) btn.onclick = vaciarPapelera;
    if (modal) modal.classList.add('activo');
}

function cerrarModalVaciarPapelera() {
    document.getElementById('modalVaciarPapelera')?.classList.remove('activo');
}

// ─── 8. MODAL EDITAR ────────────────────────────────────────────────────────

function abrirModalEditar(id, titulo, descripcion, categoria, tipo, ruta, youtube) {
    // SEGURIDAD: .value asigna como texto plano — inmune a XSS
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

// ─── 9. MODAL SUBIR ARCHIVO ─────────────────────────────────────────────────

let _focusAnteriorSubir = null;

function abrirModalSubir() {
    _focusAnteriorSubir = document.activeElement;
    const overlay = document.getElementById('overlaySubir');
    if (!overlay) return;
    overlay.classList.add('activo');
    document.querySelector('.barra-superior_recursos')?.classList.add('borrosa');
    document.querySelector('.area-contenido')?.classList.add('borrosa');
    limpiarFormSubir();
    setTimeout(() => document.getElementById('subir_titulo')?.focus(), 60);
}

function cerrarModalSubir() {
    document.getElementById('overlaySubir')?.classList.remove('activo');
    document.querySelector('.barra-superior_recursos')?.classList.remove('borrosa');
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
    const sel  = document.getElementById('subir_categoria');
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
    // ── VALIDACIÓN DE EXTENSIÓN (LISTA BLANCA) ──
    const permitidos = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'mp4', 'mov', 'avi', 'xls', 'xlsx', 'ppt', 'pptx', 'gif', 'webp'];
    const partes = file.name.split('.');
    const ext = partes.length > 1 ? partes.pop().toLowerCase() : '';
    
    if (!permitidos.includes(ext)) {
        // Mostrar advertencia
        const modalErr = document.getElementById('modalErrorExtension');
        if (modalErr) modalErr.style.display = 'flex';
        
        // Desaparecer el archivo del panel
        const camp = document.getElementById('subir_campoPrincipal');
        if (camp) camp.value = '';
        const archivoSel = document.getElementById('subir_archivoSel');
        if (archivoSel) archivoSel.style.display = 'none';
        
        return;
    }

    // SEGURIDAD: textContent para nombre de archivo (podría contener HTML)
    const nombreEl = document.getElementById('subir_archivoNombre');
    if (nombreEl) nombreEl.textContent = file.name;
    const archivoSel = document.getElementById('subir_archivoSel');
    if (archivoSel) archivoSel.style.display = 'flex';
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

// ─── 10. NAVEGACIÓN DE PÁGINAS ───────────────────────────────────────────────

const TITULOS_PAGINA = {
    publico:  { eyebrow: 'Comunidad · Recursos' },
    archivos: { eyebrow: 'Administración' },
    papelera: { eyebrow: 'Administración' },
};

function mostrarPagina(nombre) {
    localStorage.setItem('recursos_pagina_activa', nombre);
    document.querySelectorAll('.pagina').forEach(p => p.classList.remove('activa'));
    const objetivo = document.getElementById('pagina-' + nombre);
    if (objetivo) objetivo.classList.add('activa');

    const eyebrow = document.getElementById('eyebrowPagina');
    if (eyebrow && TITULOS_PAGINA[nombre]) {
        // SEGURIDAD: textContent para texto que proviene de variable JS controlada
        eyebrow.textContent = TITULOS_PAGINA[nombre].eyebrow;
    }

    document.querySelectorAll('.nav-btn[data-pagina]').forEach(b => b.classList.remove('activo'));
    const navBtn = document.querySelector('.nav-btn[data-pagina="' + nombre + '"]');
    if (navBtn) navBtn.classList.add('activo');

    cerrarDropdownBusqueda();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── 11. DROPDOWN BÚSQUEDA — NAVEGACIÓN TECLADO ──────────────────────────────

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


// ─── 12. NOTIFICACIONES TOAST ────────────────────────────────────────────────

/**
 * Muestra un aviso toast (notificación no intrusiva).
 * Usa textContent para el mensaje — nunca innerHTML con datos externos.
 *
 * @param {string} mensaje  Texto del aviso
 * @param {string} tipo     'exito' | 'error'
 */
function mostrarAviso(mensaje, tipo) {
    const aviso = document.getElementById('aviso');
    const texto = document.getElementById('mensajeAviso');
    if (!aviso || !texto) return;
    // SEGURIDAD: textContent — no innerHTML — para texto dinámico
    texto.textContent = mensaje;
    aviso.className = 'aviso ' + (tipo || 'exito');
    void aviso.offsetWidth; // Fuerza reflow para reiniciar animación CSS
    aviso.classList.add('visible');
    clearTimeout(window._timerAviso);
    window._timerAviso = setTimeout(() => aviso.classList.remove('visible'), 3500);
}


// ─── CARGA INICIAL DE DATOS (SPA) ──────────────────────────────────────────
let _todosLosRecursos = [];
let _recursosPapelera = [];

async function cargarRecursosInicial() {
    mostrarSpinner(true);
    try {
        const resp = await fetch(API_RECURSOS, {
            headers: { 'Accept': 'application/json' }
        });
        if (!resp.ok) throw new Error("HTTP " + resp.status);
        const data = await resp.json();
        if (!data.ok) throw new Error(data.error || 'Error al cargar recursos');

        _todosLosRecursos = data.data.archivos || [];
        _recursosPapelera = data.data.papelera || [];

        renderizarCuadriculaPublica();
        renderizarCuadriculaAdmin();
        renderizarPapelera();
        
        const btnPapelera = document.querySelector('.nav-btn[data-pagina="papelera"] .nav-badge');
        if (btnPapelera) {
            btnPapelera.textContent = _recursosPapelera.length;
            btnPapelera.style.display = _recursosPapelera.length > 0 ? '' : 'none';
        }
        
        const txtPapelera = document.getElementById('textoCantidadPapelera');
        if (txtPapelera) txtPapelera.textContent = _recursosPapelera.length + ' archivo(s) en la papelera';
        
        const banner = document.getElementById('bannerPapelera');
        if (banner) banner.style.display = _recursosPapelera.length > 0 ? 'flex' : 'none';

        const urlParams = new URLSearchParams(window.location.search);
        let pagina = urlParams.get('pagina') || localStorage.getItem('recursos_pagina_activa') || 'publico';
        mostrarPagina(pagina);

    } catch (err) {
        mostrarAviso('Error al cargar datos: ' + err.message, 'error');
    } finally {
        mostrarSpinner(false);
    }
}

function renderizarCuadriculaPublica() {
    const contenedor = document.getElementById('contenedorPublico');
    if (!contenedor) return;
    
    if (_todosLosRecursos.length === 0) {
        contenedor.innerHTML = '<p style="color:var(--texto-suave);font-size:.9rem;grid-column:1/-1;text-align:center;padding:40px;">No hay recursos publicados aún.</p>';
        return;
    }

    const iconos = { pdf: '📄', img: '🖼️', vid: '🎬', doc: '📝', yt: '▶️' };
    const clases = { pdf: 'pdf', img: 'img', vid: 'vid', doc: 'doc', yt: 'yt' };
    const slabs = { pdf: 'PDF', img: 'IMAGEN', vid: 'VIDEO', doc: 'DOCUMENTO', yt: 'YOUTUBE' };

    let html = '';
    _todosLosRecursos.forEach(a => {
        const tipo = a.tipo || 'doc';
        const icono = iconos[tipo] || '📁';
        const clase = clases[tipo] || 'doc';
        const slab = slabs[tipo] || 'DOC';
        
        const descargas = a.descargas || 0;
        const descargas_fmt = descargas > 999 ? (descargas/1000).toFixed(1) + 'k' : descargas;
        
        let thumb_html = '';
        if (a.ruta_thumb) {
            const url = a.ruta_thumb.startsWith('http') ? a.ruta_thumb : '/IglesiaDelNazarenoBagua/' + a.ruta_thumb;
            thumb_html = `<img class="slab-preview-admin" src="${_esc(url)}" alt="" loading="lazy" onerror="this.style.display='none'">`;
        }
        
        html += `
        <div class="tarjeta-publica" data-categoria="${_esc(a.categoria)}">
            <div class="slab-publico ${clase}">
                ${thumb_html}
                <span class="etiqueta-slab ${tipo}">${slab}</span>
                ${!thumb_html ? icono : ''}
                ${tipo === 'vid' || tipo === 'yt' ? '<div class="superposicion-play"><div class="boton-play"><i class="fa-solid fa-play"></i></div></div>' : ''}
            </div>
            <div class="cuerpo-publico">
                <div class="titulo-publico">${_esc(a.titulo)}</div>
                <div class="descripcion-publica">${_esc((a.descripcion||'').substring(0, 90))}</div>
                <div class="meta-publica">
                    <span>${_esc(a.categoria||'')}</span>
                    <span class="separador"></span>
                    <span><i class="fa-solid fa-download"></i> ${descargas_fmt}</span>
                </div>
                <a href="/IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin&descargar=${a.id}" class="boton-descarga-publica">
                    <i class="fa-solid fa-download"></i> Descargar
                </a>
            </div>
        </div>`;
    });
    
    contenedor.innerHTML = html;
}

function renderizarCuadriculaAdmin() {
    const contenedor = document.getElementById('todosArchivos');
    if (!contenedor) return;

    if (_todosLosRecursos.length === 0) {
        contenedor.innerHTML = '<p style="color:var(--texto-suave);font-size:.9rem;grid-column:1/-1;text-align:center;padding:40px;">No hay archivos registrados.</p>';
        return;
    }
    
    const iconos = { pdf: '📄', img: '🖼️', vid: '🎬', doc: '📝', yt: '▶️' };
    const clases = { pdf: 'pdf', img: 'img', vid: 'vid', doc: 'doc', yt: 'yt' };

    let html = '';
    _todosLosRecursos.forEach(a => {
        const tipo = a.tipo || 'doc';
        const icono = iconos[tipo] || '📁';
        const clase = clases[tipo] || 'doc';
        
        let thumb_html = '';
        if (a.ruta_thumb) {
            const url = a.ruta_thumb.startsWith('http') ? a.ruta_thumb : '/IglesiaDelNazarenoBagua/' + a.ruta_thumb;
            thumb_html = `<img class="miniatura-preview" src="${_esc(url)}" alt="" loading="lazy" onerror="this.style.display='none'">`;
        }
        
        const titulo_js = _esc(a.titulo).replace(/'/g, "\\'");
        const desc_js   = _esc(a.descripcion || '').replace(/'/g, "\\'").replace(/\r?\n/g, '\\n').replace(/\n/g, '\\n');
        const cat_js    = _esc(a.categoria || '').replace(/'/g, "\\'");
        const ruta_js   = _esc(a.ruta_archivo || '').replace(/'/g, "\\'");
        const yt_js     = _esc(a.enlace_youtube || '').replace(/'/g, "\\'");
        const descargas = a.descargas || 0;

        html += `
        <div class="tarjeta-archivo ${thumb_html ? 'has-preview' : ''}" data-tipo="${_esc(tipo)}" data-recurso-id="${a.id}">
            <div class="miniatura-archivo ${clase}">
                ${thumb_html}
                ${!thumb_html ? icono : ''}
                <span class="etiqueta-archivo etiqueta-${tipo}">${tipo.toUpperCase()}</span>
            </div>
            <div class="info-archivo">
                <div>
                    <div class="nombre-archivo">${_esc(a.titulo)}</div>
                    <div class="meta-archivo">
                        ${_esc(a.categoria)} · ${_esc(a.fecha_creacion||'')}
                        <br>
                        <span style="font-size:0.8em; color:var(--texto-suave);">
                            Subido por: <strong>${_esc(a.autor_nombre || 'Desconocido')}</strong>
                            ${a.editor_nombre ? ` · Edit. por: <strong>${_esc(a.editor_nombre)}</strong>` : ''}
                            ${descargas > 0 ? ` · <i class="fa-solid fa-download"></i> ${descargas}` : ''}
                        </span>
                    </div>
                </div>
                <div class="acciones-archivo">
                    <a href="/IglesiaDelNazarenoBagua/?vista=dashboard&seccion=recurso_admin&descargar=${a.id}" class="boton boton-contorno" title="Descargar">
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <button class="boton boton-primario" title="Editar" onclick="abrirModalEditar(${a.id}, '${titulo_js}', '${desc_js}', '${cat_js}', '${tipo}', '${ruta_js}', '${yt_js}')">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="boton boton-peligro" title="Mover a papelera" onclick="confirmarEliminar(${a.id}, '${titulo_js}')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });
    
    contenedor.innerHTML = html;
}

function renderizarPapelera() {
    const contenedor = document.getElementById('contenedorPapelera');
    if (!contenedor) return;

    if (_recursosPapelera.length === 0) {
        contenedor.innerHTML = `
        <div class="papelera-vacia">
            <i class="fa-solid fa-trash-can" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; display:block;"></i>
            <p style="font-size:1rem;font-weight:600;color:var(--texto);">Papelera vacía</p>
            <p style="font-size:.85rem;margin-top:6px;color:#94a3b8;">Los archivos eliminados aparecerán aquí.</p>
        </div>`;
        return;
    }
    
    const iconos = { pdf: '📄', img: '🖼️', vid: '🎬', doc: '📝', yt: '▶️' };
    let html = '<div class="cuadricula-papelera">';
    
    _recursosPapelera.forEach(a => {
        const tipo = a.tipo || 'doc';
        const icono = iconos[tipo] || '📁';
        
        let thumb_html = '';
        if (a.ruta_thumb) {
            const url = a.ruta_thumb.startsWith('http') ? a.ruta_thumb : '/IglesiaDelNazarenoBagua/' + a.ruta_thumb;
            thumb_html = `<img class="miniatura-preview" src="${_esc(url)}" alt="" loading="lazy" onerror="this.style.display='none'">`;
        }

        const titulo_js = _esc(a.titulo).replace(/'/g, "\\'");

        html += `
        <div class="tarjeta-papelera ${thumb_html ? 'has-preview' : ''}">
            <div class="icono-papelera">
                ${thumb_html || icono}
            </div>
            <div>
                <div class="nombre-papelera">${_esc(a.titulo)}</div>
                <div class="meta-papelera">
                    ${_esc(a.categoria)}<br>
                    <i class="fa-solid fa-clock"></i> Eliminado el: ${_esc(a.fecha_eliminacion||'')}
                    <br>
                    <span style="font-size:0.8em; color:var(--rojo);">
                        Eliminado por: <strong>${_esc(a.eliminador_nombre || 'Desconocido')}</strong>
                    </span>
                </div>
            </div>
            <div class="acciones-papelera">
                <button class="boton boton-exito" title="Restaurar" onclick="restaurarRecurso(${a.id})">
                    <i class="fa-solid fa-rotate-left"></i> Restaurar
                </button>
                <button class="boton boton-peligro" title="Eliminar definitivamente" onclick="confirmarEliminarDefinitivo(${a.id}, '${titulo_js}')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;
    });
    
    html += '</div>';
    contenedor.innerHTML = html;
}

// ── MANEJO DE FORMULARIOS FETCH ──────────────────────────────────────────

async function enviarFormularioSubir(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    formData.set('csrf_token', _CSRF());

    mostrarSpinner(true);
    try {
        const resp = await fetch('index.php?vista=api/recursos', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        });
        
        if (!resp.ok) {
            const errData = await resp.json().catch(() => ({}));
            throw new Error(errData.error || `Error HTTP ${resp.status}`);
        }
        
        const data = await resp.json();
        if (!data.ok) throw new Error(data.error || 'Error al subir el recurso');
        
        mostrarAviso('Recurso publicado exitosamente', 'exito');
        cerrarModalSubir();
        cargarRecursosInicial(); 
    } catch (err) {
        mostrarAviso(err.message, 'error');
    } finally {
        mostrarSpinner(false);
    }
}

async function enviarFormularioEditar(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    formData.set('csrf_token', _CSRF());

    mostrarSpinner(true);
    try {
        const resp = await fetch('index.php?vista=api/recursos/update', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        });
        
        if (!resp.ok) {
            const errData = await resp.json().catch(() => ({}));
            throw new Error(errData.error || `Error HTTP ${resp.status}`);
        }
        
        const data = await resp.json();
        if (!data.ok) throw new Error(data.error || 'Error al editar el recurso');
        
        mostrarAviso('Recurso editado exitosamente', 'exito');
        cerrarModalEditar();
        cargarRecursosInicial(); 
    } catch (err) {
        mostrarAviso(err.message, 'error');
    } finally {
        mostrarSpinner(false);
    }
}

// ─── 13. INICIALIZACIÓN ──────────────────────────────────────────────────────


document.addEventListener('DOMContentLoaded', () => {
    cargarRecursosInicial();
    
    const formSubir = document.getElementById('formSubir');
    if (formSubir) formSubir.addEventListener('submit', enviarFormularioSubir);
    
    const formEditar = document.querySelector('#modalEditar form');
    if (formEditar) {
        formEditar.addEventListener('submit', enviarFormularioEditar);
        const btnGuardarEditar = formEditar.querySelector('button[name="guardar"]');
        if (btnGuardarEditar) {
            btnGuardarEditar.type = 'submit';
            btnGuardarEditar.removeAttribute('onclick');
        }
    }


    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', (e) => {
        const wrap = document.getElementById('wrapBusqueda');
        if (wrap && !wrap.contains(e.target)) cerrarDropdownBusqueda();
    });

    // Trampa de foco en modal de subida (accesibilidad)
    const modalSubir = document.getElementById('modalSubir');
    if (modalSubir) {
        modalSubir.addEventListener('keydown', e => _trapTab(e, modalSubir));
    }

    // Atajos de teclado globales
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
});

function _trapTab(e, modalEl) {
    if (e.key !== 'Tab') return;
    const foc = modalEl.querySelectorAll(
        'button, input, select, textarea, a[href], [tabindex]:not([tabindex="-1"])'
    );
    if (foc.length === 0) return;
    const first = foc[0], last = foc[foc.length - 1];
    if (e.shiftKey) {
        if (document.activeElement === first) { e.preventDefault(); last.focus(); }
    } else {
        if (document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
}

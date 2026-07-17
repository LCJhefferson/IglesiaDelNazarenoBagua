// ── Variable para guardar el id del usuario a eliminar ──
let idUsuarioEliminar = null;

// ══════════════════════════════
//  FILTROS DE LA TABLA
// ══════════════════════════════
function actualizarContador() {
    const filas  = document.querySelectorAll('#cuerpoTabla tr');
    let visibles = 0;
    filas.forEach(fila => { if (fila.style.display !== 'none') visibles++; });
    document.getElementById('filasMostradas').textContent = visibles;
}

function aplicarFiltros() {
    const textoBusqueda   = document.querySelector('.campo-busqueda').value.toLowerCase();
    const estadoSeleccion = document.querySelectorAll('.selector-filtro')[0].value;
    const rolSeleccion    = document.querySelectorAll('.selector-filtro')[1].value;

    document.querySelectorAll('#cuerpoTabla tr').forEach(fila => {
        const nombre = fila.dataset.nombre || '';
        const correo = fila.dataset.correo || '';
        const estado = fila.dataset.estado || '';
        const rol    = fila.dataset.rol    || '';

        const coincideTexto  = nombre.includes(textoBusqueda) || correo.includes(textoBusqueda);
        const coincideEstado = estadoSeleccion === 'todos' || estado === estadoSeleccion;
        const coincideRol    = rolSeleccion    === 'todos' || rol    === rolSeleccion;

        fila.style.display = (coincideTexto && coincideEstado && coincideRol) ? '' : 'none';
    });
    actualizarContador();
}

function filtrarUsuarios(valor) { aplicarFiltros(); }
function filtrarPorEstado(valor) { aplicarFiltros(); }
function filtrarPorRol(valor)    { aplicarFiltros(); }

// ══════════════════════════════
//  MODAL: CREAR USUARIO
// ══════════════════════════════
function abrirModalCrear() {
    document.getElementById('crearUsername').value = '';
    document.getElementById('crearPassword').value = '';
    document.getElementById('crearRol').value      = '';
    document.getElementById('crearEstado').value   = 'activo';
    document.getElementById('modalCrear').classList.add('abierto'); 
}

function cerrarModalCrear() {
    document.getElementById('modalCrear').classList.remove('abierto');
}

// ══════════════════════════════
//  MODAL: EDITAR USUARIO
// ══════════════════════════════
function abrirModalEditar(id, username, rol, estado) {
    document.getElementById('editarId').value       = id;
    document.getElementById('editarUsername').value = username;
    document.getElementById('editarRol').value      = rol;
    document.getElementById('editarEstado').value   = estado;
    document.getElementById('modalEditar').classList.add('abierto');
}

function cerrarModalEditar() {
    document.getElementById('modalEditar').classList.remove('abierto');
}

// ══════════════════════════════
//  MODAL: ELIMINAR (DESACTIVAR) USUARIO
// ══════════════════════════════
function abrirModalEliminar(id, nombre) {
    const inputId = document.getElementById('desactivarId');
    if (inputId) {
        inputId.value = id;
    }
    
    const texto = document.getElementById('textoEliminar');
    if (texto) {
        texto.textContent = 'El usuario "' + nombre + '" pasará a estado inactivo de forma lógica.';
    }
    
    document.getElementById('modalEliminar').classList.add('abierto');
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminar').classList.remove('abierto');
}

// ══════════════════════════════
//  AVISO FLOTANTE (TOAST)
// ══════════════════════════════
function mostrarAviso(mensaje, tipo = 'exito') {
    const cajaAviso  = document.getElementById('aviso');
    const iconoAviso = cajaAviso.querySelector('i');
    document.getElementById('mensajeAviso').textContent = mensaje;
    cajaAviso.className = 'aviso ' + tipo;
    iconoAviso.className = tipo === 'exito'
        ? 'fa-solid fa-circle-check'
        : 'fa-solid fa-circle-xmark';
    cajaAviso.classList.add('visible');
    setTimeout(() => cajaAviso.classList.remove('visible'), 2800);
}

// ── Cerrar modales dinámicos al hacer clic fuera (CÓDIGO UNIFICADO Y SEGURO) ──
['modalCrear', 'modalEditar', 'modalEliminar', 'modalPassword', 'modalBitacora'].forEach(idModal => {
    const modalEl = document.getElementById(idModal);
    if (modalEl) {
        modalEl.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('abierto');
        });
    }
});

// ── Mostrar aviso si viene por GET ──
const params = new URLSearchParams(window.location.search);

if (params.get('exito') == 1) {
    mostrarAviso('¡Operación completada correctamente! ✅', 'exito');
    limpiarParametrosUrl();
} else if (params.get('error') == 1) {
    mostrarAviso('Hubo un inconveniente al procesar la solicitud ❌', 'error');
    limpiarParametrosUrl();
}

function limpiarParametrosUrl() {
    if (window.history.replaceState) {
        const url = new URL(window.location.href);
        url.searchParams.delete('exito');
        url.searchParams.delete('error');
        window.history.replaceState({ path: url.href }, '', url.href);
    }
}

// ══════════════════════════════
//  MODAL: RESTABLECER CONTRASEÑA
// ══════════════════════════════
function abrirModalPassword(id, nombre) {
    document.getElementById('passId').value = id;
    document.getElementById('textoPassword').textContent = 'Se asignará una nueva clave al usuario "' + nombre + '".';
    document.getElementById('modalPassword').classList.add('abierto');
}

function cerrarModalPassword() {
    document.getElementById('modalPassword').classList.remove('abierto');
}

// ══════════════════════════════
//  MODAL: VER BITÁCORA VIA AJAX
// ══════════════════════════════
function abrirModalBitacora(id, nombre) {
    document.getElementById('nombreUsuarioBitacora').textContent = nombre;
    const lista = document.getElementById('listaBitacora');

    lista.textContent = 'Buscando actividades...';
    document.getElementById('modalBitacora').classList.add('abierto');

    fetch('?seccion=usuarios_admin&obtener_bitacora=1&usuario_id=' + id)
        .then(response => response.json())
        .then(data => {
            lista.textContent = '';

            if (data.length === 0) {
                lista.textContent = 'No se registran actividades para este usuario de momento.';
                return;
            }

            const ul = document.createElement('ul');
            ul.style.listStyle = 'none';
            ul.style.padding = '0';
            ul.style.margin = '0';
            ul.style.fontSize = '0.9rem';

            data.forEach(log => {
                const li = document.createElement('li');
                li.style.padding = '10px';
                li.style.borderBottom = '1px solid #eee';
                li.style.display = 'flex';
                li.style.flexDirection = 'column';
                li.style.gap = '2px';

                const accion = document.createElement('span');
                accion.style.color = '#333';
                accion.style.fontWeight = '500';
                accion.textContent = log.accion;

                const fecha = document.createElement('span');
                fecha.style.color = '#999';
                fecha.style.fontSize = '0.75rem';
                fecha.textContent = '🕒 ' + log.fecha;

                li.appendChild(accion);
                li.appendChild(fecha);
                ul.appendChild(li);
            });

            lista.appendChild(ul);
        })
        .catch(err => {
            lista.textContent = 'Error al cargar la bitácora.';
        });
}

function cerrarModalBitacora() {
    document.getElementById('modalBitacora').classList.remove('abierto');
}
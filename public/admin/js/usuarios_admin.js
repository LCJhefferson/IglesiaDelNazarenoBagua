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
//  MODAL: ELIMINAR USUARIO
// ══════════════════════════════
function abrirModalEliminar(id, nombre) {
    idUsuarioEliminar = id;
    document.getElementById('textoEliminar').textContent =
        'Se eliminará permanentemente a "' + nombre + '". Esta acción no se puede deshacer.';
    document.getElementById('modalEliminar').classList.add('abierto');
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminar').classList.remove('abierto');
    idUsuarioEliminar = null;
}

function confirmarEliminar() {
    if (idUsuarioEliminar) {
        window.location.href = 'eliminar_usuario.php?id=' + idUsuarioEliminar;
    }
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

// ── Cerrar modales al hacer clic fuera ──
['modalCrear', 'modalEditar', 'modalEliminar'].forEach(idModal => {
    document.getElementById(idModal).addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('abierto');
    });
});

// ── Mostrar aviso si viene por GET ──
const params = new URLSearchParams(window.location.search);
if (params.get('exito') == 1) mostrarAviso('Usuario creado correctamente ✅', 'exito');
if (params.get('error') == 1) mostrarAviso('Error al crear usuario ❌', 'error');
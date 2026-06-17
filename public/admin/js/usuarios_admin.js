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
    // CORRECCIÓN: Cambiado de 'activo' a 'abierto'
    document.getElementById('modalCrear').classList.add('abierto'); 
}

function cerrarModalCrear() {
    // CORRECCIÓN: Cambiado de 'activo' a 'abierto'
    document.getElementById('modalCrear').classList.remove('abierto');
}

// ══════════════════════════════
//  MODAL: EDITAR USUARIO (Sirve también para reactivar usuarios)
// ══════════════════════════════
function abrirModalEditar(id, username, rol, estado) {
    document.getElementById('editarId').value       = id;
    document.getElementById('editarUsername').value = username;
    document.getElementById('editarRol').value      = rol;
    document.getElementById('editarEstado').value   = estado;
    // CORRECCIÓN: Cambiado de 'activo' a 'abierto'
    document.getElementById('modalEditar').classList.add('abierto');
}

function cerrarModalEditar() {
    // CORRECCIÓN: Cambiado de 'activo' a 'abierto'
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
        texto.textContent = 'El usuario "' + nombre + '" pasará a estado inactivo.';
    }
    
    document.getElementById('modalEliminar').classList.add('abierto');
}

function cerrarModalEliminar() {
    // CORRECCIÓN: Cambiado de 'activo' a 'abierto' para que el botón Cancelar funcione
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

// ── Cerrar modales al hacer clic fuera ──
['modalCrear', 'modalEditar', 'modalEliminar'].forEach(idModal => {
    document.getElementById(idModal).addEventListener('click', function(e) {
        // CORRECCIÓN: Al hacer clic fuera se remueve 'abierto'
        if (e.target === this) this.classList.remove('abierto');
    });
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
    lista.innerHTML = '<p style="text-align:center; color:#888; padding: 10px;">Buscando actividades...</p>';
    
    document.getElementById('modalBitacora').classList.add('abierto');
    
    // Hacemos una petición silenciosa en segundo plano al servidor
    fetch('?seccion=usuarios_admin&obtener_bitacora=1&usuario_id=' + id)
        .then(response => response.json())
        .then(data => {
            if(data.length === 0) {
                lista.innerHTML = '<p style="text-align:center; color:#999; padding:15px;">No se registran actividades para este usuario de momento.</p>';
                return;
            }
            
            let html = '<ul style="list-style:none; padding:0; margin:0; font-size:0.9rem;">';
            data.forEach(log => {
                html += '<li style="padding: 10px; border-bottom:1px solid #eee; display:flex; flex-direction:column; gap:2px;">' +
                            '<span style="color:#333; font-weight:500;">' + log.accion + '</span>' +
                            '<span style="color:#999; font-size:0.75rem;"><i class="fa-regular fa-clock"></i> ' + log.fecha + '</span>' +
                        '</li>';
            });
            html += '</ul>';
            lista.innerHTML = html;
        })
        .catch(err => {
            lista.innerHTML = '<p style="text-align:center; color:red; padding:10px;">Error al cargar la bitácora.</p>';
        });
}

function cerrarModalBitacora() {
    document.getElementById('modalBitacora').classList.remove('abierto');
}

// ── Actualizar cierre de modales al hacer clic fuera ──
['modalCrear', 'modalEditar', 'modalPassword', 'modalBitacora'].forEach(idModal => {
    const modalEl = document.getElementById(idModal);
    if(modalEl) {
        modalEl.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('abierto');
        });
    }
});
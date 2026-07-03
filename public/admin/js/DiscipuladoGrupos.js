document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $('.select2').length) {
        $('.select2').select2({
            dropdownParent: $('#modalGrupo'),
            placeholder: "Seleccione un líder..."
        });
    }
});

function abrirModalGrupo() {
    const modal = document.getElementById('modalGrupo');
    const form = document.getElementById('formGrupo');
    
    if (!form) return;

    form.reset(); 
 
    document.getElementById('grupo_id').value = ""; 
    
    // Protección XSS: usamos textContent en lugar de innerHTML
    const modalTitulo = document.getElementById('modalTitulo');
    modalTitulo.textContent = "";  // limpia el contenido de forma segura

    // Creamos el ícono con createElement (seguro contra inyección)
    const icono = document.createElement("i");
    icono.className = "fas fa-plus-circle";

    modalTitulo.appendChild(icono);
    // CORRECCIÓN XSS: usamos append con texto plano
    modalTitulo.append(" Crear Nuevo Grupo");
    
    const btnGuardar = document.getElementById('btnGuardarAction');
    btnGuardar.name = "registrar_grupo"; 
    // CORRECCIÓN XSS: antes innerText → ahora textContent
    btnGuardar.textContent = "Guardar Grupo";

    if (typeof $ !== 'undefined' && $('#discipulador_id').data('select2')) {
        $('#discipulador_id').val('').trigger('change');
    }

    modal.style.display = 'flex';
}


/**
 * @param {Object} datos 
 */
function editarGrupo(datos) {
    const modal = document.getElementById('modalGrupo');
    if (!modal) return;

    // CORRECCIÓN XSS: antes innerHTML → ahora textContent + createElement
    const modalTitulo = document.getElementById('modalTitulo');
    modalTitulo.textContent = ""; // limpia contenido seguro

    const icono = document.createElement("i");
    icono.className = "fas fa-edit";
    modalTitulo.appendChild(icono);
    modalTitulo.append(" Editar Configuración de Grupo");

    document.getElementById('grupo_id').value = datos.id;
    document.getElementById('nombre_grupo').value = datos.nombre;
    document.getElementById('nivel_grupo').value = datos.nivel;
    document.getElementById('estado_id').value = datos.estado_id;

    const selectD = document.getElementById('discipulador_id');
    if (selectD) {
        const idLider = datos.discipulador_id || datos.lider_id || (datos.discipulador ? datos.discipulador.id : '');
        selectD.value = idLider;
        
        if (typeof $ !== 'undefined' && $(selectD).hasClass('select2-hidden-accessible')) {
            $(selectD).trigger('change');
        }
    }

    const btnGuardar = document.getElementById('btnGuardarAction');
    btnGuardar.name = "editar_grupo";
    // CORRECCIÓN XSS: antes innerText → ahora textContent
    btnGuardar.textContent = "Actualizar Cambios";

    modal.style.display = 'flex';
}

function cerrarModalGrupo() {
    const modal = document.getElementById('modalGrupo');
    if (modal) {
        modal.style.display = 'none';
    }
}



/**
 * @param {Object} datos 
 */
function editarGrupo(datos) {
    const modal = document.getElementById('modalGrupo');
    if (!modal) return;

    // CORRECCIÓN XSS: antes innerHTML → ahora textContent + createElement
    const modalTitulo = document.getElementById('modalTitulo');
    modalTitulo.textContent = ""; // limpia contenido seguro

    const icono = document.createElement("i");
    icono.className = "fas fa-edit";
    modalTitulo.appendChild(icono);
    // CORRECCIÓN XSS: añadimos texto plano con append
    modalTitulo.append(" Editar Configuración de Grupo");

    // Asignación segura de valores a inputs
    document.getElementById('grupo_id').value = datos.id || "";
    document.getElementById('nombre_grupo').value = datos.nombre || "";
    document.getElementById('nivel_grupo').value = datos.nivel || "";
    document.getElementById('estado_id').value = datos.estado_id || "";

    const selectD = document.getElementById('discipulador_id');
    if (selectD) {
        const idLider = datos.discipulador_id || datos.lider_id || (datos.discipulador ? datos.discipulador.id : '');
        selectD.value = idLider;
        
        if (typeof $ !== 'undefined' && $(selectD).hasClass('select2-hidden-accessible')) {
            $(selectD).trigger('change');
        }
    }

    const btnGuardar = document.getElementById('btnGuardarAction');
    btnGuardar.name = "editar_grupo";
    // CORRECCIÓN XSS: antes innerText → ahora textContent
    btnGuardar.textContent = "Actualizar Cambios";

    modal.style.display = 'flex';
}

function cerrarModalGrupo() {
    const modal = document.getElementById('modalGrupo');
    if (modal) {
        modal.style.display = 'none';
    }
}


/**
 * @param {number} id - ID del grupo a eliminar
 * @param {string} nombre - Nombre del grupo para mostrar en el mensaje
 */
function confirmarEliminarGrupo(id, nombre) {
    const modal = document.getElementById('modalConfirmarEliminar');
    const txtNombre = document.getElementById('nombreGrupoEliminar');
    const btnEliminar = document.getElementById('enlaceEliminarSeguro');

    if (modal && txtNombre && btnEliminar) {
        // CORRECCIÓN XSS: usamos textContent para mostrar el nombre de forma segura
        txtNombre.textContent = nombre;

        // Construcción segura del enlace con encodeURIComponent para el token
        btnEliminar.href = `?seccion=DiscipuladoGrupos&eliminar_grupo=${encodeURIComponent(id)}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`;

        modal.style.display = 'flex';
    }
}

/**
 * Cierra el modal de confirmación de eliminación
 */
function cerrarModalEliminar() {
    const modal = document.getElementById('modalConfirmarEliminar');
    if (modal) {
        modal.style.display = 'none';
    }
}

window.onclick = function(event) {
    const modalGrupo = document.getElementById('modalGrupo');
    const modalEliminar = document.getElementById('modalConfirmarEliminar');
    
    if (event.target === modalGrupo) {
        cerrarModalGrupo();
    }
    if (event.target === modalEliminar) {
        cerrarModalEliminar();
    }
}
/**
 * @param {string} mensaje - Texto a mostrar
 * @param {string} tipo - 'success', 'error', o 'info'
 */
function mostrarNotificacion(mensaje, tipo = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;

    // Crear ícono seguro con createElement
    const icono = document.createElement('i');
    if (tipo === 'success') {
        icono.className = "fas fa-check-circle";
        icono.style.color = "#22c55e";
    } else if (tipo === 'error') {
        icono.className = "fas fa-times-circle";
        icono.style.color = "#ef4444";
    } else if (tipo === 'info') {
        icono.className = "fas fa-info-circle";
        icono.style.color = "#3b82f6";
    }

    // Crear span para el mensaje
    const spanMensaje = document.createElement('span');
    // CORRECCIÓN XSS: usamos textContent en lugar de innerHTML
    spanMensaje.textContent = mensaje;

    // Construir el toast de forma segura
    toast.appendChild(icono);
    toast.appendChild(spanMensaje);

    container.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 50);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function limpiarFiltros() {
    document.getElementById('buscarGrupo').value = "";
    document.getElementById('filtroNivel').value = "";
    document.getElementById('filtroDiscipulador').value = "";
    document.getElementById('filtroEstado').value = "";

    filtrarGrupos();
}

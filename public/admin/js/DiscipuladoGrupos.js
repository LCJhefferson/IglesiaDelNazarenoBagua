document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $('.select2').length) {
        $('.select2').select2({
            dropdownParent: $('#modalGrupo'),
            placeholder: "Seleccione un líder..."
        });
    }
});

/**
 * Abre el modal en modo "Crear Nuevo Grupo"
 */
function abrirModalGrupo() {
    const modal = document.getElementById('modalGrupo');
    const form = document.getElementById('formGrupo');
    
    if (!form || !modal) return;

    form.reset(); 

    document.getElementById('grupo_id').value = ""; 
    
    // Protección XSS: Manipulación segura del DOM
    const modalTitulo = document.getElementById('modalTitulo');
    modalTitulo.textContent = "";

    const icono = document.createElement("i");
    icono.className = "fas fa-plus-circle";

    modalTitulo.appendChild(icono);
    modalTitulo.append(" Crear Nuevo Grupo");
    
    const btnGuardar = document.getElementById('btnGuardarAction');
    btnGuardar.name = "registrar_grupo"; 
    btnGuardar.textContent = "Guardar Grupo";

    if (typeof $ !== 'undefined' && $('#discipulador_id').data('select2')) {
        $('#discipulador_id').val('').trigger('change');
    }

    modal.style.display = 'flex';
}

/**
 * Abre el modal en modo "Editar Grupo" extrayendo datos del botón o de un objeto
 * @param {HTMLElement|Object} elemento - Botón HTML con atributo data-grupo u objeto con los datos
 */
function editarGrupo(elemento) {
    const modal = document.getElementById('modalGrupo');
    if (!modal) return;

    let datos;
    if (elemento instanceof HTMLElement) {
        try {
            datos = JSON.parse(elemento.getAttribute('data-grupo'));
        } catch (e) {
            console.error("Error al parsear JSON del grupo:", e);
            return;
        }
    } else {
        datos = elemento;
    }

    if (!datos) return;

    // Configurar título de forma segura
    const modalTitulo = document.getElementById('modalTitulo');
    modalTitulo.textContent = ""; 

    const icono = document.createElement("i");
    icono.className = "fas fa-edit";
    modalTitulo.appendChild(icono);
    modalTitulo.append(" Editar Configuración de Grupo");

    // Cargar datos en el formulario
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
    btnGuardar.textContent = "Actualizar Cambios";

    modal.style.display = 'flex';
}

/**
 * Cierra el modal de grupo
 */
function cerrarModalGrupo() {
    const modal = document.getElementById('modalGrupo');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Abre el modal de confirmación de eliminación de grupo de forma segura
 * @param {HTMLElement} boton - Botón de eliminación que contiene data-id y data-nombre
 */
function confirmarEliminarGrupo(boton) {
    const modal = document.getElementById('modalConfirmarEliminar');
    const txtNombre = document.getElementById('nombreGrupoEliminar');
    const btnEliminar = document.getElementById('enlaceEliminarSeguro');

    const id = boton.getAttribute('data-id');
    const nombre = boton.getAttribute('data-nombre');

    // Captura el token CSRF generado en el formulario PHP
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    const tokenReal = tokenInput ? tokenInput.value : '';

    if (modal && txtNombre && btnEliminar) {
        txtNombre.textContent = nombre; // Evita inyección XSS
        btnEliminar.href = `?seccion=DiscipuladoGrupos&eliminar_grupo=${encodeURIComponent(id)}&csrf_token=${encodeURIComponent(tokenReal)}`;
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

// Cierre de modales al hacer clic fuera del contenido
window.onclick = function(event) {
    const modalGrupo = document.getElementById('modalGrupo');
    const modalEliminar = document.getElementById('modalConfirmarEliminar');
    
    if (event.target === modalGrupo) {
        cerrarModalGrupo();
    }
    if (event.target === modalEliminar) {
        cerrarModalEliminar();
    }
};

/**
 * Muestra notificaciones flotantes (Toasts)
 * @param {string} mensaje - Texto a mostrar
 * @param {string} tipo - 'success', 'error', o 'info'
 */
function mostrarNotificacion(mensaje, tipo = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;

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

    const spanMensaje = document.createElement('span');
    spanMensaje.textContent = mensaje; // Prevención XSS

    toast.appendChild(icono);
    toast.appendChild(spanMensaje);

    container.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 50);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

/**
 * Filtro básico para buscar en el listado de grupos
 */
function filtrarGrupos() {
    const buscar = document.getElementById('buscarGrupo')?.value.toLowerCase() || '';
    const nivel = document.getElementById('filtroNivel')?.value || '';
    const discipulador = document.getElementById('filtroDiscipulador')?.value.toLowerCase() || '';
    const estado = document.getElementById('filtroEstado')?.value.toLowerCase() || '';

    const tarjetas = document.querySelectorAll('.card-grupo');

    tarjetas.forEach(card => {
        const cNombre = card.getAttribute('data-nombre') || '';
        const cNivel = card.getAttribute('data-nivel') || '';
        const cLider = card.getAttribute('data-lider') || '';
        const cEstado = card.getAttribute('data-estado') || '';

        const coincideNombre = cNombre.includes(buscar);
        const coincideNivel = !nivel || cNivel === nivel;
        const coincideLider = !discipulador || cLider.includes(discipulador);
        const coincideEstado = !estado || cEstado.includes(estado);

        if (coincideNombre && coincideNivel && coincideLider && coincideEstado) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function limpiarFiltros() {
    if (document.getElementById('buscarGrupo')) document.getElementById('buscarGrupo').value = "";
    if (document.getElementById('filtroNivel')) document.getElementById('filtroNivel').value = "";
    if (document.getElementById('filtroDiscipulador')) document.getElementById('filtroDiscipulador').value = "";
    if (document.getElementById('filtroEstado')) document.getElementById('filtroEstado').value = "";

    filtrarGrupos();
}
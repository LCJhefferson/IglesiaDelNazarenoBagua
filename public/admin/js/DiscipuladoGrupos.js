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
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-plus-circle"></i> Crear Nuevo Grupo';
    
    const btnGuardar = document.getElementById('btnGuardarAction');
    btnGuardar.name = "registrar_grupo"; 
    btnGuardar.innerText = "Guardar Grupo";

    if (typeof $ !== 'undefined' && $('#discipulador_id').data('select2')) {
        $('#discipulador_id').val('').trigger('change');
    }

    modal.style.display = 'flex';
}

/**
 * @param {HTMLElement} boton - El elemento botón que recibió el click
 */
function editarGrupo(boton) {
    const modal = document.getElementById('modalGrupo');
    if (!modal) return;

    // 1. EXTRAER Y PARSEAR EL JSON DESDE EL ATRIBUTO DATA
    const datos = JSON.parse(boton.getAttribute('data-grupo'));
    if (!datos) return;

    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit"></i> Editar Configuración de Grupo';
    
    // 2. LLENAR LOS CAMPOS USANDO LOS DATOS RECONSTRUIDOS
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
    btnGuardar.innerText = "Actualizar Cambios";

    modal.style.display = 'flex';
}


function cerrarModalGrupo() {
    const modal = document.getElementById('modalGrupo');
    if (modal) {
        modal.style.display = 'none';
    }
}


function filtrarGrupos() {
    const busqueda = document.getElementById('buscarGrupo').value.toLowerCase().trim();
    const nivelFiltro = document.getElementById('filtroNivel').value.toLowerCase().trim();
    const liderFiltro = document.getElementById('filtroDiscipulador').value.toLowerCase().trim();
    const estadoFiltro = document.getElementById('filtroEstado').value.toLowerCase().trim(); // <-- NUEVO
    
    const cards = document.querySelectorAll('.card-grupo');

    cards.forEach(card => {
        const nombreGrupo = card.getAttribute('data-nombre') || "";
        const nivelTexto = card.getAttribute('data-nivel').toLowerCase().trim();
        const liderTexto = card.getAttribute('data-lider') || "";
        const estadoTexto = card.getAttribute('data-estado') || ""; // <-- NUEVO

        const coincideNombre = nombreGrupo.includes(busqueda);
        const coincideNivel = (nivelFiltro === "") || (nivelTexto === nivelFiltro);
        const coincideLider = (liderFiltro === "") || (liderTexto.includes(liderFiltro));
        const coincideEstado = (estadoFiltro === "") || (estadoTexto === estadoFiltro); // <-- NUEVO

        if (coincideNombre && coincideNivel && coincideLider && coincideEstado) {
            card.style.display = "";
            card.style.opacity = "1";
        } else {
            card.style.display = "none";
            card.style.opacity = "0";
        }
    });
}

/**
} * @param {number} id - ID del grupo a eliminar
 * @param {string} nombre - Nombre del grupo para mostrar en el mensaje
 */
/**
 * @param {HTMLElement} boton - El botón de eliminar que recibió el click
 */
function confirmarEliminarGrupo(boton) {
    const modal = document.getElementById('modalConfirmarEliminar');
    const txtNombre = document.getElementById('nombreGrupoEliminar');
    const btnEliminar = document.getElementById('enlaceEliminarSeguro');

    // 1. Obtener los datos limpios desde los atributos data del HTML
    const id = boton.getAttribute('data-id');
    const nombre = boton.getAttribute('data-nombre');

    // 2. Capturar el token CSRF real que está en el formulario del modal
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    const tokenReal = tokenInput ? tokenInput.value : '';

    if (modal && txtNombre && btnEliminar) {
        txtNombre.textContent = nombre; // Setea el texto de manera segura
        
        // Armamos la URL usando el token del formulario real de la sesión
        btnEliminar.href = `?seccion=DiscipuladoGrupos&eliminar_grupo=${id}&csrf_token=${encodeURIComponent(tokenReal)}`;
        
        modal.style.display = 'flex';
    }
}

/**
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
    
    let icono = '<i class="fas fa-check-circle" style="color: #22c55e;"></i>';
    if (tipo === 'error') icono = '<i class="fas fa-times-circle" style="color: #ef4444;"></i>';
    if (tipo === 'info') icono = '<i class="fas fa-info-circle" style="color: #3b82f6;"></i>';

    toast.innerHTML = `${icono} <span>${mensaje}</span>`;
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
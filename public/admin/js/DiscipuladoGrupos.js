/**
 * Lógica para la gestión de Grupos de Discipulado (Versión CARDS)
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Select2 si la librería está cargada y hay elementos con esa clase
    if (typeof $ !== 'undefined' && $('.select2').length) {
        $('.select2').select2({
            dropdownParent: $('#modalGrupo'),
            placeholder: "Seleccione un líder..."
        });
    }
});

/**
 * Prepara el modal para crear un grupo nuevo
 */
function abrirModalGrupo() {
    const modal = document.getElementById('modalGrupo');
    const form = document.getElementById('formGrupo');
    
    if (!form) return;

    form.reset(); // Limpia todos los campos
    
    // El ID debe estar vacío para que el controlador (PHP) ejecute INSERT y no UPDATE
    document.getElementById('grupo_id').value = ""; 
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-plus-circle"></i> Crear Nuevo Grupo';
    
    // Cambiamos el name del botón para que el Controlador sepa qué acción ejecutar
    const btnGuardar = document.getElementById('btnGuardarAction');
    btnGuardar.name = "registrar_grupo"; 
    btnGuardar.innerText = "Guardar Grupo";

    // Si usas Select2, hay que resetearlo visualmente
    if (typeof $ !== 'undefined' && $('#discipulador_id').data('select2')) {
        $('#discipulador_id').val('').trigger('change');
    }

    modal.style.display = 'flex';
}

/**
 * Prepara el modal con los datos del grupo para editar
 * @param {Object} datos - Objeto JSON con la información del grupo
 */
function editarGrupo(datos) {
    const modal = document.getElementById('modalGrupo');
    if (!modal) return;

    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit"></i> Editar Configuración de Grupo';
    
    // Llenar los campos ocultos y visibles
    document.getElementById('grupo_id').value = datos.id;
    document.getElementById('nombre_grupo').value = datos.nombre;
    document.getElementById('nivel_grupo').value = datos.nivel;
    document.getElementById('estado_id').value = datos.estado_id;

    const selectD = document.getElementById('discipulador_id');
    if (selectD) {
        // Obtenemos el ID de forma a prueba de fallos
        const idLider = datos.discipulador_id || datos.lider_id || (datos.discipulador ? datos.discipulador.id : '');
        selectD.value = idLider;
        
        // Actualizar Select2 si existe
        if (typeof $ !== 'undefined' && $(selectD).hasClass('select2-hidden-accessible')) {
            $(selectD).trigger('change');
        }
    }

    // Cambiamos el atributo 'name' para que el controlador PHP detecte 'editar_grupo'
    const btnGuardar = document.getElementById('btnGuardarAction');
    btnGuardar.name = "editar_grupo";
    btnGuardar.innerText = "Actualizar Cambios";

    modal.style.display = 'flex';
}

/**
 * Cierra el modal de grupos
 */
function cerrarModalGrupo() {
    const modal = document.getElementById('modalGrupo');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Filtro dinámico para las CARDS de grupos
 */
function filtrarGrupos() {
    const busqueda = document.getElementById('buscarGrupo').value.toLowerCase().trim();
    const nivelFiltro = document.getElementById('filtroNivel').value.toLowerCase().trim();
    const liderFiltro = document.getElementById('filtroDiscipulador').value.toLowerCase().trim();
    const estadoFiltro = document.getElementById('filtroEstado').value.toLowerCase().trim(); // <-- NUEVO
    
    const cards = document.querySelectorAll('.card-grupo');

    cards.forEach(card => {
        // Obtenemos los datos desde los atributos data-* de la card
        const nombreGrupo = card.getAttribute('data-nombre') || "";
        const nivelTexto = card.getAttribute('data-nivel').toLowerCase().trim();
        const liderTexto = card.getAttribute('data-lider') || "";
        const estadoTexto = card.getAttribute('data-estado') || ""; // <-- NUEVO

        // Evaluamos las condiciones
        const coincideNombre = nombreGrupo.includes(busqueda);
        const coincideNivel = (nivelFiltro === "") || (nivelTexto === nivelFiltro);
        const coincideLider = (liderFiltro === "") || (liderTexto.includes(liderFiltro));
        const coincideEstado = (estadoFiltro === "") || (estadoTexto === estadoFiltro); // <-- NUEVO

        // Mostramos u ocultamos la card según corresponda
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
 * Abre el modal de confirmación personalizado para eliminar un grupo
 * @param {number} id - ID del grupo a eliminar
 * @param {string} nombre - Nombre del grupo para mostrar en el mensaje
 */
function confirmarEliminarGrupo(id, nombre) {
    const modal = document.getElementById('modalConfirmarEliminar');
    const txtNombre = document.getElementById('nombreGrupoEliminar');
    const btnEliminar = document.getElementById('enlaceEliminarSeguro');

    if (modal && txtNombre && btnEliminar) {
        txtNombre.textContent = nombre;
        // Configuramos la URL exacta de redirección con el ID seleccionado
        btnEliminar.href = `?seccion=DiscipuladoGrupos&eliminar_grupo=${id}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`;
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

/**
 * UNIFICADO: Cerrar cualquier modal al hacer clic en el fondo oscuro externo
 */
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
 * Crea y muestra un mensaje emergente (Toast) que desaparece automáticamente
 * @param {string} mensaje - Texto a mostrar
 * @param {string} tipo - 'success', 'error', o 'info'
 */
function mostrarNotificacion(mensaje, tipo = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    
    // Cambiar icono según el estado
    let icono = '<i class="fas fa-check-circle" style="color: #22c55e;"></i>';
    if (tipo === 'error') icono = '<i class="fas fa-times-circle" style="color: #ef4444;"></i>';
    if (tipo === 'info') icono = '<i class="fas fa-info-circle" style="color: #3b82f6;"></i>';

    toast.innerHTML = `${icono} <span>${mensaje}</span>`;
    container.appendChild(toast);

    // Animación de entrada fluida
    setTimeout(() => toast.classList.add('show'), 50);

    // Desvanecer y remover automáticamente a los 4 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

/**
 * NUEVO: Resetea todos los valores de los selectores de filtro
 * y vuelve a mostrar la grilla completa de grupos.
 */
function limpiarFiltros() {
    document.getElementById('buscarGrupo').value = "";
    document.getElementById('filtroNivel').value = "";
    document.getElementById('filtroDiscipulador').value = "";
    document.getElementById('filtroEstado').value = "";

    // Ejecuta el filtrado con parámetros vacíos para restablecer la vista
    filtrarGrupos();
}
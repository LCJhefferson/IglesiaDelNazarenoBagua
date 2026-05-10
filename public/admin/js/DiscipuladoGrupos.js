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

    // Seleccionar al discipulador en el select
    const selectD = document.getElementById('discipulador_id');
    if (selectD) {
        selectD.value = datos.discipulador_id;
        // Actualizar Select2 si existe
        if (typeof $ !== 'undefined' && $(selectD).data('select2')) {
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
/**
 * Filtro dinámico para las CARDS de grupos
 */
function filtrarGrupos() {
    const busqueda = document.getElementById('buscarGrupo').value.toLowerCase().trim();
    const nivelFiltro = document.getElementById('filtroNivel').value.toLowerCase().trim();
    const liderFiltro = document.getElementById('filtroDiscipulador').value.toLowerCase().trim();
    
    const cards = document.querySelectorAll('.card-grupo');

    cards.forEach(card => {
        // Datos de la card
        const nombreGrupo = card.querySelector('h3').innerText.toLowerCase();
        const nivelTexto = card.querySelector('.badge-nivel').innerText.toLowerCase().replace('nivel', '').trim();
        // Buscamos el nombre del discipulador en el párrafo correspondiente
        const liderTexto = card.querySelector('.discipulador').innerText.toLowerCase().trim();

        // Validaciones
        const coincideNombre = nombreGrupo.includes(busqueda);
        const coincideNivel = (nivelFiltro === "") || (nivelTexto === nivelFiltro.toLowerCase());
        const coincideLider = (liderFiltro === "") || (liderTexto.includes(liderFiltro));

        // Aplicar filtro
        if (coincideNombre && coincideNivel && coincideLider) {
            card.style.display = "";
            card.style.opacity = "1";
        } else {
            card.style.display = "none";
            card.style.opacity = "0";
        }
    });
}

/**
 * Cerrar modal al hacer clic en el fondo oscuro
 */
window.onclick = function(event) {
    const modal = document.getElementById('modalGrupo');
    if (event.target == modal) {
        cerrarModalGrupo();
    }
}
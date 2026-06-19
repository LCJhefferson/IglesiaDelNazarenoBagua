/**
 * Lógica para la gestión de Integrantes de Discipulado
 * Filtrado instantáneo (Cliente) y gestión de Modal
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. SELECT2 (Para selección de Miembros en el Modal)
    if (typeof $ !== 'undefined' && $('.select2-buscable').length) {
        $('.select2-buscable').select2({
            placeholder: "Escriba para buscar...",
            allowClear: true,
            dropdownParent: $('#modalAsignar'),
            width: '100%',
            language: {
                noResults: function() { return "No se encontraron resultados"; }
            }
        });
    }

    // 2. BUSCADOR MANUAL DE GRUPOS (Lógica dentro del Modal)
    const inputGrupo = document.getElementById('buscarGrupoInput');
    const listaGrupos = document.getElementById('listaGruposResultados');
    const hiddenInputGrupo = document.getElementById('grupo_id_real');
    
    if (inputGrupo && listaGrupos) {
        const items = listaGrupos.querySelectorAll('.grupo-item');

        inputGrupo.addEventListener('input', function() {
            const valor = this.value.toLowerCase();
            listaGrupos.style.display = 'block';
            
            items.forEach(item => {
                const texto = item.textContent.toLowerCase();
                item.style.display = (valor === "" || texto.includes(valor)) ? 'block' : 'none';
            });
        });

        inputGrupo.addEventListener('focus', function() {
            listaGrupos.style.display = 'block';
        });

        items.forEach(item => {
            item.addEventListener('click', function() {
                inputGrupo.value = this.textContent.trim().split('(')[0].trim();
                hiddenInputGrupo.value = this.getAttribute('data-id');
                listaGrupos.style.display = 'none';
            });
        });

        document.addEventListener('click', function(e) {
            if (!inputGrupo.contains(e.target) && !listaGrupos.contains(e.target)) {
                listaGrupos.style.display = 'none';
            }
        });
    }

    // 3. CIERRE DE MODALES UNIFICADO (Teclas y Clic fuera)
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            cerrarModalAsignar();
            cerrarModalEstadoAlumno();
            cerrarModalQuitar(); // <-- CORREGIDO: Ahora cierra al presionar Escape
        }
    });

    window.addEventListener('click', function(event) {
        const modalAsignar = document.getElementById('modalAsignar');
        const modalEstado = document.getElementById('modalEstadoAlumno');
        const modalQuitar = document.getElementById('modalConfirmarQuitar'); // <-- CORREGIDO: Declarado globalmente
        
        if (event.target === modalAsignar) cerrarModalAsignar();
        if (event.target === modalEstado) cerrarModalEstadoAlumno();
        if (event.target === modalQuitar) cerrarModalQuitar(); // <-- CORREGIDO: Cierra al hacer clic en el fondo
    });
});

/**
 * ══════════════════════════════════════════
 * FILTROS DE LA TABLA (TIPO USUARIOS_ADMIN)
 * ══════════════════════════════════════════
 */

function actualizarContador() {
    const filas = document.querySelectorAll('.fila-integrante');
    let visibles = 0;
    filas.forEach(fila => {
        if (fila.style.display !== 'none') visibles++;
    });
    
    const elemContador = document.getElementById('filasMostradas');
    if (elemContador) elemContador.textContent = visibles;
}

function filtrarTablaIntegrantes() {
    const busqueda = document.getElementById('inputBusq').value.toLowerCase();
    const nivel = document.getElementById('filtroNivel').value;
    const lider = document.getElementById('filtroLider').value;
    const estado = document.getElementById('filtroEstado').value; // <-- 1. Capturar el nuevo filtro
    
    const filas = document.querySelectorAll('.fila-integrante');
    let encontrados = 0;

    filas.forEach(fila => {
        const nombreFila = fila.dataset.nombre || '';
        const nivelFila  = fila.dataset.nivel  || '';
        const liderFila  = fila.dataset.lider  || '';
        const estadoFila = fila.dataset.estado || ''; // <-- 2. Capturar el estado de la fila

        const coincideNombre = nombreFila.includes(busqueda);
        const coincideNivel  = (nivel === 'todos' || nivelFila === nivel);
        const coincideLider  = (lider === 'todos' || liderFila === lider);
        const coincideEstado = (estado === 'todos' || estadoFila === estado); // <-- 3. Evaluar coincidencia

        // 4. Añadir la condición al IF
        if (coincideNombre && coincideNivel && coincideLider && coincideEstado) {
            fila.style.display = '';
            encontrados++;
        } else {
            fila.style.display = 'none';
        }
    });

    const tbody = document.getElementById('cuerpoTablaIntegrantes');
    let noDataRow = document.getElementById('noResultsRow');

    if (encontrados === 0) {
        if (!noDataRow) {
            noDataRow = document.createElement('tr');
            noDataRow.id = 'noResultsRow';
            noDataRow.innerHTML = `<td colspan="6" style="text-align:center; padding:30px; color:#6b7a99;">No se encontraron resultados</td>`;
            tbody.appendChild(noDataRow);
        }
    } else if (noDataRow) {
        noDataRow.remove();
    }

    actualizarContador();
}

/**
 * ══════════════════════════════════════════
 * FUNCIONES GLOBALES DEL MODAL ASIGNAR
 * ══════════════════════════════════════════
 */

function abrirModalAsignar() {
    const modal = document.getElementById('modalAsignar');
    if (modal) {
        const inputGrupo = document.getElementById('buscarGrupoInput');
        const hiddenGrupo = document.getElementById('grupo_id_real');
        if(inputGrupo) inputGrupo.value = "";
        if(hiddenGrupo) hiddenGrupo.value = "";
        
        if (typeof $ !== 'undefined' && $('.select2-buscable').length) {
            $('.select2-buscable').val(null).trigger('change');
        }
        modal.style.display = 'flex';
    }
}

function cerrarModalAsignar() {
    const modal = document.getElementById('modalAsignar');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * ══════════════════════════════════════════
 * FUNCIONES DEL MODAL DE ESTADO ALUMNO
 * ══════════════════════════════════════════
 */

function abrirModalEstadoAlumno(idIntegrante, idEstadoActual, nombreAlumno) {
    const modal = document.getElementById('modalEstadoAlumno');
    if (modal) {
        document.getElementById('modal_integrante_id').value = idIntegrante;
        document.getElementById('modal_alumno_nombre').value = nombreAlumno;
        document.getElementById('modal_estado_select').value = idEstadoActual;
        modal.style.display = 'flex';
    }
}

function cerrarModalEstadoAlumno() {
    const modal = document.getElementById('modalEstadoAlumno');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * ══════════════════════════════════════════
 * CONTROL DEL MODAL CONFIRMAR QUITAR
 * ══════════════════════════════════════════
 */
function confirmarQuitarIntegrante(id, nombre) {
    const modal = document.getElementById('modalConfirmarQuitar');
    const txtNombre = document.getElementById('nombreIntegranteQuitar');
    const btnQuitar = document.getElementById('enlaceQuitarSeguro');

    if (modal && txtNombre && btnQuitar) {
        txtNombre.textContent = nombre;
        // Asignamos la dirección de acción correspondiente al controlador PHP
        btnQuitar.href = `dashboard?seccion=DiscipuladoIntegrantes&quitar_integrante=${id}`;
        modal.style.display = 'flex';
    }
}

function cerrarModalQuitar() {
    const modal = document.getElementById('modalConfirmarQuitar');
    if (modal) {
        modal.style.display = 'none';
    }
}

function limpiarFiltrosIntegrantes() {
    // 1. Restablecer los valores de los elementos en el DOM
    document.getElementById('inputBusq').value = '';
    document.getElementById('filtroNivel').value = 'todos';
    document.getElementById('filtroLider').value = 'todos';
    document.getElementById('filtroEstado').value = 'todos';

    // 2. Volver a ejecutar el filtro para actualizar la tabla con todos los registros
    filtrarTablaIntegrantes();
}
/**
 * Genera y muestra un Toast de notificación dinámico y moderno en pantalla
 */
function mostrarToastNotificacion(mensaje, tipo = 'success', icono = 'fa-check-circle') {
    const contenedor = document.getElementById('toast-container');
    if (!contenedor) return;

    // Crear el elemento del toast
    const toast = document.createElement('div');
    toast.className = `custom-toast ${tipo}`;
    
    // Configurar contenido con su icono FontAwesome correspondiente
    toast.innerHTML = `
        <i class="fas ${icono} toast-icon"></i>
        <div class="toast-message">${mensaje}</div>
        <span class="toast-close" onclick="this.parentElement.remove()">&times;</span>
    `;

    // Añadir al contenedor
    contenedor.appendChild(toast);

    // Auto-eliminar después de 4 segundos con animación de salida
    setTimeout(() => {
        toast.style.animation = 'toastFadeOut 0.3s ease-in forwards';
        toast.addEventListener('animationend', () => {
            toast.remove();
        });
    }, 4000);
}
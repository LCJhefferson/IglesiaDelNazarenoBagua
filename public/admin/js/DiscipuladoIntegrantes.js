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

    // 3. CIERRE DE MODALES (Teclas y Clic fuera)
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") cerrarModalAsignar();
    });

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('modalAsignar');
        if (event.target === modal) cerrarModalAsignar();
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
    
    const filas = document.querySelectorAll('.fila-integrante');
    let encontrados = 0;

    filas.forEach(fila => {
        // Extraemos los datos de los data-attributes definidos en el PHP
        const nombreFila = fila.dataset.nombre || '';
        const nivelFila  = fila.dataset.nivel  || '';
        const liderFila  = fila.dataset.lider  || '';

        const coincideNombre = nombreFila.includes(busqueda);
        const coincideNivel  = (nivel === 'todos' || nivelFila === nivel);
        const coincideLider  = (lider === 'todos' || liderFila === lider);

        if (coincideNombre && coincideNivel && coincideLider) {
            fila.style.display = '';
            encontrados++;
        } else {
            fila.style.display = 'none';
        }
    });

    // Manejo de mensaje "No hay resultados"
    const tbody = document.getElementById('cuerpoTablaIntegrantes');
    let noDataRow = document.getElementById('noResultsRow');

    if (encontrados === 0) {
        if (!noDataRow) {
            noDataRow = document.createElement('tr');
            noDataRow.id = 'noResultsRow';
            noDataRow.innerHTML = `<td colspan="5" style="text-align:center; padding:30px; color:#6b7a99;">No se encontraron resultados</td>`;
            tbody.appendChild(noDataRow);
        }
    } else if (noDataRow) {
        noDataRow.remove();
    }

    actualizarContador();
}

/**
 * ══════════════════════════════════════════
 * FUNCIONES GLOBALES DEL MODAL
 * ══════════════════════════════════════════
 */

function abrirModalAsignar() {
    const modal = document.getElementById('modalAsignar');
    if (modal) {
        // Limpiar inputs
        const inputGrupo = document.getElementById('buscarGrupoInput');
        const hiddenGrupo = document.getElementById('grupo_id_real');
        if(inputGrupo) inputGrupo.value = "";
        if(hiddenGrupo) hiddenGrupo.value = "";
        
        // Resetear Select2 de Miembros
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
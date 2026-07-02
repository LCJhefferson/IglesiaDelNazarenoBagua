document.addEventListener('DOMContentLoaded', function() {
    
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

    const inputGrupo = document.getElementById('buscarGrupoInput');
    const listaGrupos = document.getElementById('listaGruposResultados');
    const hiddenInputGrupo = document.getElementById('grupo_id_real');
    
    if (inputGrupo && listaGrupos) {
        const items = listaGrupos.querySelectorAll('.grupo-item');

        inputGrupo.addEventListener('input', function() {
            const valor = this.value.toLowerCase();
            listaGrupos.style.display = 'block';
            
            items.forEach(item => {
                // ✅ antes: item.innerText.toLowerCase()
                const texto = item.textContent.toLowerCase(); 
                item.style.display = (valor === "" || texto.includes(valor)) ? 'block' : 'none';
            });
        });

        inputGrupo.addEventListener('focus', function() {
            listaGrupos.style.display = 'block';
        });

        items.forEach(item => {
            item.addEventListener('click', function() {
                // ✅ antes: this.innerText
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

    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            cerrarModalAsignar();
            cerrarModalEstadoAlumno();
            cerrarModalQuitar(); 
        }
    });

    window.addEventListener('click', function(event) {
        const modalAsignar = document.getElementById('modalAsignar');
        const modalEstado = document.getElementById('modalEstadoAlumno');
        const modalQuitar = document.getElementById('modalConfirmarQuitar'); 
        
        if (event.target === modalAsignar) cerrarModalAsignar();
        if (event.target === modalEstado) cerrarModalEstadoAlumno();
        if (event.target === modalQuitar) cerrarModalQuitar(); 
    });
});

function actualizarContador() {
    const filas = document.querySelectorAll('.fila-integrante');
    let visibles = 0;
    filas.forEach(fila => {
        if (fila.style.display !== 'none') visibles++;
    });
    
    const elemContador = document.getElementById('filasMostradas');
    if (elemContador) {
        // ✅ antes: elemContador.innerText = visibles;
        elemContador.textContent = visibles;
    }
}

function filtrarTablaIntegrantes() {
    const busqueda = document.getElementById('inputBusq').value.toLowerCase();
    const nivel = document.getElementById('filtroNivel').value;
    const lider = document.getElementById('filtroLider').value;
    const estado = document.getElementById('filtroEstado').value; 
    
    const filas = document.querySelectorAll('.fila-integrante');
    let encontrados = 0;

    filas.forEach(fila => {
        const nombreFila = fila.dataset.nombre || '';
        const nivelFila  = fila.dataset.nivel  || '';
        const liderFila  = fila.dataset.lider  || '';
        const estadoFila = fila.dataset.estado || ''; 

        const coincideNombre = nombreFila.includes(busqueda);
        const coincideNivel  = (nivel === 'todos' || nivelFila === nivel);
        const coincideLider  = (lider === 'todos' || liderFila === lider);
        const coincideEstado = (estado === 'todos' || estadoFila === estado); 

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

            // ✅ antes: noDataRow.innerHTML = `<td colspan="6" ...>No se encontraron resultados</td>`;
            const td = document.createElement('td');
            td.colSpan = 6;
            td.style.textAlign = "center";
            td.style.padding = "30px";
            td.style.color = "#6b7a99";
            td.textContent = "No se encontraron resultados";
            noDataRow.appendChild(td);

            tbody.appendChild(noDataRow);
        }
    } else if (noDataRow) {
        noDataRow.remove();
    }

    actualizarContador();
}

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

function confirmarQuitarIntegrante(id, nombre) {
    const modal = document.getElementById('modalConfirmarQuitar');
    const txtNombre = document.getElementById('nombreIntegranteQuitar');
    const btnQuitar = document.getElementById('enlaceQuitarSeguro');

    if (modal && txtNombre && btnQuitar) {
        // ✅ ya estaba correcto con textContent
        txtNombre.textContent = nombre;
        btnQuitar.href = `dashboard?seccion=DiscipuladoIntegrantes&quitar_integrante=${id}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`;
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
    document.getElementById('inputBusq').value = '';
    document.getElementById('filtroNivel').value = 'todos';
    document.getElementById('filtroLider').value = 'todos';
    document.getElementById('filtroEstado').value = 'todos';

    filtrarTablaIntegrantes();
}

function mostrarToastNotificacion(mensaje, tipo = 'success', icono = 'fa-check-circle') {
    const contenedor = document.getElementById('toast-container');
    if (!contenedor) return;

    const toast = document.createElement('div');
    toast.className = `custom-toast ${tipo}`;
    
    //antes: toast.innerHTML = `<i ...>${mensaje}</div> ...`
    const iconElem = document.createElement("i");
    iconElem.className = `fas ${icono} toast-icon`;

    const msgElem = document.createElement("div");
    msgElem.className = "toast-message";
    msgElem.textContent = mensaje;

    const closeElem = document.createElement("span");
    closeElem.className = "toast-close";
    closeElem.textContent = "×";
    closeElem.onclick = function() { this.parentElement.remove(); };

    toast.appendChild(iconElem);
    toast.appendChild(msgElem);
    toast.appendChild(closeElem);

    contenedor.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'toastFadeOut 0.3s ease-in forwards';
        toast.addEventListener('animationend', () => {
            toast.remove();
        });
    }, 4000);
}

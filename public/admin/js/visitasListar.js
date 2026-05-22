
document.addEventListener("DOMContentLoaded", function() {
    if (localStorage.getItem('v_filtroNombre') !== null) {
        document.getElementById('filtroNombre').value = localStorage.getItem('v_filtroNombre');
    }
    if (localStorage.getItem('v_filtroMotivo') !== null) {
        document.getElementById('filtroMotivo').value = localStorage.getItem('v_filtroMotivo');
    }
    if (localStorage.getItem('v_filtroEstado') !== null) {
        document.getElementById('filtroEstado').value = localStorage.getItem('v_filtroEstado');
    }
    if (localStorage.getItem('v_filtroModo') !== null) {
        document.getElementById('filtroModo').value = localStorage.getItem('v_filtroModo');
    }

    filtrarVisitas();
});

// 1. MÓDULO MODAL: REGISTRAR VISITA PASTORAL
function abrirModalVisita(id, nombre) {
    document.getElementById('modalMiembroId').value = id;
    document.getElementById('modalNombreMiembro').innerText = nombre;

    const inputFecha = document.getElementById('txtFechaVisita');
    const hoy = new Date();
    
    const anio = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia = String(hoy.getDate()).padStart(2, '0');
    const fechaFormateada = `${anio}-${mes}-${dia}`;
    
    inputFecha.value = fechaFormateada; 
    inputFecha.max = fechaFormateada; 

    document.getElementById('modalVisita').style.display = 'flex';
}

function cerrarModalVisita() {
    document.getElementById('modalVisita').style.display = 'none';
    document.getElementById('selectMotivo').value = 'Visita Regular';
    document.getElementById('contenedorOtros').style.display = 'none';
    document.getElementById('txtMotivoLibre').value = '';
    document.getElementById('txtMotivoLibre').required = false;
}

function evaluarSeleccionMotivo(selector) {
    const contenedorOtros = document.getElementById('contenedorOtros');
    const txtMotivoLibre = document.getElementById('txtMotivoLibre');
    
    if (selector.value === 'Otros') {
        contenedorOtros.style.display = 'block';
        txtMotivoLibre.required = true;
        txtMotivoLibre.focus();
    } else {
        contenedorOtros.style.display = 'none';
        txtMotivoLibre.required = false;
    }
}

function procesarGuardarVisita(event) {
    event.preventDefault(); 
    const form = document.getElementById('formRegistrarVisita');
    const urlDestino = form.getAttribute('action'); 
    const formData = new FormData(form);

    fetch(urlDestino, {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.ok) {
            window.location.reload(); // Al recargar, el bloque 0 restaurará tus filtros
        } else {
            alert("Error al guardar el registro de visita.");
        }
    })
    .catch(err => {
        console.error("Error Fetch:", err);
        alert("Error de conexión con el servidor.");
    });
}

function abrirModalAjustes() {
    document.getElementById('modalAjustes').style.display = 'flex';
}

function cerrarModalAjustes() {
    document.getElementById('modalAjustes').style.display = 'none';
}

function procesarGuardarAjustes(event) {
    event.preventDefault();
    const form = document.getElementById('formAjustesVisita');
    const urlDestino = form.getAttribute('action');
    const formData = new FormData(form);

    fetch(urlDestino, {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.ok) {
            window.location.reload(); // Al recargar, el bloque 0 restaurará tus filtros
        } else {
            alert("Error al actualizar los ajustes.");
        }
    })
    .catch(err => {
        console.error("Error Fetch:", err);
        alert("Error de conexión con el servidor.");
    });
}


function abrirModalEliminar(visitaId, nombreMiembro) {
    document.getElementById('modalEliminarVisitaId').value = visitaId;
    document.getElementById('eliminarNombreMiembro').textContent = nombreMiembro;
    document.getElementById('modalEliminarVisita').style.display = 'flex';
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminarVisita').style.display = 'none';
}

function procesarEliminacionLogica() {
    const form = document.getElementById('formEliminarVisita');
    const urlDestino = form.getAttribute('action');
    const formData = new FormData(form);

    fetch(urlDestino, {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.ok) {
            window.location.reload(); 
        } else {
            alert("Error al suprimir la visita.");
        }
    })
    .catch(err => {
        console.error("Error Fetch:", err);
        alert("Error de conexión con el servidor.");
    });
}

function filtrarVisitas() {
    const nombre = document.getElementById('filtroNombre').value;
    const motivo = document.getElementById('filtroMotivo').value;
    const estado = document.getElementById('filtroEstado').value;
    const modo = document.getElementById('filtroModo').value;

    localStorage.setItem('v_filtroNombre', nombre);
    localStorage.setItem('v_filtroMotivo', motivo);
    localStorage.setItem('v_filtroEstado', estado);
    localStorage.setItem('v_filtroModo', modo);

    document.getElementById('th-fecha').textContent = (modo === 'todos') ? 'Fecha de Visita' : 'Última Visita';
    document.getElementById('th-motivo').textContent = (modo === 'todos') ? 'Motivo de Visita' : 'Motivo Último';

    const url = `index.php?vista=admin/contenidos/visitasListar&ajax=1&nombre=${encodeURIComponent(nombre)}&motivo=${encodeURIComponent(motivo)}&estado=${encodeURIComponent(estado)}&modo=${encodeURIComponent(modo)}`;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const nuevoCuerpo = doc.getElementById('ajax-tabla-bridge');
            if(nuevoCuerpo) {
                document.getElementById('tabla-visitas-cuerpo').innerHTML = nuevoCuerpo.innerHTML;
            }

            const nuevasStats = doc.getElementById('ajax-stats-bridge');
            if(nuevasStats) {
                document.getElementById('contenedor-stats').innerHTML = nuevasStats.innerHTML;
            }
        })
        .catch(error => console.error('Error al filtrar:', error));
}

function limpiarFiltros() {
    document.getElementById('filtroNombre').value = '';
    document.getElementById('filtroMotivo').value = '';
    document.getElementById('filtroEstado').value = '';
    document.getElementById('filtroModo').value = 'ultimo';

    // LIMPIAR MEMORIA: Aquí sí eliminamos los registros de la memoria del navegador
    localStorage.removeItem('v_filtroNombre');
    localStorage.removeItem('v_filtroMotivo');
    localStorage.removeItem('v_filtroEstado');
    localStorage.removeItem('v_filtroModo');

    filtrarVisitas();
}

window.onclick = function(event) {
    const modalVisita = document.getElementById('modalVisita');
    const modalAjustes = document.getElementById('modalAjustes');
    const modalEliminar = document.getElementById('modalEliminarVisita');
    
    if (event.target === modalVisita) cerrarModalVisita();
    if (event.target === modalAjustes) cerrarModalAjustes();
    if (event.target === modalEliminar) cerrarModalEliminar();
}
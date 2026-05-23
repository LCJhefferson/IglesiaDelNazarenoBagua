/**
 * SISTEMA DE GESTIÓN DE VISITAS - JAVASCRIPT PRINCIPAL (CORREGIDO)
 */

document.addEventListener("DOMContentLoaded", function() {
    // 1. Restaurar filtros desde LocalStorage
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

    // 2. Ejecutar el filtrado inicial
    filtrarVisitas();
});

// ==========================================
// MÓDULO MODAL: REGISTRAR / EDITAR VISITA
// ==========================================

function abrirModalVisita(id, nombre) {
    document.getElementById('modalHeaderTitulo').innerText = 'Registrar Visita';
    document.getElementById('btnTextVisita').innerText = 'Guardar Registro';
    
    document.getElementById('modalVisitaId').value = ''; 
    document.getElementById('modalMiembroId').value = id;
    document.getElementById('modalNombreMiembro').innerText = nombre;

    const inputFecha = document.getElementById('txtFechaVisita');
    const hoy = new Date().toISOString().split('T')[0];
    inputFecha.value = hoy;
    inputFecha.max = hoy; 

    document.getElementById('selectMotivo').value = 'Visita Regular';
    document.getElementById('contenedorOtros').style.display = 'none';
    document.getElementById('txtMotivoLibre').value = '';
    document.getElementById('txtMotivoLibre').required = false;

    document.getElementById('modalVisita').style.display = 'flex';
}

/**
 * Función corregida para cargar la fecha correctamente
 */
/**
 * Función corregida para cargar la fecha correctamente y bloquear fechas futuras
 */
function abrirModalEditar(visitaId, miembroId, nombre, fecha, motivo) {
    document.getElementById('modalHeaderTitulo').innerText = 'Modificar Visita';
    document.getElementById('btnTextVisita').innerText = 'Actualizar Cambios';
    
    document.getElementById('modalVisitaId').value = visitaId;
    document.getElementById('modalMiembroId').value = miembroId;
    document.getElementById('modalNombreMiembro').innerText = nombre;

    // --- CORRECCIÓN DE FECHA AQUÍ ---
    const inputFecha = document.getElementById('txtFechaVisita');
    const hoy = new Date().toISOString().split('T')[0]; // Obtiene hoy en formato YYYY-MM-DD
    
    inputFecha.max = hoy;     // 1. Bloquea que se puedan elegir días futuros
    inputFecha.value = fecha; // 2. Inserta la fecha que viene de la Base de Datos
    // --------------------------------

    const select = document.getElementById('selectMotivo');
    const contenedorOtros = document.getElementById('contenedorOtros');
    const txtMotivoLibre = document.getElementById('txtMotivoLibre');
    
    // Lista de motivos estándar coincidente con el PHP
    const motivosPredefinidos = ['Visita Regular', 'Por Enfermedad', 'Evangelística'];

    if (motivosPredefinidos.includes(motivo)) {
        select.value = motivo;
        contenedorOtros.style.display = 'none';
        txtMotivoLibre.value = '';
        txtMotivoLibre.required = false;
    } else {
        select.value = 'Otros';
        contenedorOtros.style.display = 'block';
        txtMotivoLibre.value = motivo;
        txtMotivoLibre.required = true;
    }

    document.getElementById('modalVisita').style.display = 'flex';
}

function cerrarModalVisita() {
    document.getElementById('modalVisita').style.display = 'none';
    document.getElementById('formRegistrarVisita').reset();
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
        txtMotivoLibre.value = '';
    }
}

function procesarGuardarVisita(event) {
    event.preventDefault(); 
    const form = document.getElementById('formRegistrarVisita');
    const urlDestino = form.getAttribute('action'); 
    const formData = new FormData(form);

    const btnSubmit = document.getElementById('btnSubmitVisita');
    const btnText = document.getElementById('btnTextVisita');
    const textoOriginal = btnText ? btnText.innerText : 'Guardar Registro';

    if (btnSubmit) btnSubmit.disabled = true;
    if (btnText) btnText.innerText = 'Guardando...';

    fetch(urlDestino, {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.ok) {
            cerrarModalVisita();
            // Esto actualiza la tabla automáticamente sin recargar la página completa
            filtrarVisitas(); 
        } else {
            alert(data.error || "Error al procesar el registro.");
        }
    })
    .catch(err => {
        console.error("Error Fetch:", err);
        alert("Error de conexión con el servidor.");
    })
    .finally(() => {
        if (btnSubmit) btnSubmit.disabled = false;
        if (btnText) btnText.innerText = textoOriginal;
    });
}








// ==========================================
// MÓDULO: FILTRADO DINÁMICO (AJAX)
// ==========================================
function filtrarVisitas() {
    const nombre = document.getElementById('filtroNombre').value;
    const motivo = document.getElementById('filtroMotivo').value;
    const estado = document.getElementById('filtroEstado').value;
    const modo   = document.getElementById('filtroModo').value;

    localStorage.setItem('v_filtroNombre', nombre);
    localStorage.setItem('v_filtroMotivo', motivo);
    localStorage.setItem('v_filtroEstado', estado);
    localStorage.setItem('v_filtroModo', modo);

    // URL corregida según tu estructura de rutas
    const url = `index.php?vista=dashboard&seccion=visitasListar&ajax=1&nombre=${encodeURIComponent(nombre)}&motivo=${encodeURIComponent(motivo)}&estado=${encodeURIComponent(estado)}&modo=${encodeURIComponent(modo)}`;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const nuevoCuerpo = doc.getElementById('ajax-tbody-bridge');
            const tablaDestino = document.getElementById('tabla-visitas-cuerpo');
            
            // Actualizar Cuerpo de Tabla
            if (nuevoCuerpo && tablaDestino) {
                tablaDestino.innerHTML = nuevoCuerpo.innerHTML;
            } else {
                console.error("Error: Respuesta AJAX incompleta (posibles errores PHP).");
            }

            // Actualizar Estadísticas
            const nuevasStats = doc.getElementById('ajax-stats-bridge');
            const contenedorStats = document.getElementById('contenedor-stats');
            if (nuevasStats && contenedorStats) {
                contenedorStats.innerHTML = nuevasStats.innerHTML;
            }

            // Actualizar Cabecera (por si cambia el texto de "Última Visita")
            const nuevaCab = doc.getElementById('ajax-thead-bridge');
            const tablaHead = document.getElementById('tabla-visitas-head');
            if (nuevaCab && tablaHead) {
                tablaHead.innerHTML = nuevaCab.innerHTML;
            }
        })
        .catch(error => console.error('Error en el filtrado:', error));
}

function limpiarFiltros() {
    document.getElementById('filtroNombre').value = '';
    document.getElementById('filtroMotivo').value = '';
    document.getElementById('filtroEstado').value = '';
    document.getElementById('filtroModo').value = 'ultimo';

    localStorage.removeItem('v_filtroNombre');
    localStorage.removeItem('v_filtroMotivo');
    localStorage.removeItem('v_filtroEstado');
    localStorage.removeItem('v_filtroModo');

    filtrarVisitas();
}

// ==========================================
// MÓDULO: ELIMINACIÓN Y AJUSTES
// ==========================================

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
    const formData = new FormData(form);

    fetch('index.php?vista=admin/eliminarVisita', {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.ok) {
            cerrarModalEliminar();
            // Actualización automática tras eliminar
            filtrarVisitas(); 
        } else {
            alert(data.error || "Error al suprimir la visita.");
        }
    })
    .catch(err => alert("Error de red al intentar eliminar."));
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
    const formData = new FormData(form);

    fetch(form.getAttribute('action'), {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.ok) {
            // Recargamos la página completa solo en ajustes para re-procesar todo el PHP
            window.location.reload(); 
        } else {
            alert("Error al actualizar los ajustes.");
        }
    })
    .catch(err => console.error("Error Fetch Ajustes:", err));
}

// --- Cerrar modales al hacer clic fuera ---
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
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

// =========================================================================
    // NUEVO: Escuchar los cambios en los inputs para filtrar sin recargar
    // =========================================================================
    
    // // Escucha cuando el usuario escribe en el buscador de nombres
    // document.getElementById('filtroNombre').addEventListener('input', filtrarVisitas);
    
    // // Escuha cuando el usuario cambia una opción en los selectores desplegables
    // document.getElementById('filtroMotivo').addEventListener('change', filtrarVisitas);
    // document.getElementById('filtroEstado').addEventListener('change', filtrarVisitas);
    // document.getElementById('filtroModo').addEventListener('change', filtrarVisitas);
    

    // 2. Ejecutar el filtrado inicial
    filtrarVisitas();
});

// ==========================================
// MÓDULO MODAL: REGISTRAR / EDITAR VISITA
// ==========================================

function abrirModalVisita(id, nombre) {

    document.getElementById('modalHeaderTitulo').textContent = 'Registrar Visita';
    document.getElementById('btnTextVisita').textContent = 'Guardar Registro';
    
   
    document.getElementById('modalVisitaId').value = ''; 
    document.getElementById('modalMiembroId').value = id;
    
    document.getElementById('modalNombreMiembro').textContent = nombre;

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
    // ✅ CORRECCIÓN XSS: antes innerText → ahora textContent
    document.getElementById('modalHeaderTitulo').textContent = 'Modificar Visita';
    document.getElementById('btnTextVisita').textContent = 'Actualizar Cambios';
    
    // ✅ Asignación segura de valores a inputs
    document.getElementById('modalVisitaId').value = visitaId;
    document.getElementById('modalMiembroId').value = miembroId;
    // ✅ Mostrar nombre como texto plano
    document.getElementById('modalNombreMiembro').textContent = nombre;

    // --- CORRECCIÓN DE FECHA AQUÍ ---
    const inputFecha = document.getElementById('txtFechaVisita');
    const hoy = new Date().toISOString().split('T')[0]; // Obtiene hoy en formato YYYY-MM-DD
    
    inputFecha.max = hoy;     // ✅ Bloquea que se puedan elegir días futuros
    inputFecha.value = fecha; // ✅ Inserta la fecha que viene de la Base de Datos
    // --------------------------------

    const select = document.getElementById('selectMotivo');
    const contenedorOtros = document.getElementById('contenedorOtros');
    const txtMotivoLibre = document.getElementById('txtMotivoLibre');
    
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
    // ✅ CORRECCIÓN XSS: usar textContent en lugar de innerText
    const textoOriginal = btnText ? btnText.textContent : 'Guardar Registro';

    if (btnSubmit) btnSubmit.disabled = true;
    if (btnText) btnText.textContent = 'Guardando...'; // seguro

    fetch(urlDestino, {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.ok) {
            cerrarModalVisita();
            filtrarVisitas(); // ✅ actualiza tabla sin recargar
        } else {
            // ✅ CORRECCIÓN XSS: mostrar error como texto plano
            const mensajeError = typeof data.error === "string" ? data.error : "Error al procesar el registro.";
            alert(mensajeError);
        }
    })
    .catch(err => {
        console.error("Error Fetch:", err);
        alert("Error de conexión con el servidor."); // seguro
    })
    .finally(() => {
        if (btnSubmit) btnSubmit.disabled = false;
        if (btnText) btnText.textContent = textoOriginal; // seguro
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

    // ✅ URL segura con encodeURIComponent
    const url = `index.php?vista=dashboard&seccion=visitasListar&ajax=1&nombre=${encodeURIComponent(nombre)}&motivo=${encodeURIComponent(motivo)}&estado=${encodeURIComponent(estado)}&modo=${encodeURIComponent(modo)}`;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const nuevoCuerpo = doc.getElementById('ajax-tbody-bridge');
            const tablaDestino = document.getElementById('tabla-visitas-cuerpo');
            
            // ✅ Actualizar cuerpo de tabla de forma segura
            if (nuevoCuerpo && tablaDestino) {
                tablaDestino.textContent = ""; // limpiar seguro
                tablaDestino.append(...nuevoCuerpo.children); // copiar nodos en lugar de innerHTML
            } else {
                console.error("Error: Respuesta AJAX incompleta (posibles errores PHP).");
            }

            // ✅ Actualizar estadísticas
            const nuevasStats = doc.getElementById('ajax-stats-bridge');
            const contenedorStats = document.getElementById('contenedor-stats');
            if (nuevasStats && contenedorStats) {
                contenedorStats.textContent = "";
                contenedorStats.append(...nuevasStats.children);
            }

            // ✅ Actualizar cabecera
            const nuevaCab = doc.getElementById('ajax-thead-bridge');
            const tablaHead = document.getElementById('tabla-visitas-head');
            if (nuevaCab && tablaHead) {
                tablaHead.textContent = "";
                tablaHead.append(...nuevaCab.children);
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
    // ✅ Asignación segura de valores
    document.getElementById('modalEliminarVisitaId').value = visitaId;
    // ✅ CORRECCIÓN XSS: mostrar nombre como texto plano
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
            filtrarVisitas(); // ✅ Actualización automática tras eliminar
        } else {
            // ✅ CORRECCIÓN XSS: mostrar error como texto plano
            const mensajeError = typeof data.error === "string" ? data.error : "Error al suprimir la visita.";
            alert(mensajeError);
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
            // ✅ Recargamos la página completa solo en ajustes
            window.location.reload(); 
        } else {
            // ✅ Mensaje seguro contra XSS
            alert("Error al actualizar los ajustes.");
        }
    })
    .catch(err => console.error("Error Fetch Ajustes:", err));
}

// --- Cerrar modales al hacer clic fuera ---
window.onclick = function(event) {
    // ✅ Verificación segura: solo cierra si el target es un modal
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

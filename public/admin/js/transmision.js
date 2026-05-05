document.addEventListener('DOMContentLoaded', () => {
    const buscar = document.getElementById('buscarTransmision');
    const estado = document.getElementById('filtroEstado');
    const fecha = document.getElementById('filtroFecha');

    // Escuchar cambios en los inputs
    [buscar, estado, fecha].forEach(el => {
        el.addEventListener('input', filtrarTabla);
    });
});

function filtrarTabla() {
    const texto = document.getElementById('buscarTransmision').value.toLowerCase();
    const estVal = document.getElementById('filtroEstado').value;
    const fecVal = document.getElementById('filtroFecha').value;
    const filas = document.querySelectorAll('#tablaTransmisiones tr');

    filas.forEach(fila => {
        const titulo = fila.cells[0].innerText.toLowerCase();
        const estado = fila.cells[2].innerText; // Ajustar según el texto del badge
        const fecha = fila.cells[3].innerText;

        const coincideTexto = titulo.includes(texto);
        const coincideEstado = estVal === "" || estado.includes(estVal);
        const coincideFecha = fecVal === "" || fecha === fecVal;

        fila.style.display = (coincideTexto && coincideEstado && coincideFecha) ? "" : "none";
    });
}

function limpiarFiltros() {
    document.getElementById('buscarTransmision').value = "";
    document.getElementById('filtroEstado').value = "";
    document.getElementById('filtroFecha').value = "";
    filtrarTabla();
}

    // Función para subir los datos de la tabla al formulario para editar
    function cargarParaEditar(data) {
        document.getElementById('formActionTitle').innerText = "Editar Transmisión";
        document.getElementById('btnText').innerText = "Actualizar Datos";
        document.getElementById('btnCancelar').style.display = "inline-block";
        
        // Llenar campos
        document.getElementById('formId').value = data.id;
        document.getElementById('formTitulo').value = data.titulo;
        document.getElementById('formDesc').value = data.descripcion;
        document.getElementById('formLink').value = data.link_video;
        
        // Ocultar selector de estado al editar para evitar conflictos (según tu requerimiento)
        document.getElementById('containerEstado').style.display = "none";
        
        // Hacer scroll suave hacia el formulario
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formTransmision').reset();
        document.getElementById('formActionTitle').innerText = "Nueva Transmisión";
        document.getElementById('btnText').innerText = "Iniciar y Guardar";
        document.getElementById('btnCancelar').style.display = "none";
        document.getElementById('containerEstado').style.display = "block";
        document.getElementById('formId').value = "";
    }

    function finalizarTransmision(id) {
        if(confirm('¿Deseas finalizar esta transmisión en vivo?')) {
            window.location.href = 'contenidos/procesar_estado.php?id=' + id + '&nuevo_estado=3';
        }
    }

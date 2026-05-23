/**
 * ARCHIVO: membresia.js
 * Descripción: Gestión de modal, filtros de tabla e inicialización de Select2
 */

// 1. INICIALIZACIÓN
document.addEventListener("DOMContentLoaded", function() {
    inicializarSelect2();
});

function inicializarSelect2() {
    $('#cargos_select').select2({
        placeholder: "Buscar o seleccionar funciones...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modal'),
        language: {
            noResults: function() { return "No se encontraron cargos"; }
        }
    });
}

// 2. GESTIÓN DEL MODAL
function abrirModal() {
    const modal = document.getElementById("modal");
    const form = document.getElementById("formMiembro");

    if (modal) {
        modal.classList.add("active");
        modal.style.display = "flex";

        // Resetear formulario y limpiar Select2
        if (form) form.reset();
        $('#cargos_select').val(null).trigger('change');

        // Configuración para modo "Nuevo"
        document.getElementById("btnAgregar").style.display = "inline-block";
        document.getElementById("btnActualizar").style.display = "none";
        document.getElementById("tituloModal").innerHTML = '<i class="fa-solid fa-user-plus"></i> Nuevo Miembro';

        // Limpiar campos específicos/ocultos
        document.getElementsByName("id")[0].value = "";
        document.getElementById("latitud").value = "";
        document.getElementById("longitud").value = "";
        
        // Ejecutar lógica extra de tipo si existe
        if (typeof checkTipo === 'function') checkTipo();
    }
}

function cerrarModal() {
    const modal = document.getElementById("modal");
    if (modal) {
        modal.classList.remove("active");
        modal.style.display = "none";
    }
}

// Cerrar al hacer clic fuera del contenido del modal
window.onclick = function(event) {
    const modal = document.getElementById("modal");
    if (event.target === modal) {
        cerrarModal();
    }
};

// 3. EDICIÓN DE REGISTROS
function editar(m) {
    // Reutilizamos la apertura y limpieza base
    abrirModal();

    // Ajustar UI para modo "Edición"
    document.getElementById("tituloModal").innerHTML = '<i class="fa-solid fa-pen"></i> Editar Miembro';
    document.getElementById("btnAgregar").style.display = "none";
    document.getElementById("btnActualizar").style.display = "inline-block";

    // Llenado de campos básicos
    document.getElementsByName("id")[0].value = m.id;
    document.getElementsByName("nombres")[0].value = m.nombres;
    document.getElementsByName("apellidos")[0].value = m.apellidos;
    document.getElementsByName("telefono")[0].value = m.telefono;
    document.getElementsByName("direccion")[0].value = m.direccion;
    document.getElementsByName("fecha_nacimiento")[0].value = m.fecha_nacimiento;
    document.getElementsByName("condicion_id")[0].value = m.condicion_id;
    document.getElementById("tipo_miembro_id").value = m.tipo_miembro_id;
    document.getElementById("latitud").value = m.latitud || "";
    document.getElementById("longitud").value = m.longitud || "";
    
    const inputEstado = document.getElementById("inputEstado");
    if (inputEstado) inputEstado.value = m.estado;

    // Llenado de Select2 (Cargos Múltiples)
    if (m.cargos_ids) {
        // Si cargos_ids ya es un array (ej: [1, 2]) o string separado por comas
        let ids = Array.isArray(m.cargos_ids) ? m.cargos_ids : m.cargos_ids.split(',');
        $('#cargos_select').val(ids).trigger('change');
    } else if (m.cargo_id) {
        // Fallback para cuando solo viene un ID
        $('#cargos_select').val([m.cargo_id]).trigger('change');
    }
}

// 4. FILTROS Y BÚSQUEDA
function filtrarTabla() {
    const busqueda = document.getElementById("buscar").value.toLowerCase();
    const filtroTipo = document.getElementById("filtroTipo").value.toLowerCase();
    const filtroRol = document.getElementById("filtroRol").value.toLowerCase();
    const filtroEstado = document.getElementById("filtroEstado").value;
    
    const filas = document.querySelectorAll("#tablaCuerpo tr");

    filas.forEach(fila => {
        const nombre = fila.querySelector("td:nth-child(1)").innerText.toLowerCase();
        const tipo = fila.querySelector(".col-tipo").innerText.toLowerCase();
        const rol = fila.querySelector(".col-rol").innerText.toLowerCase();
        const estado = fila.getAttribute("data-estado");

        const coincideNombre = nombre.includes(busqueda);
        const coincideTipo = filtroTipo === "" || tipo.includes(filtroTipo);
        const coincideRol = filtroRol === "" || rol.includes(filtroRol);
        const coincideEstado = filtroEstado === "" || estado === filtroEstado;

        // Mostrar solo si cumple todos los filtros
        fila.style.display = (coincideNombre && coincideTipo && coincideRol && coincideEstado) ? "" : "none";
    });
}

// 5. MAPA 
let mapaSeleccion;
let marcador;
let latTemporal = null;
let lngTemporal = null;

function abrirMapa() {
    document.getElementById('modalMapa').style.display = 'block';

    if (!mapaSeleccion) {
        // Coordenadas exactas aproximadas para Jr. Cajamarca, Bagua
        const defaultLat = -5.640882315845701; 
        const defaultLng = -78.52988421066584;

        // Inicializamos con un zoom de 18 para que se vea la calle claramente
        mapaSeleccion = L.map('mapa-seleccionar').setView([defaultLat, defaultLng], 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapaSeleccion);

        // Agregamos el marcador inicial en la iglesia
        actualizarMarcador(defaultLat, defaultLng);

        // Configuración del buscador
        const geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
            placeholder: "Buscar calle o número...",
            errorMessage: "No encontrado"
        })
        .on('markgeocode', function(e) {
            const center = e.geocode.center;
            actualizarMarcador(center.lat, center.lng);
            mapaSeleccion.setView(center, 18);
        })
        .addTo(mapaSeleccion);

        mapaSeleccion.on('click', function(e) {
            actualizarMarcador(e.latlng.lat, e.latlng.lng);
        });
    }

    setTimeout(() => {
        mapaSeleccion.invalidateSize(true);
    }, 300);
}

function actualizarMarcador(lat, lng) {
    latTemporal = lat;
    lngTemporal = lng;

    if (marcador) {
        marcador.setLatLng([lat, lng]);
    } else {
        marcador = L.marker([lat, lng], { draggable: true }).addTo(mapaSeleccion);
        marcador.on('dragend', function(event) {
            const position = marcador.getLatLng();
            latTemporal = position.lat;
            lngTemporal = position.lng;
        });
    }
}

function confirmarUbicacion() {
    if (latTemporal && lngTemporal) {
        // Asignar valores a los campos del modal principal
        document.getElementById('latitud').value = latTemporal.toFixed(6);
        document.getElementById('longitud').value = lngTemporal.toFixed(6);
        cerrarModalMapa();
    } else {
        alert("Por favor, selecciona un punto en el mapa.");
    }
}

function cerrarModalMapa() {
    document.getElementById('modalMapa').style.display = 'none';
}

let urlConfirmacion = "";

function showConfirm(url) {
    urlConfirmacion = url;
    const modal = document.getElementById("customConfirm");
    modal.style.display = "flex"; // Cambiado de 'block' a 'flex' para el centrado
    
    document.getElementById("btnConfirmAction").onclick = function() {
        window.location.href = urlConfirmacion;
    };
}

function closeConfirm() {
    const modal = document.getElementById("customConfirm");
    modal.style.display = "none";
    modal.classList.remove("active");
}
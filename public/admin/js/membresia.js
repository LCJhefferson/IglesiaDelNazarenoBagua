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

    // Revisar si ya hay coordenadas en los inputs (por ejemplo al editar)
    const latGuardada = document.getElementById('latitud').value;
    const lngGuardada = document.getElementById('longitud').value;

    if (!mapaSeleccion) {
        // Si hay coordenadas guardadas, usamos esas, si no, Bagua
        const initialLat = latGuardada ? parseFloat(latGuardada) : -5.640882;
        const initialLng = lngGuardada ? parseFloat(lngGuardada) : -78.529884;

        mapaSeleccion = L.map('mapa-seleccionar').setView([initialLat, initialLng], 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapaSeleccion);

        actualizarMarcador(initialLat, initialLng, false);

        // ... resto de tu configuración del geocoder y click ...
        mapaSeleccion.on('click', function(e) {
            actualizarMarcador(e.latlng.lat, e.latlng.lng, true);
        });
    } else {
        // Si el mapa ya existía, pero lo volvemos a abrir
        if (latGuardada && lngGuardada) {
            const lat = parseFloat(latGuardada);
            const lng = parseFloat(lngGuardada);
            mapaSeleccion.setView([lat, lng], 18);
            actualizarMarcador(lat, lng, false);
        }
    }

    setTimeout(() => {
        mapaSeleccion.invalidateSize(true);
    }, 300);
}

// Variable global para guardar la dirección que encuentra el mapa
let direccionEncontradaMapa = ""; 

function actualizarMarcador(lat, lng, buscarDireccion = true) {
    latTemporal = lat;
    lngTemporal = lng;

    if (marcador) {
        marcador.setLatLng([lat, lng]);
    } else {
        marcador = L.marker([lat, lng], { draggable: true }).addTo(mapaSeleccion);
        marcador.on('dragend', function(event) {
            const position = marcador.getLatLng();
            actualizarMarcador(position.lat, position.lng, true);
        });
    }

    if (buscarDireccion) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data.display_name) {
                    // RESCATAR EL NÚMERO DEL INPUT ACTUAL
                    const actualValue = document.getElementById('direccion').value;
                    const matches = actualValue.match(/\b\d+\b/g);
                    const numeroPrevio = matches ? matches[matches.length - 1] : "";

                    let calleNueva = data.address.road || data.address.pedestrian || data.display_name.split(',')[0];
                    
                    // Si ya teníamos un número, se lo pegamos a la nueva calle
                    direccionEncontradaMapa = numeroPrevio ? `${calleNueva} ${numeroPrevio}` : calleNueva;
                    
                    marcador.bindPopup("Ubicación exacta: " + direccionEncontradaMapa).openPopup();
                }
            });
    }
}

function confirmarUbicacion() {
    if (latTemporal && lngTemporal) {
        document.getElementById('latitud').value = latTemporal.toFixed(6);
        document.getElementById('longitud').value = lngTemporal.toFixed(6);
        
        // Si el mapa encontró una dirección nueva, la ponemos en el input principal
        if (direccionEncontradaMapa !== "") {
            document.getElementById('direccion').value = direccionEncontradaMapa;
        }
        
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

// --- NUEVO: SISTEMA DE GEOCODIFICACIÓN (Búsqueda de Direcciones) ---
let debounceTimeout = null;
let direccionTemporal = ""; // Para guardar la calle cuando hacen clic en el mapa

document.addEventListener("DOMContentLoaded", function() {
    inicializarBuscadorDirecciones();
});
function inicializarBuscadorDirecciones() {
    const inputDireccion = document.getElementById('direccion');
    const listaSugerencias = document.getElementById('lista-sugerencias');

    if(!inputDireccion) return;

    inputDireccion.addEventListener('input', function() {
        clearTimeout(debounceTimeout);
        const query = this.value;

        if (query.length < 4) {
            listaSugerencias.style.display = 'none';
            return;
        }

        debounceTimeout = setTimeout(() => {
            // Buscamos con addressdetails=1 para obtener la calle limpia
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=pe&addressdetails=1&limit=5`)
                .then(response => response.json())
                .then(data => {
                    listaSugerencias.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(lugar => {
                            const li = document.createElement('li');
                            li.textContent = lugar.display_name;
                            li.style.cssText = "padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; font-size: 0.85em; color: #333;";

                            li.onclick = function() {
    // 1. Capturamos el número que el usuario escribió (ej: "123")
    const textoEscrito = inputDireccion.value;
    const matches = textoEscrito.match(/\b\d+\b/g); 
    const numeroManual = matches ? matches[matches.length - 1] : "";

    // 2. Nombre limpio de la calle desde la API
    let calleAPI = lugar.address.road || lugar.address.pedestrian || lugar.display_name.split(',')[0];

    // 3. Unimos todo
    const direccionFinal = numeroManual ? `${calleAPI} ${numeroManual}` : calleAPI;

    // 4. Llenamos los campos
    inputDireccion.value = direccionFinal;
    document.getElementById('latitud').value = parseFloat(lugar.lat).toFixed(6);
    document.getElementById('longitud').value = parseFloat(lugar.lon).toFixed(6);
    
    // Sincronizamos coordenadas temporales
    latTemporal = parseFloat(lugar.lat);
    lngTemporal = parseFloat(lugar.lon);
    direccionEncontradaMapa = direccionFinal; // <--- IMPORTANTE

    if(mapaSeleccion) {
        mapaSeleccion.setView([latTemporal, lngTemporal], 19);
        actualizarMarcador(latTemporal, lngTemporal, false); 
    }
    listaSugerencias.style.display = 'none';
};
                            listaSugerencias.appendChild(li);
                        });
                        listaSugerencias.style.display = 'block';
                    }
                });
        }, 600); 
    });
}


// Agrega esto al final de tu archivo membresia.js o dentro del DOMContentLoaded
document.addEventListener("DOMContentLoaded", function() {
    const flashData = document.getElementById('flash-data');
    
    if (flashData) {
        const mensaje = flashData.getAttribute('data-mensaje');
        const tipo = flashData.getAttribute('data-tipo');

        Swal.fire({
            icon: tipo,
            title: mensaje,
            showConfirmButton: false,
            timer: 2000,
            toast: true,
            position: 'top-end',
            timerProgressBar: true
        });
    }
    
    // ... resto de tus funciones (inicializarSelect2, etc.)
});
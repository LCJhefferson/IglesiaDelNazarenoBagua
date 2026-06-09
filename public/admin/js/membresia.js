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

    // SI el marcador se movió, buscamos el nombre de la calle automáticamente
    if (buscarDireccion) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data.display_name) {
                    direccionEncontradaMapa = data.display_name;
                    // Opcional: mostrar un mensajito en el mapa de qué dirección se encontró
                    marcador.bindPopup("Dirección detectada: " + data.display_name).openPopup();
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
                                // 1. Rescatamos el número que el usuario escribió (buscamos cualquier grupo de números)
                                const textoEscrito = inputDireccion.value;
                                const matchNumero = textoEscrito.match(/\d+/g); // Busca todos los números
                                const ultimoNumero = matchNumero ? matchNumero[matchNumero.length - 1] : "";

                                // 2. Obtenemos el nombre de la calle limpio de la API
                                // Prioridad: road (calle) > house_number > o el primer segmento antes de la coma
                                let nombreCalleAPI = lugar.address.road || lugar.address.pedestrian || lugar.display_name.split(',')[0];

                                // 3. Construimos la dirección final: Calle + Numero (si existía)
                                const direccionFinal = ultimoNumero 
                                    ? `${nombreCalleAPI} ${ultimoNumero}` 
                                    : nombreCalleAPI;

                                // 4. Actualizamos el input y los campos ocultos
                                inputDireccion.value = direccionFinal;
                                
                                const lat = parseFloat(lugar.lat);
                                const lng = parseFloat(lugar.lon);
                                
                                document.getElementById('latitud').value = lat.toFixed(6);
                                document.getElementById('longitud').value = lng.toFixed(6);
                                
                                // Sincronizar con el mapa
                                latTemporal = lat;
                                lngTemporal = lng;
                                direccionEncontradaMapa = direccionFinal; // Guardamos para que el mapa no lo borre

                                if(mapaSeleccion) {
                                    mapaSeleccion.setView([lat, lng], 19);
                                    actualizarMarcador(lat, lng, false); // false para que no vuelva a buscar la calle
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
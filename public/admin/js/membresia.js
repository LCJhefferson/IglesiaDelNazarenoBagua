/**
 * ARCHIVO: membresia.js
 * Descripción: Gestión de modal, fecha de nacimiento, filtros e integración con mapas
 */

document.addEventListener("DOMContentLoaded", function() {
    inicializarSelect2();
    inicializarBuscadorDirecciones();
    inicializarFlashData();
    inicializarEventosFecha();
});

// 1. GESTIÓN Y SINCRONIZACIÓN DE FECHA DE NACIMIENTO
function inicializarEventosFecha() {
    // Escuchar cambios en inputs de día, mes y año
    ['fn_dia', 'fn_mes', 'fn_anio'].forEach(id => {
        const elem = document.getElementById(id);
        if (elem) {
            elem.addEventListener('input', sincronizarFechaNacimiento);
            elem.addEventListener('change', sincronizarFechaNacimiento);
        }
    });

    // Validar la fecha antes de enviar el formulario
    const form = document.getElementById("formMiembro");
    if (form) {
        form.addEventListener("submit", function(e) {
            if (!validarFechaNacimiento()) {
                e.preventDefault(); // Detiene el envío si la fecha es inválida o posterior a hoy
            }
        });
    }
}

function sincronizarFechaNacimiento() {
    const diaElem = document.getElementById('fn_dia');
    const mesElem = document.getElementById('fn_mes');
    const anioElem = document.getElementById('fn_anio');
    const hiddenElem = document.getElementById('fecha_nacimiento');

    if (!diaElem || !mesElem || !anioElem || !hiddenElem) return;

    const diaVal = diaElem.value.trim();
    const mesVal = mesElem.value.trim();
    const anioVal = anioElem.value.trim();

    if (diaVal && mesVal && anioVal && anioVal.length === 4) {
        const dia = String(diaVal).padStart(2, '0');
        const mes = String(mesVal).padStart(2, '0');
        hiddenElem.value = `${anioVal}-${mes}-${dia}`;
    } else {
        hiddenElem.value = '';
    }
}

function validarFechaNacimiento() {
    const diaInput = document.getElementById('fn_dia').value.trim();
    const mesInput = document.getElementById('fn_mes').value.trim();
    const anioInput = document.getElementById('fn_anio').value.trim();

    if (!diaInput && !mesInput && !anioInput) {
        document.getElementById('fecha_nacimiento').value = '';
        return true; 
    }

    if (!diaInput || !mesInput || !anioInput) {
        Swal.fire('Fecha Incompleta', 'Por favor complete el día, mes y año de nacimiento.', 'warning');
        return false;
    }

    const dia = parseInt(diaInput, 10);
    const mes = parseInt(mesInput, 10);
    const anio = parseInt(anioInput, 10);

    const diasEnMes = new Date(anio, mes, 0).getDate();
    if (dia < 1 || dia > diasEnMes) {
        Swal.fire('Fecha Inválida', `El mes seleccionado solo tiene ${diasEnMes} días.`, 'warning');
        return false;
    }

    const fechaSeleccionada = new Date(anio, mes - 1, dia);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    if (fechaSeleccionada > hoy) {
        Swal.fire('Fecha Inválida', 'La fecha de nacimiento no puede ser posterior a la fecha actual.', 'warning');
        return false;
    }

    const mm = String(mes).padStart(2, '0');
    const dd = String(dia).padStart(2, '0');
    document.getElementById('fecha_nacimiento').value = `${anio}-${mm}-${dd}`;

    return true;
}

function limpiarCamposFecha() {
    const ids = ['fn_dia', 'fn_mes', 'fn_anio', 'fecha_nacimiento'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
}

// 2. INICIALIZACIÓN DE SELECT2
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

// 3. GESTIÓN DEL MODAL
function abrirModal() {
    const modal = document.getElementById("modal");
    const form = document.getElementById("formMiembro");

    if (modal) {
        modal.classList.add("active");
        modal.style.display = "flex";

        if (form) form.reset();

        // Restablecer el valor predeterminado a '1' (Saludable)
        const selectCondicion = document.getElementById("condicion_id") || document.getElementsByName("condicion_id")[0];
        if (selectCondicion) {
            selectCondicion.value = "1";
        }

        $('#cargos_select').val(null).trigger('change');

        limpiarCamposFecha();

        // Configuración para modo "Nuevo"
        document.getElementById("btnAgregar").style.display = "inline-block";
        document.getElementById("btnActualizar").style.display = "none";
        document.getElementById("tituloModal").textContent = "Nuevo Miembro";

        document.getElementsByName("id")[0].value = "";
        document.getElementById("latitud").value = "";
        document.getElementById("longitud").value = "";

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

window.onclick = function(event) {
    const modal = document.getElementById("modal");
    if (event.target === modal) {
        cerrarModal();
    }
};

// 4. EDICIÓN DE REGISTROS
function editar(m) {
    abrirModal();

    // Ajustar UI para modo "Edición"
    document.getElementById("tituloModal").textContent = "Editar Miembro";
    document.getElementById("btnAgregar").style.display = "none";
    document.getElementById("btnActualizar").style.display = "inline-block";

    // Llenado de campos básicos
    document.getElementsByName("id")[0].value = m.id;
    document.getElementsByName("nombres")[0].value = m.nombres;
    document.getElementsByName("apellidos")[0].value = m.apellidos;
    document.getElementsByName("telefono")[0].value = m.telefono;
    document.getElementsByName("direccion")[0].value = m.direccion;

    // Validación y desglose de fecha de nacimiento
    if (m.fecha_nacimiento && m.fecha_nacimiento !== '0000-00-00' && m.fecha_nacimiento !== '0000-00-00 00:00:00') {
        const partes = m.fecha_nacimiento.split(' ')[0].split('-');
        if (partes.length === 3 && partes[0] !== '0000') {
            document.getElementById("fn_anio").value = partes[0];
            document.getElementById("fn_mes").value = partes[1].padStart(2, '0');
            document.getElementById("fn_dia").value = parseInt(partes[2], 10);
            document.getElementById("fecha_nacimiento").value = `${partes[0]}-${partes[1].padStart(2, '0')}-${partes[2].padStart(2, '0')}`;
        } else {
            limpiarCamposFecha();
        }
    } else {
        limpiarCamposFecha();
    }

    document.getElementsByName("condicion_id")[0].value = m.condicion_id;
    document.getElementById("tipo_miembro_id").value = m.tipo_miembro_id;
    document.getElementById("latitud").value = m.latitud || "";
    document.getElementById("longitud").value = m.longitud || "";
    
    const inputEstado = document.getElementById("inputEstado");
    if (inputEstado) inputEstado.value = m.estado;

    // Llenado de Select2 (Cargos Múltiples)
    if (m.cargos_ids) {
        let ids = Array.isArray(m.cargos_ids) ? m.cargos_ids : m.cargos_ids.split(',');
        $('#cargos_select').val(ids).trigger('change');
    } else if (m.cargo_id) {
        $('#cargos_select').val([m.cargo_id]).trigger('change');
    }
}

// 5. FILTROS Y BÚSQUEDA
function filtrarTabla() {
    const busqueda = document.getElementById("buscar").value.toLowerCase();
    const filtroTipo = document.getElementById("filtroTipo").value.toLowerCase();
    const filtroRol = document.getElementById("filtroRol").value.toLowerCase();
    const filtroEstado = document.getElementById("filtroEstado").value;
    
    const filas = document.querySelectorAll("#tablaCuerpo tr");

    filas.forEach(fila => {
        const nombre = fila.querySelector("td:nth-child(1)").textContent.toLowerCase();
        const tipo = fila.querySelector(".col-tipo").textContent.toLowerCase();
        const rol = fila.querySelector(".col-rol").textContent.toLowerCase();
        const estado = fila.getAttribute("data-estado");

        const coincideNombre = nombre.includes(busqueda);
        const coincideTipo = filtroTipo === "" || tipo.includes(filtroTipo);
        const coincideRol = filtroRol === "" || rol.includes(filtroRol);
        const coincideEstado = filtroEstado === "" || estado === filtroEstado;

        fila.style.display = (coincideNombre && coincideTipo && coincideRol && coincideEstado) ? "" : "none";
    });
}

// 6. MAPA Y GEOLOCALIZACIÓN
let mapaSeleccion;
let marcador;
let latTemporal = null;
let lngTemporal = null;
let direccionEncontradaMapa = ""; 

function abrirMapa() {
    document.getElementById('modalMapa').style.display = 'block';

    const latGuardada = document.getElementById('latitud').value;
    const lngGuardada = document.getElementById('longitud').value;

    if (!mapaSeleccion) {
        const initialLat = latGuardada ? parseFloat(latGuardada) : -5.640882;
        const initialLng = lngGuardada ? parseFloat(lngGuardada) : -78.529884;

        mapaSeleccion = L.map('mapa-seleccionar').setView([initialLat, initialLng], 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapaSeleccion);

        actualizarMarcador(initialLat, initialLng, false);

        mapaSeleccion.on('click', function(e) {
            actualizarMarcador(e.latlng.lat, e.latlng.lng, true);
        });
    } else if (latGuardada && lngGuardada) {
        const lat = parseFloat(latGuardada);
        const lng = parseFloat(lngGuardada);
        mapaSeleccion.setView([lat, lng], 18);
        actualizarMarcador(lat, lng, false);
    }

    setTimeout(() => {
        mapaSeleccion.invalidateSize(true);
    }, 300);
}

function actualizarMarcador(lat, lng, buscarDireccion = true) {
    latTemporal = lat;
    lngTemporal = lng;

    if (marcador) {
        marcador.setLatLng([lat, lng]);
    } else {
        marcador = L.marker([lat, lng], { draggable: true }).addTo(mapaSeleccion);
        marcador.on('dragend', function() {
            const position = marcador.getLatLng();
            actualizarMarcador(position.lat, position.lng, true);
        });
    }

    if (buscarDireccion) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data.display_name) {
                    const actualValue = document.getElementById('direccion').value;
                    const matches = actualValue.match(/\b\d+\b/g);
                    const numeroPrevio = matches ? matches[matches.length - 1] : "";

                    let calleNueva = data.address.road || data.address.pedestrian || data.display_name.split(',')[0];
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

// 7. CONFIRMACIÓN PERSONALIZADA
let urlConfirmacion = "";

function showConfirm(url) {
    urlConfirmacion = url;
    const modal = document.getElementById("customConfirm");
    modal.style.display = "flex";
    
    document.getElementById("btnConfirmAction").onclick = function() {
        window.location.href = urlConfirmacion;
    };
}

function closeConfirm() {
    const modal = document.getElementById("customConfirm");
    modal.style.display = "none";
    modal.classList.remove("active");
}

// 8. BUSCADOR DE DIRECCIONES (GEOCODIFICACIÓN)
let debounceTimeout = null;

function inicializarBuscadorDirecciones() {
    const inputDireccion = document.getElementById('direccion');
    const listaSugerencias = document.getElementById('lista-sugerencias');

    if (!inputDireccion || !listaSugerencias) return;

    inputDireccion.addEventListener('input', function() {
        clearTimeout(debounceTimeout);
        const query = this.value;

        if (query.length < 4) {
            listaSugerencias.style.display = 'none';
            return;
        }

        debounceTimeout = setTimeout(() => {
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
                                const textoEscrito = inputDireccion.value;
                                const matches = textoEscrito.match(/\b\d+\b/g); 
                                const numeroManual = matches ? matches[matches.length - 1] : "";

                                let calleAPI = lugar.address.road || lugar.address.pedestrian || lugar.display_name.split(',')[0];
                                const direccionFinal = numeroManual ? `${calleAPI} ${numeroManual}` : calleAPI;

                                inputDireccion.value = direccionFinal;
                                document.getElementById('latitud').value = parseFloat(lugar.lat).toFixed(6);
                                document.getElementById('longitud').value = parseFloat(lugar.lon).toFixed(6);
                                
                                latTemporal = parseFloat(lugar.lat);
                                lngTemporal = parseFloat(lugar.lon);
                                direccionEncontradaMapa = direccionFinal;

                                if (mapaSeleccion) {
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

// 9. MENSAJES FLASH DE NOTIFICACIÓN
function inicializarFlashData() {
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
}
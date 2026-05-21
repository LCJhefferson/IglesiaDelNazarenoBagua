// 1. INICIALIZACIÓN DEL MAPA (Con un zoom inicial estándar)
const map = L.map('map').setView([-5.637, -78.528], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Almacén global para los datos y grupo de capas interactivo
let listaMiembros = [];
const capaMarcadores = L.layerGroup().addTo(map);

// Forzar a Leaflet a re-dibujar el contenedor del mapa por si cargó oculto
setTimeout(() => {
    map.invalidateSize();
}, 200);

// 2. CARGA DE DATOS DESDE EL ENDPOINT JSON
fetch(URL_BASE_PROYECTO + 'visitasMapJSON')
    .then(response => {
        if (!response.ok) throw new Error('Error al cargar el JSON del mapa');
        return response.json();
    })
    .then(data => {
        console.log("Datos recibidos en el JS:", data);
        listaMiembros = data;
        renderizarMarcadores(listaMiembros);
    })
    .catch(error => console.error('Error al inicializar Leaflet:', error));

// 3. FUNCIÓN PARA DIBUJAR MARCADORES CON FILTRO DE PRIORIDAD SEMAFÓRICA CORREGIDO
function renderizarMarcadores(miembros) {
    capaMarcadores.clearLayers(); 
    
    const puntosValidos = [];

    miembros.forEach(miembro => {
        // --- Cálculo de iniciales ---
        let iniciales = "M";
        const nombre = miembro.nombre ? miembro.nombre.trim() : '';
        const apellido = miembro.apellido ? miembro.apellido.trim() : '';

        if (nombre && apellido) {
            iniciales = nombre.charAt(0).toUpperCase() + apellido.charAt(0).toUpperCase();
        } else if (nombre) {
            iniciales = nombre.substring(0, 2).toUpperCase();
        }

        // --- Determinación de colores dinámicos (Prioridad de alertas primero) ---
        let claseColor = 'bg-sin-estado';
        let htmlBadgeEstado = "<span style='color:#64748b;'><i class='fa-solid fa-circle-xmark'></i> Sin visitas</span>";
        
        const claseBase = miembro.clase_css || '';
        const estadoNombreBase = miembro.estado_nombre || '';

        // 1. EVALUAR CRÍTICO 
        if (claseBase.includes('critico') || estadoNombreBase === 'Retrasada') {
            claseColor = 'bg-critico';
            htmlBadgeEstado = `<span style='color:#ef4444; font-weight: 600;'><i class='fa-solid fa-triangle-exclamation'></i> ${miembro.estado_texto || 'Pendiente crítico'}</span>`;
        } 
        // 2. EVALUAR PRÓXIMO 
        else if (claseBase.includes('proximo') || estadoNombreBase === 'Cercano') {
            claseColor = 'bg-proximo';
            htmlBadgeEstado = `<span style='color:#f59e0b; font-weight: 600;'><i class='fa-solid fa-clock'></i> ${miembro.estado_texto || 'Pendiente próximo'}</span>`;
        } 
        // 3. EVALUAR INTERMEDIO
        else if (claseBase.includes('intermedio')) {
            claseColor = 'bg-intermedio';
            htmlBadgeEstado = `<span style='color:#3b82f6; font-weight: 600;'><i class='fa-solid fa-user-check'></i> ${miembro.estado_texto || 'Visitado intermedio'}</span>`;
        } 
        // 4. EVALUAR RECIENTE
        else if (claseBase.includes('reciente') || estadoNombreBase === 'Visitado') {
            claseColor = 'bg-reciente';
            htmlBadgeEstado = `<span style='color:#10b981; font-weight: 600;'><i class='fa-solid fa-circle-check'></i> ${miembro.estado_texto || 'Visitado reciente'}</span>`;
        } 
        // 5. MIEMBROS SIN NINGUNA VISITA REGISTRADA
        else {
            claseColor = 'bg-sin-estado';
            htmlBadgeEstado = "<span style='color:#64748b;'><i class='fa-solid fa-circle-xmark'></i> Sin visitas registradas</span>";
        }

        //Crear círculo DivIcon personalizado
        const iconPersonalizado = L.divIcon({
            className: `custom-marker-icon ${claseColor}`,
            html: `<span>${iniciales}</span>`,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        //Contenido HTML del Popup
        const popupContenido = `
            <div style="font-family: 'Segoe UI', sans-serif; padding: 5px; min-width: 195px;">
                <strong style="font-size: 1.05rem; color: #1e293b;">${nombre} ${apellido}</strong><br>
                <small style="color:#64748b; display:inline-block; margin-top:2px;">
                    <i class="fa-solid fa-location-dot"></i> ${miembro.direccion || 'Sin dirección registrada'}
                </small>
                <div style="border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 6px; font-size: 0.9rem;">
                    <strong>Estado:</strong> ${htmlBadgeEstado}<br>
                    <strong>Motivo:</strong> ${miembro.motivo || 'Ninguno'}<br>
                    <strong>Fecha:</strong> ${miembro.fecha_visita ? miembro.fecha_visita : 'No registrada'}
                </div>
            </div>
        `;

        const lat = parseFloat(miembro.latitud);
        const lng = parseFloat(miembro.longitud);

        if (!isNaN(lat) && !isNaN(lng)) {
            L.marker([lat, lng], { icon: iconPersonalizado })
                .bindPopup(popupContenido)
                .addTo(capaMarcadores);
                
            puntosValidos.push([lat, lng]);
        }
    });

    // Auto-ajuste de cámara inteligente
    if (puntosValidos.length > 0) {
        const bounds = L.latLngBounds(puntosValidos);
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
    }
}

// 4. LÓGICA DE FILTRADO COMBINADO EN TIEMPO REAL (CORREGIDO CON PRIORIDAD)
const inputNombre = document.getElementById('buscarNombre');
const selectEstado = document.getElementById('buscarEstado');

function ejecutarFiltros() {
    const busquedaText = inputNombre.value.toLowerCase().trim();
    const estadoSeleccionado = selectEstado.value;

    const miembrosFiltrados = listaMiembros.filter(miembro => {
        const nombreCompleto = `${miembro.nombre} ${miembro.apellido}`.toLowerCase();
        const matchNombre = nombreCompleto.includes(busquedaText);
        
        let estadoMiembro = 'ninguno';
        const claseBase = miembro.clase_css || '';
        const estadoNombreBase = miembro.estado_nombre || '';

        // El mapeo de filtrado debe seguir exactamente el mismo orden de prioridad
        if (claseBase.includes('critico') || estadoNombreBase === 'Retrasada') {
            estadoMiembro = 'critico';
        } else if (claseBase.includes('proximo') || estadoNombreBase === 'Cercano') {
            estadoMiembro = 'proximo';
        } else if (claseBase.includes('intermedio')) {
            estadoMiembro = 'intermedio';
        } else if (claseBase.includes('reciente') || estadoNombreBase === 'Visitado') {
            estadoMiembro = 'reciente';
        } else {
            estadoMiembro = 'ninguno';
        }

        const matchEstado = (estadoSeleccionado === 'todos' || estadoMiembro === estadoSeleccionado);
        
        return matchNombre && matchEstado;
    });

    renderizarMarcadores(miembrosFiltrados);

    if (busquedaText !== "" && miembrosFiltrados.length === 1) {
        const unico = miembrosFiltrados[0];
        map.setView([parseFloat(unico.latitud), parseFloat(unico.longitud)], 17);
    }
}

inputNombre.addEventListener('input', ejecutarFiltros);
selectEstado.addEventListener('change', ejecutarFiltros);
// OBTENCIÓN DE RUTA DINÁMICA PERFECTA
const pathSegments = window.location.pathname.split('/');
const RUTA_PROYECTO = pathSegments[1] ? '/' + pathSegments[1] + '/' : '/';
const RUTA_BASE = window.location.origin + RUTA_PROYECTO;

// Variable global para controlar los límites de registros visibles por pestaña
let limitesRender = { miembros: 10, visitas: 10, discipulado: 10, cumpleanos: 10 };
let datosGlobalescache = {}; // Almacena la respuesta full para paginar en memoria sin saturar la BD
let timeoutBusqueda = null;

document.addEventListener('DOMContentLoaded', () => {
    // Inicialización de selectores dinámicos desde la BD al cargar la página
    inicializarCondicionesMiembros();

    // EVITAR RECARGA DE PÁGINA ACCIDENTAL: Si presionan ENTER en un buscador, que no se recargue la web
    const formDiscipulado = document.getElementById('form-discipulado');
    if (formDiscipulado) {
        formDiscipulado.addEventListener('submit', (e) => e.preventDefault());
    }

    // CERRAR LISTAS AL HACER CLIC AFUERA: Oculta los resultados si el usuario hace clic en otra parte
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.autocomplete-container')) {
            document.querySelectorAll('.autocomplete-resultados').forEach(box => {
                box.innerHTML = '';
                box.style.display = 'none';
            });
        }
    });
});

/**
 * Conmuta la visibilidad de los paneles de reportes (Sistema de Pestañas)
 */
window.cambiarPestaña = function(tipo, botonActivo) {
    // 1. Quitar la clase active de todos los botones y ponérsela al actual
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    botonActivo.classList.add('active');

    // 2. Ocultar todas las tarjetas de reportes
    document.querySelectorAll('.card-reporte').forEach(card => card.classList.remove('active'));

    // 3. Mostrar únicamente la tarjeta seleccionada
    const panelAsociado = document.getElementById(`pane-${tipo}`);
    if (panelAsociado) {
        panelAsociado.classList.add('active');
    }
};

/**
 * Carga las opciones de condiciones de miembros y estados de discípulo desde la Base de Datos
 */
function inicializarCondicionesMiembros() {
    const selectCondiciones = document.getElementById('select-condiciones');
    const selectEstadosDisc = document.getElementById('select-estados-discipulo');

    fetch(`${RUTA_BASE}inicializar_filtros_reporte`)
    .then(res => {
        if (!res.ok) throw new Error(`HTTP status: ${res.status}`);
        return res.json();
    })
    .then(data => {
        // 1. Cargar condiciones de miembros
        if (selectCondiciones && data.condiciones && Array.isArray(data.condiciones)) {
            selectCondiciones.innerHTML = '<option value="">Todas</option>'; 
            data.condiciones.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.nombre; 
                selectCondiciones.appendChild(opt);
            });
        }
        
        // 2. Cargar estados de discipulado maestro (Uso exclusivo de la nueva tabla)
        if (selectEstadosDisc && data.estados_discipulo && Array.isArray(data.estados_discipulo)) {
            selectEstadosDisc.innerHTML = '<option value="">Todos</option>';
            data.estados_discipulo.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.textContent = e.nombre;
                selectEstadosDisc.appendChild(opt);
            });
        }
    })
    .catch(err => console.error("Error cargando selectores iniciales:", err));
}

window.cargarVistaPrevia = function(tipo) {
    const form = document.getElementById(`form-${tipo}`);
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    
    if (!form || !tbody) return; 

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('tipo', tipo);

    fetch(`${RUTA_BASE}datos_reporte?${params.toString()}`)
    .then(res => {
        if (!res.ok) throw new Error(`HTTP status: ${res.status}`);
        return res.json();
    })
    .then(datos => {
        // Guardamos en caché global el set completo para manejar el botón "Ver más"
        datosGlobalescache[tipo] = datos || [];
        // Reseteamos el límite de vista inicial para esta consulta
        limitesRender[tipo] = 100;

        window.renderizarBloqueTabla(tipo);
    })
    .catch(err => console.error(`Error cargando vista previa de ${tipo}:`, err));
};

/**
 * Descarga y exportación física de archivos dinámico
 */
window.descargar = function(tipo, formato) {
    const form = document.getElementById(`form-${tipo}`);
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('tipo', tipo);
    params.append('formato', formato);

    window.location.href = `${RUTA_BASE}descargar_reporte?${params.toString()}`;
};

/**
 * SISTEMA AUTOCOMPLETE REACTIVO: Busca sugerencias en tiempo real y recarga la tabla al seleccionar
 */
window.ejecutarAutocomplete = function(tipo, input) {
    const term = input.value.trim();
    const contenedorId = `res-${tipo}`; 
    const contenedor = document.getElementById(contenedorId);
    
    if (!contenedor) return;

    // DEFINICIÓN DE IDENTIFICADORES ÚNICOS PARA CADA CASO:
    let sufijoHidden = "";
    if (tipo === 'miembros') {
        sufijoHidden = "miembro";
    } else if (tipo === 'discipulado_alumno') {
        sufijoHidden = "discipulado-alumno";
    } else if (tipo === 'grupos') {
        sufijoHidden = "grupo";
    } else if (tipo === 'cumpleanos') {
        sufijoHidden = "cumpleanero";
    } else {
        sufijoHidden = "discipulador";
    }
    
    // Si se borra el texto, vaciamos el input hidden y recargamos la tabla correspondiente
    if (term.length === 0) {
        contenedor.innerHTML = '';
        contenedor.style.display = 'none';
        
        const hiddenInput = document.getElementById(`hidden-${sufijoHidden}`);
        if (hiddenInput && hiddenInput.value !== '') {
            hiddenInput.value = '';
            
            if (tipo === 'cumpleanos') {
                window.cargarVistaPrevia('cumpleanos');
            } else if (tipo === 'miembros') {
                window.cargarVistaPrevia('miembros');
            } else {
                window.cargarVistaPrevia('discipulado'); 
            }
        }
        return;
    }

    if (term.length < 2) {
        contenedor.innerHTML = '';
        contenedor.style.display = 'none';
        return;
    }

    // MAPEO DE RUTAS: Tanto 'miembros', 'cumpleanos' y 'discipulado_alumno' buscan personas en la tabla 'miembros'
    const tipoConsultaUrl = (tipo === 'cumpleanos' || tipo === 'discipulado_alumno' || tipo === 'miembros') ? 'miembros' : tipo;

    fetch(`${RUTA_BASE}sugerencias_reporte?tipo=${tipoConsultaUrl}&term=${encodeURIComponent(term)}`)
    .then(res => {
        if (!res.ok) throw new Error(`HTTP status: ${res.status}`);
        return res.json();
    })
    .then(data => {
        contenedor.innerHTML = '';

        if (!data || data.length === 0) {
            contenedor.innerHTML = '<div class="autocomplete-no-resultados" style="padding:8px; color:#888;">No se encontraron coincidencias</div>';
            contenedor.style.display = 'block';
            return;
        }

        contenedor.style.display = 'block';

        data.forEach(item => {
            const opcion = document.createElement('div');
            opcion.className = 'autocomplete-item';
            opcion.style.cursor = 'pointer';
            opcion.textContent = item.nombre || item.nombre_completo;

            opcion.addEventListener('click', () => {
                input.value = item.nombre || item.nombre_completo; 
                
                const hiddenInput = document.getElementById(`hidden-${sufijoHidden}`);
                if (hiddenInput) {
                    hiddenInput.value = item.id; 
                }

                contenedor.innerHTML = '';
                contenedor.style.display = 'none';
                
                // Carga la vista previa en la pestaña correcta
                if (tipo === 'cumpleanos') {
                    window.cargarVistaPrevia('cumpleanos');
                } else if (tipo === 'miembros') {
                    window.cargarVistaPrevia('miembros');
                } else {
                    window.cargarVistaPrevia('discipulado'); // Si es discipulado_alumno, grupos o discipuladores, actualiza la tabla de discipulado
                }
            });

            contenedor.appendChild(opcion);
        });
    })
    .catch(err => console.error(`Error en autocomplete de ${tipo}:`, err));
};

/**
 * SISTEMA DE LIMPIEZA: Resetea el formulario, limpia autocompletes y VACÍA la tabla de forma segura
 */
window.limpiarFiltros = function(tipo) {
    const form = document.getElementById(`form-${tipo}`);
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    
    if (form) {
        form.reset(); 
        
        if (tipo === 'discipulado') {
            ['miembro', 'grupo', 'discipulador'].forEach(id => {
                const inputTexto = document.getElementById(`input-${id}`);
                const inputHidden = document.getElementById(`hidden-${id}`);
                if (inputTexto) inputTexto.value = '';
                if (inputHidden) inputHidden.value = '';
            });
        }
        
        if (tipo === 'cumpleanos') {
            const inputTexto = document.getElementById('input-cumpleanero');
            const inputHidden = document.getElementById('hidden-cumpleanero');
            if (inputTexto) inputTexto.value = '';
            if (inputHidden) inputHidden.value = '';
        }
        if (tipo === 'miembros') {
        const inputTexto = document.getElementById('input-miembro');
        const inputHidden = document.getElementById('hidden-miembro');
        if (inputTexto) inputTexto.value = '';
        if (inputHidden) inputHidden.value = '';
        
        // Y luego de resetear, recarga la vista limpia
        //window.cargarVistaPrevia('miembros');
    }
    }

    if (tbody) {
        const numColumnas = tipo === 'miembros' ? 7 : (tipo === 'visitas' ? 5 : (tipo === 'cumpleanos' ? 4 : 3));
        tbody.innerHTML = `<tr><td colspan="${numColumnas}" style="text-align:center; padding:25px; color:#94a3b8; font-style: italic;">
            Filtros limpiados. Use los buscadores o selectores para consultar registros específicos.
        </td></tr>`;
    }
};



/**
 * Pinta únicamente los registros permitidos por el límite actual de paginación
 */
window.renderizarBloqueTabla = function(tipo) {
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    const datos = datosGlobalescache[tipo] || [];
    tbody.innerHTML = ''; 

    if (datos.length === 0) {
        const numColumnas = tipo === 'miembros' ? 7 : (tipo === 'visitas' ? 5 : (tipo === 'cumpleanos' ? 4 : 3));
        tbody.innerHTML = `<tr><td colspan="${numColumnas}" style="text-align:center; padding:15px; color:#888;">No se encontraron registros para los filtros seleccionados</td></tr>`;
        window.removerBotonVerMas(tipo);
        return;
    }

    const limiteActual = limitesRender[tipo];
    const datosVisibles = datos.slice(0, limiteActual);

    datosVisibles.forEach(fila => {
        const tr = document.createElement('tr');
        let columnas = [];

        if (tipo === 'miembros') {
            let origenTexto = fila.origen == 1 ? 'Local' : (fila.origen == 2 ? 'Externo' : 'Otros');
            columnas = [fila.nombre_completo || 'Sin Nombre', fila.telefono || '-', fila.edad !== undefined ? fila.edad : '-', fila.direccion || '-', origenTexto, fila.condicion || 'Sin asignar', fila.estado || 'Activo'];
        } else if (tipo === 'visitas') {
            let celdaFechaHtml = fila.ultima_visita !== 'Sin visitas' 
                ? `${fila.ultima_visita}<br><small style="color: #64748b; font-weight: normal; display: block; margin-top: 2px;">${fila.dias_transcurridos || ''}</small>`
                : `<span style="color: #ef4444; font-weight: bold;">Sin visitas</span><br><small style="color: #94a3b8;">${fila.dias_transcurridos || 'Requiere atención'}</small>`;
            columnas = [fila.nombre_completo || 'Sin Nombre', fila.direccion || 'Sin dirección', celdaFechaHtml, fila.motivo || 'Sin motivo', fila.estado || 'Pendiente'];
        } else if (tipo === 'discipulado') {
            // MAPEO REGLAMENTARIO CORREGIDO Y ORDENADO AL MILÍMETRO
            let nombreMiembro = fila.integrante_nombre || 'Sin Nombre';
            let nombreGrupo = fila.grupo_nombre || 'Sin Grupo';
            let nombreLider = fila.discipulador_nombre || 'Sin asignar';
            let estadoTexto = fila.estado_alumno_texto || 'Sin estado';

            // Armamos el bloque visual dinámico que AHORA VA EN LA COLUMNA 1 (Índice 0)
            let bloqueGrupoHtml = `<strong>${nombreGrupo}</strong><br><small style="color: #64748b;">Líder: ${nombreLider}</small>`;
            
            columnas = [
                bloqueGrupoHtml,  // Columna 1 (index 0): Grupo de Crecimiento (con Líder abajo)
                nombreMiembro,    // Columna 2 (index 1): Miembro / Alumno
                estadoTexto       // Columna 3 (index 2): Estado Del Alumno
            ];
        } else if (tipo === 'cumpleanos') { 
            let fechaFormateada = '-';
            if (fila.fecha_nacimiento) {
                const partes = fila.fecha_nacimiento.split('-'); 
                if (partes.length === 3) {
                    const fechaLocal = new Date(partes[0], partes[1] - 1, partes[2]);
                    fechaFormateada = fechaLocal.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
                }
            }
            columnas = [fila.nombre_completo || 'Sin Nombre', fila.telefono || '-', fechaFormateada, fila.edad !== undefined ? `${fila.edad} años` : '-'];
        }

        columnas.forEach((texto, index) => {
            const td = document.createElement('td');
            // CORRECCIÓN DE ÍNDICE: En discipulado el HTML ahora está en la primera columna (index 0)
            if ((tipo === 'discipulado' && index === 0) || (tipo === 'visitas' && index === 2)) {
                td.innerHTML = texto;
            } else {
                td.textContent = texto;
            }
            if (tipo === 'visitas' && index === 2) td.style.textAlign = 'center'; 
            tr.appendChild(td);
        });

        tbody.appendChild(tr);
    });

    if (datos.length > limiteActual) {
        window.mostrarBotonVerMas(tipo, datos.length - limiteActual);
    } else {
        window.removerBotonVerMas(tipo);
    }
};
/**
 * Muestra un botón limpio debajo de la tabla indicando cuántos registros quedan pendientes
 */
window.mostrarBotonVerMas = function(tipo, restantes) {
    window.removerBotonVerMas(tipo);
    const tabla = document.getElementById(`tabla-${tipo}`);
    if (!tabla) return;

    const btn = document.createElement('button');
    btn.id = `btn-vermas-${tipo}`;
    btn.className = 'btn-ver-mas'; 
    btn.style = 'display: block; margin: 15px auto; padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
    btn.textContent = `Mostrar más registros (${restantes} restantes)`;
    
    btn.onclick = function() {
        limitesRender[tipo] += 100; 
        window.renderizarBloqueTabla(tipo);
    };

    tabla.parentNode.insertBefore(btn, tabla.nextSibling);
};

window.removerBotonVerMas = function(tipo) {
    const btn = document.getElementById(`btn-vermas-${tipo}`);
    if (btn) btn.remove();
};

/**
 * Buscador Reactivo con protección Debounce para entrada directa por teclado
 */
window.buscarEnTiempoReal = function(tipo, input) {
    clearTimeout(timeoutBusqueda);
    
    timeoutBusqueda = setTimeout(() => {
        const form = document.getElementById(`form-${tipo}`);
        if (!form) return;

        window.cargarVistaPrevia(tipo);
    }, 300);
};
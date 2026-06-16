// OBTENCIÓN DE RUTA DINÁMICA PERFECTA
const pathSegments = window.location.pathname.split('/');
const RUTA_PROYECTO = pathSegments[1] ? '/' + pathSegments[1] + '/' : '/';
const RUTA_BASE = window.location.origin + RUTA_PROYECTO;

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

/**
 * Renderiza la tabla de vista previa con AJAX de forma dinámica para cualquier módulo
 */
window.cargarVistaPrevia = function(tipo) {
    const form = document.getElementById(`form-${tipo}`);
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    
    if (!form || !tbody) {
        console.error(`No se encontró el formulario o la tabla para el tipo: ${tipo}`);
        return; 
    }

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('tipo', tipo);

    fetch(`${RUTA_BASE}datos_reporte?${params.toString()}`)
    .then(res => {
        if (!res.ok) throw new Error(`HTTP status: ${res.status}`);
        return res.json();
    })
    .then(datos => {
        tbody.innerHTML = ''; 

        if (!datos || datos.length === 0) {
            const numColumnas = tipo === 'miembros' ? 7 : (tipo === 'visitas' ? 5 : 3);
            tbody.innerHTML = `<tr><td colspan="${numColumnas}" style="text-align:center; padding:15px; color:#888;">No se encontraron registros para los filtros seleccionados</td></tr>`;
            return;
        }

        datos.forEach(fila => {
            const tr = document.createElement('tr');
            let columnas = [];

            // Evaluamos la estructura de columnas dependiendo de qué reporte solicita la data
            if (tipo === 'miembros') {
                let origenTexto = 'Otros';
                if (fila.origen == 1) origenTexto = 'Local';
                else if (fila.origen == 2) origenTexto = 'Externo';

                columnas = [
                    fila.nombre_completo || 'Sin Nombre',
                    fila.telefono || '-',
                    fila.edad !== undefined ? fila.edad : '-',
                    fila.direccion || '-',
                    origenTexto,
                    fila.condicion || 'Sin asignar',
                    fila.estado || 'Activo'
                ];
            } else if (tipo === 'visitas') {
                columnas = [
                    fila.nombre_completo || 'Sin Nombre',
                    fila.direccion || 'Sin dirección',
                    fila.ultima_visita || 'Sin visitas',
                    fila.motivo || 'Sin motivo',
                    fila.estado || 'Pendiente'
                ];
            } else if (tipo === 'discipulado') {
                // Estructura de 3 columnas cruzando los datos obtenidos del servicio modular
                columnas = [
                    `${fila.grupo_nombre || 'Sin grupo'}<br><small style="color:#666; font-weight:normal;">Líder: ${fila.discipulador_nombre || 'Sin asignar'}</small>`,
                    fila.integrante_nombre || 'Sin Nombre',
                    fila.estado_alumno_texto || 'En proceso'
                ];
            }

            // Inyectamos las celdas a la fila
            columnas.forEach((texto, index) => {
                const td = document.createElement('td');
                
                // Permitimos HTML estructurado en la primera columna de discipulado para ver el líder debajo del grupo
                if (tipo === 'discipulado' && index === 0) {
                    td.innerHTML = texto;
                } else {
                    td.textContent = texto;
                }

                if (tipo === 'visitas' && texto === fila.ultima_visita) {
                    td.style.textAlign = 'center'; 
                }
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });
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
    
    // Determinamos el ID correcto del contenedor flotante de resultados
    const contenedorId = `res-${tipo}`; 
    const contenedor = document.getElementById(contenedorId);
    
    if (!contenedor) return;

    const tipoSingular = tipo === 'miembros' ? 'miembro' : (tipo === 'grupos' ? 'grupo' : 'discipulador');

    // Si el usuario borra por completo el texto, vaciamos el ID oculto y recargamos la tabla
    if (term.length === 0) {
        contenedor.innerHTML = '';
        contenedor.style.display = 'none';
        
        const hiddenInput = document.getElementById(`hidden-${tipoSingular}`);
        if (hiddenInput && hiddenInput.value !== '') {
            hiddenInput.value = '';
            window.cargarVistaPrevia('discipulado'); // Recarga sin este filtro
        }
        return;
    }

    // Esperamos a que escriba al menos 2 caracteres para no saturar el servidor
    if (term.length < 2) {
        contenedor.innerHTML = '';
        contenedor.style.display = 'none';
        return;
    }

    fetch(`${RUTA_BASE}sugerencias_reporte?tipo=${tipo}&term=${encodeURIComponent(term)}`)
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
            
            // Evaluamos el campo de texto que retorne tu modelo
            opcion.textContent = item.nombre || item.nombre_completo;

            // Al hacer clic en una sugerencia de la lista
            opcion.addEventListener('click', () => {
                input.value = item.nombre || item.nombre_completo; // Seteamos el texto visual
                
                const hiddenInput = document.getElementById(`hidden-${tipoSingular}`);
                if (hiddenInput) {
                    hiddenInput.value = item.id; // Seteamos el ID real en el input hidden
                }

                // Ocultamos y limpiamos el contenedor flotante
                contenedor.innerHTML = '';
                contenedor.style.display = 'none';

                // ¡Lanzamos la recarga de la tabla con el nuevo filtro aplicado!
                window.cargarVistaPrevia('discipulado');
            });

            contenedor.appendChild(opcion);
        });
    })
    .catch(err => console.error(`Error en autocomplete de ${tipo}:`, err));
};

/**
 * SISTEMA DE LIMPIEZA DINÁMICO: Resetea el formulario, limpia autocompletes y vacía la tabla
 */
/**
 * SISTEMA DE LIMPIEZA: Resetea el formulario, limpia autocompletes y VACÍA la tabla de forma segura
 */
window.limpiarFiltros = function(tipo) {
    const form = document.getElementById(`form-${tipo}`);
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    
    if (form) {
        form.reset(); // Revierte selectores estándar y cajas de texto
        
        // Limpieza específica para el autocomplete si es discipulado
        if (tipo === 'discipulado') {
            ['miembro', 'grupo', 'discipulador'].forEach(id => {
                const inputTexto = document.getElementById(`input-${id}`);
                const inputHidden = document.getElementById(`hidden-${id}`);
                if (inputTexto) inputTexto.value = '';
                if (inputHidden) inputHidden.value = '';
            });
        }
    }

    // SOLUCIÓN EFICIENTE: En lugar de cargar la vista previa vacía con 2,000 registros,
    // limpiamos el HTML del tbody por completo dejando una instrucción elegante.
    if (tbody) {
        const numColumnas = tipo === 'miembros' ? 7 : (tipo === 'visitas' ? 5 : 3);
        tbody.innerHTML = `<tr><td colspan="${numColumnas}" style="text-align:center; padding:25px; color:#94a3b8; font-style: italic;">
            Filtros limpiados. Use los buscadores o selectores para consultar registros específicos.
        </td></tr>`;
    }
};
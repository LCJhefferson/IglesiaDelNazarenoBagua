// OBTENCIÓN DE RUTA DINÁMICA PERFECTA
const pathSegments = window.location.pathname.split('/');
const RUTA_PROYECTO = pathSegments[1] ? '/' + pathSegments[1] + '/' : '/';
const RUTA_BASE = window.location.origin + RUTA_PROYECTO;

document.addEventListener('DOMContentLoaded', () => {
    // Inicialización del select de condiciones al cargar la página
    inicializarCondicionesMiembros();
});

/**
 * Carga las opciones del selector de condiciones desde la Base de Datos
 */
function inicializarCondicionesMiembros() {
    const select = document.getElementById('select-condiciones');
    if (!select) return;

    fetch(`${RUTA_BASE}inicializar_filtros_reporte`)
    .then(res => {
        if (!res.ok) throw new Error(`HTTP status: ${res.status}`);
        return res.json();
    })
    .then(data => {
        select.innerHTML = '<option value="">Todas</option>'; 
        if (data.condiciones && Array.isArray(data.condiciones)) {
            data.condiciones.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.nombre; 
                select.appendChild(opt);
            });
        }
        // REACTIVADO: Carga automática inicial de la tabla al abrir el módulo
       // cargarVistaPrevia('miembros');
    })
    .catch(err => console.error("Error cargando condiciones:", err));
}

/**
 * Renderiza la tabla de vista previa con AJAX de forma global
 */
window.cargarVistaPrevia = function(tipo) {
    if (tipo !== 'miembros') return; 

    const form = document.getElementById('form-miembros');
    if (!form) return; 

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('tipo', 'miembros');

    fetch(`${RUTA_BASE}datos_reporte?${params.toString()}`)
    .then(res => {
        if (!res.ok) throw new Error(`HTTP status: ${res.status}`);
        return res.json();
    })
    .then(datos => {
        const tbody = document.querySelector('#tabla-miembros tbody');
        if (!tbody) return;
        
        tbody.innerHTML = ''; 

        if (!datos || datos.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:15px; color:#888;">No se encontraron registros para los filtros seleccionados</td></tr>`;
            return;
        }

        datos.forEach(fila => {
            const tr = document.createElement('tr');
            
            // TRADUCCIÓN DEL ORIGEN: 1 = Local, 2 = Externo
            let origenTexto = 'Otros';
            if (fila.origen == 1) {
                origenTexto = 'Local';
            } else if (fila.origen == 2) {
                origenTexto = 'Externo';
            }

            const columnas = [
                fila.nombre_completo || 'Sin Nombre',
                fila.telefono || '-',
                fila.edad !== undefined ? fila.edad : '-',
                fila.direccion || '-',
                origenTexto,
                fila.condicion || 'Sin asignar',
                fila.estado || 'Activo'
            ];

            columnas.forEach(texto => {
                const td = document.createElement('td');
                td.textContent = texto;
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });
    })
    .catch(err => console.error("Error cargando vista previa de miembros:", err));
};

/**
 * FUNCIÓN NUEVA: Resetea el formulario y limpia la tabla visual por completo
 */
window.limpiarFiltrosMiembros = function() {
    const form = document.getElementById('form-miembros');
    if (form) {
        form.reset(); // Devuelve todos los inputs y selects a su valor vacío por defecto
    }

    const tbody = document.querySelector('#tabla-miembros tbody');
    if (tbody) {
        // Dejamos la tabla en blanco con un mensaje limpio para poder pasar tranquilamente al siguiente módulo
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:15px; color:#aaa;">Filtros limpiados. Seleccione un filtro para iniciar una nueva búsqueda.</td></tr>`;
    }
};

/**
 * Descarga y exportación física de archivos de forma global
 */
window.descargar = function(tipo, formato) {
    if (tipo !== 'miembros') return;

    const form = document.getElementById('form-miembros');
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('tipo', 'miembros');
    params.append('formato', formato);

    window.location.href = `${RUTA_BASE}descargar_reporte?${params.toString()}`;
};
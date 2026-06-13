/**
 * Envía las variables del formulario vía AJAX para obtener la vista previa
 * @param {string} modulo 
 */
function cargarVistaPrevia(modulo) {
    const form = document.getElementById(`form-${modulo}`);
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();

    // Llamada dinámica a tu controlador único index/FrontController
    fetch(`datos_reporte?tipo=${modulo}&${params}`)
        .then(response => {
            if (!response.ok) throw new Error("Error en la respuesta del servidor");
            return response.json();
        })
        .then(data => {
            renderizarTabla(modulo, data);
        })
        .catch(error => {
            console.error(`Error al cargar la vista previa de ${modulo}:`, error);
        });
}

/**
 * Renderiza dinámicamente las filas devueltas por el servidor
 * @param {string} modulo 
 * @param {Object} data 
 */
function renderizarTabla(modulo, data) {
    const tbody = document.querySelector(`#tabla-${modulo} tbody`);
    if (!tbody) return;
    
    tbody.innerHTML = ""; // Limpiar tabla previa

    // Lógica especial para metadatos del bloque de discipulado
    if (modulo === 'discipulado') {
        const metaBloque = document.getElementById('bloque-meta-discipulado');
        if (data.meta && data.meta.mostrar) {
            document.getElementById('meta-grupo').textContent = data.meta.grupo || 'N/A';
            document.getElementById('meta-nivel').textContent = data.meta.nivel || 'N/A';
            document.getElementById('meta-estado').textContent = data.meta.estado || 'N/A';
            metaBloque.style.display = 'flex';
        } else {
            metaBloque.style.display = 'none';
        }
    }

    const filas = data.registros || [];
    if (filas.length === 0) return;

    filas.forEach(row => {
        const tr = document.createElement('tr');
        let htmlContent = "";

        switch (modulo) {
            case 'miembros':
                htmlContent = `
                    <td><b>${row.nombre_completo}</b></td>
                    <td>${row.telefono || '-'}</td>
                    <td>${row.edad || '-'}</td>
                    <td>${row.direccion || '-'}</td>
                    <td>${row.origen || '-'}</td>
                    <td>${row.condicion || '-'}</td>
                    <td>${row.estado || '-'}</td>
                `;
                break;
            case 'visitas':
                htmlContent = `
                    <td><b>${row.nombre_completo}</b></td>
                    <td>${row.direccion || '-'}</td>
                    <td>${row.ultima_visita || '-'}</td>
                    <td>${row.motivo || '-'}</td>
                    <td>${row.estado || '-'}</td>
                `;
                break;
            case 'discipulado':
                htmlContent = `
                    <td>${row.integrante}</td>
                    <td>${row.estado_integrante || '-'}</td>
                `;
                break;
            case 'cumpleanos':
                htmlContent = `
                    <td><b>${row.nombre_completo}</b></td>
                    <td>${row.fecha_nacimiento || '-'}</td>
                    <td>${row.edad_actual || '-'} años</td>
                    <td>${row.telefono || '-'}</td>
                `;
                break;
        }

        tr.innerHTML = htmlContent;
        tbody.appendChild(tr);
    });
}

/**
 * Redirecciona al endpoint que compila y descarga el archivo binario
 * @param {string} modulo 
 * @param {string} formato 
 */
function descargar(modulo, formato) {
    const form = document.getElementById(`form-${modulo}`);
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();

    // Redirección directa hacia el FrontController corregido del index
    window.location.href = `descargar_reporte?tipo=${modulo}&formato=${formato}&${params}`;
}

// Carga inicial automatizada de todas las vistas previas al entrar al panel de reportes
document.addEventListener("DOMContentLoaded", () => {
    cargarVistaPrevia('miembros');
    cargarVistaPrevia('visitas');
    cargarVistaPrevia('discipulado');
    cargarVistaPrevia('cumpleanos');
});
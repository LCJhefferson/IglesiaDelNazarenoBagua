// OBTENCIÓN DE RUTA DINÁMICA PERFECTA
const pathSegments = window.location.pathname.split('/');
const RUTA_PROYECTO = pathSegments[1] ? '/' + pathSegments[1] + '/' : '/';
const RUTA_BASE = window.location.origin + RUTA_PROYECTO;

// Control de paginación estructurada por pestaña
let paginacion = {
    miembros: { paginaActual: 1, porPagina: 10 }, 
    visitas: { paginaActual: 1, porPagina: 10 },
    discipulado: { paginaActual: 1, porPagina: 10 },
    cumpleanos: { paginaActual: 1, porPagina: 10 }
};
let datosGlobalescache = {}; 
let timeoutBusqueda = null;

document.addEventListener('DOMContentLoaded', () => {
    inicializarCondicionesMiembros();
    window.cargarVistaPrevia('miembros');

    const formDiscipulado = document.getElementById('form-discipulado');
    if (formDiscipulado) {
        formDiscipulado.addEventListener('submit', (e) => e.preventDefault());
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.autocomplete-container')) {
            document.querySelectorAll('.autocomplete-resultados').forEach(box => {
                // ✅ antes: box.innerHTML = '';
                box.textContent = '';
                box.style.display = 'none';
            });
        }
    });
});

// --- CONFIRMAR ELIMINAR ---
window.confirmarEliminar = function(id, titulo) {
    const modalConfirmar = document.getElementById("modal-confirmar");
    // ✅ antes: innerText
    document.getElementById("confirmar-nombre").textContent = titulo;
    modalConfirmar.style.display = "flex";

    document.getElementById("btn-confirmar-ok").onclick = function() {
        window.location.href = `/IglesiaDelNazarenoBagua/public/index.php?vista=dashboard&seccion=noticias&eliminar=${id}`;
    };
};

window.cerrarConfirmar = function() {
    document.getElementById("modal-confirmar").style.display = "none";
};

// --- DRAG & DROP PORTADA ---
window.procesarImagenPortada = function(file) {
    const txtPortada = document.getElementById("txt-imagen");
    // ✅ antes: innerText
    txtPortada.textContent = "Seleccionada: " + file.name;
    txtPortada.style.color = "var(--verde)";
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById("preview-img").src = e.target.result;
    };
    reader.readAsDataURL(file);
};

// --- TOAST ---
window.mostrarToast = function(mensaje, tipo = "exito") {
    const iconos    = { exito: "fa-circle-check", error: "fa-circle-xmark", info: "fa-circle-info" };
    const container = document.getElementById("toast-container");
    if(!container) return;

    const toast     = document.createElement("div");
    toast.className = `toast ${tipo}`;
    
    // ✅ antes: toast.innerHTML
    const icon = document.createElement("i");
    icon.className = `fa-solid ${iconos[tipo]}`;

    const msg = document.createElement("span");
    msg.textContent = mensaje;

    toast.appendChild(icon);
    toast.appendChild(msg);

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = "toastSalida 0.3s ease forwards";
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

// --- GALERÍA ---
window.renderizarListaGaleria = function() {
    const listaAdjuntos = document.getElementById("lista-imagenes");
    const txtMulti      = document.getElementById("txt-multi");
    
    document.querySelectorAll(".item-nuevo-temp").forEach(el => el.remove());

    archivosGaleria.forEach((file, index) => {
        const li = document.createElement("li");
        li.className = "item-nuevo-temp";
        li.style.cssText = "..."; // estilos omitidos para brevedad

        const reader = new FileReader();
        reader.onload = (e) => {
            // antes: li.innerHTML
            const img = document.createElement("img");
            img.src = e.target.result;
            img.style.cssText = "...";

            const span = document.createElement("span");
            span.title = file.name;
            span.style.cssText = "...";
            span.textContent = `(Nuevo) ${file.name}`;

            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "btn-eliminar-adjunto";
            btn.style.cssText = "...";
            btn.onclick = function() { quitarImagenNueva(index); };

            const icon = document.createElement("i");
            icon.className = "fa-solid fa-xmark";

            btn.appendChild(icon);
            li.appendChild(img);
            li.appendChild(span);
            li.appendChild(btn);
        };
        reader.readAsDataURL(file);
        
        listaAdjuntos.appendChild(li);
    });

    if (archivosGaleria.length > 0) {
        // antes: txtMulti.innerHTML
        txtMulti.textContent = `${archivosGaleria.length} imágenes nuevas listas`;
        txtMulti.style.color = "var(--acento)";
    } else {
        // antes: txtMulti.innerText
        txtMulti.textContent = "Arrastra imágenes aquí o haz clic para añadir";
        txtMulti.style.color = "";
    }
};


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
    // 🌟 NUEVO: Si la caché está vacía (es decir, nunca se han cargado datos), los trae automáticamente
        if (!datosGlobalescache[tipo] || datosGlobalescache[tipo].length === 0) {
            window.cargarVistaPrevia(tipo);
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
            // ✅ antes: selectCondiciones.innerHTML = '<option value="">Todas</option>';
            selectCondiciones.textContent = ''; 
            const optDefault = document.createElement('option');
            optDefault.value = '';
            optDefault.textContent = 'Todas';
            selectCondiciones.appendChild(optDefault);

            data.condiciones.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.nombre; 
                selectCondiciones.appendChild(opt);
            });
        }
        
        // 2. Cargar estados de discipulado maestro
        if (selectEstadosDisc && data.estados_discipulo && Array.isArray(data.estados_discipulo)) {
            // antes: selectEstadosDisc.innerHTML = '<option value="">Todos</option>';
            selectEstadosDisc.textContent = '';
            const optDefault = document.createElement('option');
            optDefault.value = '';
            optDefault.textContent = 'Todos';
            selectEstadosDisc.appendChild(optDefault);

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
        datosGlobalescache[tipo] = datos || [];
        
        if(paginacion[tipo]) {
            paginacion[tipo].paginaActual = 1;
        }

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
 * SISTEMA DE LIMPIEZA: Resetea el formulario, limpia autocompletes e inputs ocultos,
 * y recarga automáticamente todos los registros manteniendo la paginación activa.
 */
window.limpiarFiltros = function(tipo) {
    const form = document.getElementById(`form-${tipo}`);
    
    if (form) {
        // 1. Restablece todos los selectores (<select>) a su primera opción ("Todos" / "Todas")
        form.reset(); 
        
        // 2. Limpieza manual para los inputs de Autocomplete y sus IDs ocultos (Hidden)
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
        }
        
        // 3. LA CLAVE: Tras dejar el formulario limpio, llamamos a cargarVistaPrevia.
        // Al viajar los parámetros vacíos al backend, este traerá la totalidad de registros
        // y el renderizador pintará la página 1 de manera inmediata.
        window.cargarVistaPrevia(tipo);
    }
};


/**
 * Pinta únicamente los registros permitidos por el límite actual de paginación
 */
window.renderizarBloqueTabla = function(tipo) {
    const tbody = document.querySelector(`#tabla-${tipo} tbody`);
    const datos = datosGlobalescache[tipo] || [];
    tbody.innerHTML = ''; 

    const numColumnas = tipo === 'miembros' ? 7 : (tipo === 'visitas' ? 5 : (tipo === 'cumpleanos' ? 4 : 3));

    if (datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${numColumnas}" style="text-align:center; padding:15px; color:#888;">No se encontraron registros para los filtros seleccionados</td></tr>`;
        window.removerControlesPaginacion(tipo);
        return;
    }

    // CÁLCULO DE PAGINACIÓN COMPACTA
    const config = paginacion[tipo];
    const totalRegistros = datos.length;
    const totalPaginas = Math.ceil(totalRegistros / config.porPagina);
    
    // Si por alguna razón la página actual excede el total, la nivelamos
    if (config.paginaActual > totalPaginas) config.paginaActual = totalPaginas;
    if (config.paginaActual < 1) config.paginaActual = 1;

    // Corte de datos en memoria (Ej: página 1 saca del 0 al 2)
    const inicio = (config.paginaActual - 1) * config.porPagina;
    const fin = inicio + config.porPagina;
    const datosVisibles = datos.slice(inicio, fin);

    // Inyección de filas en la tabla (Tu lógica exacta intacta)
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
            let nombreMiembro = fila.integrante_nombre || 'Sin Nombre';
            let nombreGrupo = fila.grupo_nombre || 'Sin Grupo';
            let nombreLider = fila.discipulador_nombre || 'Sin asignar';
            let estadoTexto = fila.estado_alumno_texto || 'Sin estado';
            let bloqueGrupoHtml = `<strong>${nombreGrupo}</strong><br><small style="color: #64748b;">Líder: ${nombreLider}</small>`;
            columnas = [bloqueGrupoHtml, nombreMiembro, estadoTexto];
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

    // Pintamos los nuevos controles inteligentes debajo de la tabla
    window.renderizarControlesPaginacion(tipo, config.paginaActual, totalPaginas, totalRegistros);
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
/**
 * Genera la botonera estilizada de paginación clásica
 */
window.renderizarControlesPaginacion = function(tipo, paginaActual, totalPaginas, totalRegistros) {
    window.removerControlesPaginacion(tipo);
    const tabla = document.getElementById(`tabla-${tipo}`);
    if (!tabla) return;

    // Contenedor principal de la botonera
    const container = document.createElement('div');
    container.id = `paginacion-control-${tipo}`;
    container.style = 'display: flex; justify-content: center; align-items: center; gap: 15px; margin: 20px auto; font-family: inherit;';

    // Botón Anterior
    const btnAnt = document.createElement('button');
    btnAnt.innerHTML = '<i class="fas fa-chevron-left"></i> Anterior';
    btnAnt.style = 'padding: 8px 14px; background: #e2e8f0; color: #334155; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 13px; transition: 0.2s;';
    if (paginaActual === 1) {
        btnAnt.style.opacity = '0.5';
        btnAnt.style.cursor = 'not-allowed';
    } else {
        btnAnt.onclick = function() {
            paginacion[tipo].paginaActual--;
            window.renderizarBloqueTabla(tipo);
        };
    }

    // Texto de ubicación de páginas e indicadores numéricos
    const textoInfo = document.createElement('span');
    textoInfo.style = 'font-size: 14px; color: #475569; font-weight: 500;';
    textoInfo.textContent = `Página ${paginaActual} de ${totalPaginas} (${totalRegistros} registros totales)`;

    // Botón Siguiente
    const btnSig = document.createElement('button');
    btnSig.innerHTML = 'Siguiente <i class="fas fa-chevron-right"></i>';
    btnSig.style = 'padding: 8px 14px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 13px; transition: 0.2s;';
    if (paginaActual === totalPaginas) {
        btnSig.style.opacity = '0.5';
        btnSig.style.cursor = 'not-allowed';
    } else {
        btnSig.onclick = function() {
            paginacion[tipo].paginaActual++;
            window.renderizarBloqueTabla(tipo);
        };
    }

    // Construcción del nodo
    container.appendChild(btnAnt);
    container.appendChild(textoInfo);
    container.appendChild(btnSig);

    // Lo inyectamos exactamente debajo de la tabla correspondiente
    tabla.parentNode.insertBefore(container, tabla.nextSibling);
};

window.removerControlesPaginacion = function(tipo) {
    const el = document.getElementById(`paginacion-control-${tipo}`);
    if (el) el.remove();
};
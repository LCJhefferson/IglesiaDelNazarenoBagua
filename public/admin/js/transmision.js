document.addEventListener('DOMContentLoaded', () => {
    const inputs = {
        id: document.getElementById('formId'),
        titulo: document.getElementById('formTitulo'),
        desc: document.getElementById('formDesc'),
        link: document.getElementById('formLink'),
        estado: document.getElementById('formEstado'),
        btnPrincipal: document.getElementById('btnAccionPrincipal')
    };

    const monitores = {
        titulo: document.getElementById('monitorTitulo'),
        desc: document.getElementById('monitorDesc'),
        video: document.getElementById('monitorVideo'),
        placeholder: document.getElementById('monitorPlaceholder')
    };

    // 1. SINCRONIZACIÓN EN TIEMPO REAL
    if (inputs.titulo) {
        inputs.titulo.addEventListener('input', (e) => {
            monitores.titulo.innerText = e.target.value.trim() || 'Título del Evento';
        });
    }

    if (inputs.desc) {
        inputs.desc.addEventListener('input', (e) => {
            monitores.desc.innerText = e.target.value.trim() || 'Descripción de la transmisión...';
        });
    }

    if (inputs.link) {
        inputs.link.addEventListener('input', (e) => {
            const url = e.target.value.trim();
            if (url) {
                actualizarMonitor(url);
                monitores.placeholder.style.display = 'none';
                monitores.video.style.display = 'block';
            } else {
                monitores.video.src = "";
                monitores.video.style.display = 'none';
                monitores.placeholder.style.display = 'flex';
            }
        });
    }

    // 2. LÓGICA DEL BOTÓN PRINCIPAL
    if (inputs.btnPrincipal) {
        inputs.btnPrincipal.addEventListener('click', () => {
            confirmarAccion();
        });
    }
});

// --- FUNCIONES DE APOYO ---

function actualizarMonitor(url) {
    const monitor = document.getElementById('monitorVideo');
    if (!monitor) return;

    let videoId = '';
    if (url.includes('shorts/')) videoId = url.split('shorts/')[1].split('?')[0];
    else if (url.includes('v=')) videoId = url.split('v=')[1].split('&')[0];
    else if (url.includes('youtu.be/')) videoId = url.split('youtu.be/')[1].split('?')[0];
    else if (url.includes('live/')) videoId = url.split('live/')[1].split('?')[0];
    else if (url.includes('embed/')) videoId = url.split('embed/')[1].split('?')[0];

    if (videoId) {
        monitor.src = "https://www.youtube.com/embed/" + videoId + "?autoplay=1";
        monitor.style.display = 'block';
        document.getElementById('monitorPlaceholder').style.display = 'none';
    }
}

function prepararNueva() {
    document.getElementById('formId').value = "";
    document.getElementById('formTitulo').value = "";
    document.getElementById('formDesc').value = "";
    document.getElementById('formLink').value = "";
    
    const selectEstado = document.getElementById('formEstado');
    selectEstado.value = "1"; // Por defecto En Vivo
    selectEstado.disabled = false;
    
    document.getElementById('formActionTitle').innerText = "Nueva Transmisión";
    document.getElementById('btnAccionPrincipal').innerHTML = '<i class="fa-solid fa-play"></i> Iniciar y Notificar';
    
    document.getElementById('monitorTitulo').innerText = "Título del Evento";
    document.getElementById('monitorDesc').innerText = "Descripción de la transmisión...";
    document.getElementById('monitorVideo').src = "";
    document.getElementById('monitorVideo').style.display = 'none';
    document.getElementById('monitorPlaceholder').style.display = 'flex';
}

function cargarParaEditar(data) {
    document.getElementById('formId').value = data.id;
    document.getElementById('formTitulo').value = data.titulo;
    document.getElementById('formDesc').value = data.descripcion;
    document.getElementById('formLink').value = data.link_video;
    
    const selectEstado = document.getElementById('formEstado');
    selectEstado.value = data.estado_id;

    if (data.estado_id == 2) {
        selectEstado.disabled = true;
        document.getElementById('formActionTitle').innerText = "Editando Transmisión Finalizada";
    } else {
        selectEstado.disabled = false;
        document.getElementById('formActionTitle').innerText = "Control de Vivo";
    }

    document.getElementById('monitorTitulo').innerText = data.titulo;
    document.getElementById('monitorDesc').innerText = data.descripcion;
    actualizarMonitor(data.link_video);
    
    document.getElementById('btnAccionPrincipal').innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function confirmarAccion() {
    const id = document.getElementById('formId').value;
    const estado = document.getElementById('formEstado').value;
    const esNuevo = id === "";

    // Siempre habilitar el select antes de validar/enviar para que viaje el dato en el POST
    document.getElementById('formEstado').disabled = false;

    if (esNuevo && estado == "1") {
        mostrarModal("Se finalizará automáticamente cualquier transmisión anterior y se notificará el nuevo inicio. ¿Deseas continuar?");
    } else {
        ejecutarEnvio();
    }
}

function mostrarModal(mensaje) {
    const modal = document.getElementById('modalConfirmar');
    const texto = document.getElementById('textoModal');
    if (modal && texto) {
        texto.innerText = mensaje;
        const btnConfirmar = document.getElementById("btnConfirmarModal");
        btnConfirmar.onclick = ejecutarEnvio; 
        modal.style.display = 'flex';
    }
}

function cerrarModal() {
    document.getElementById('modalConfirmar').style.display = 'none';
}

function ejecutarEnvio() {
    const formulario = document.getElementById('formTransmision');
    if(formulario.checkValidity()) {
        formulario.submit();
    } else {
        formulario.reportValidity();
        cerrarModal();
    }
}

function abrirModalFinalizar(id, titulo) {
    const modal = document.getElementById('modalConfirmar');
    const texto = document.getElementById('textoModal');
    
    texto.innerText = "¿Deseas finalizar la transmisión: '" + titulo + "'? Esto cerrará la conexión para todos los espectadores.";
    
    const btnConfirmar = document.getElementById("btnConfirmarModal");
    btnConfirmar.onclick = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ''; 

        const csrfInputExistente = document.querySelector('input[name="csrf_token"]');
        const tokenValor = csrfInputExistente ? csrfInputExistente.value : '';

        const params = {
            'csrf_token': tokenValor, 
            'id_transmision': id,
            'estado_id': '2',
            'titulo': titulo,
            'mensaje_pusher': 'Fin de la transmisión'
        };

        for (const key in params) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = params[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit(); 
    };
    
    modal.style.display = 'flex';
}
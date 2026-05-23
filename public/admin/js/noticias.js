/* ─────────────────────────────────────────
   VARIABLES GLOBALES
───────────────────────────────────────── */
let archivosGaleria = []; // Almacena temporalmente los archivos de la galería

/* ─────────────────────────────────────────
   INICIALIZACIÓN
───────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", () => {
    const modal         = document.getElementById("modal");
    const form          = document.getElementById("form-noticia");
    const listaAdjuntos = document.getElementById("lista-imagenes");
    const inputPortada  = document.getElementById("imagen");
    const txtPortada    = document.getElementById("txt-imagen");
    const inputMulti    = document.getElementById("imagenes");
    const txtMulti      = document.getElementById("txt-multi");

    const totalReal = parseInt(document.querySelector(".badge-total-real")?.innerText || "0");
    window.animarContador("badge-total", totalReal);

    if (localStorage.getItem("tema-noticias") === "dark") {
        document.body.classList.add("dark-mode");
        document.getElementById("icono-tema").className = "fa-solid fa-sun";
    }

    setTimeout(() => {
        const skeleton = document.getElementById("skeleton-grid");
        const grid     = document.getElementById("contenedor-noticias");
        if (skeleton) skeleton.style.display = "none";
        if (grid)     grid.style.display     = "grid";
    }, 600);

    /* ── MODAL ── */
    window.abrirModal = function(editar = false) {
        modal.classList.add("active");
        modal.style.display = "flex";

        if (!editar) {
            form.reset();
            archivosGaleria = []; // Limpiamos el array
            window.sincronizarInputGaleria(); // Limpiamos el input real
            
            document.getElementById("id_noticia").value    = "";
            document.getElementById("imagen_actual").value = "";
            txtPortada.innerText   = "Arrastra una imagen aquí o haz clic para subir";
            txtPortada.style.color = "";
            txtMulti.innerText     = "Arrastra imágenes aquí o haz clic para añadir";
            txtMulti.style.color   = "";
            listaAdjuntos.innerHTML = "";
            document.getElementById("char-resumen").innerText = "0 / 150";
            document.getElementById("char-resumen").className = "char-contador";
            document.getElementById("modal-titulo").innerHTML = '<i class="fa-solid fa-plus"></i> Nueva Noticia';
            document.getElementById("label-upload").style.display = "flex";
            document.getElementById("contenedor-portada-edit").style.display = "none";
            const btn = document.getElementById("btn-submit-noticia");
            if (btn) btn.innerHTML = '<i class="fa-solid fa-save"></i> <span>Guardar Publicación</span>';
        }
        
        if (!window.quillInstance) {
            window.quillInstance = new Quill('#quill-editor', { theme: 'snow' });
            window.quillInstance.on('text-change', function() {
                document.getElementById('f-contenido').value = window.quillInstance.root.innerHTML;
            });
        }

        if (!editar) {
            window.quillInstance.root.innerHTML = '';
        }
    };

   window.cerrarModal = function() {
        modal.classList.remove("active");
        modal.style.display = "none";
        
        // ¡NUEVO!: Limpiamos las imágenes en cola si el usuario cancela
        archivosGaleria = []; 
        window.sincronizarInputGaleria();
    };

    modal.addEventListener("click", (e) => {
        if (e.target === modal) window.cerrarModal();
    });

    /* ── PORTADA ── */
    if(inputPortada) {
        inputPortada.addEventListener("change", function() {
            if (this.files && this.files[0]) {
                window.procesarImagenPortada(this.files[0]);
            }
        });
    }

    /* ── GALERÍA (SELECCIÓN POR CLIC) ── */
    if(inputMulti) {
        inputMulti.addEventListener("change", function() {
            const nuevosArchivos = Array.from(this.files);
            archivosGaleria = [...archivosGaleria, ...nuevosArchivos];
            window.renderizarListaGaleria();
            window.sincronizarInputGaleria();
        });
    }

    /* Preview en tiempo real */
    const fTitulo = document.getElementById("f-titulo");
    if(fTitulo) {
        fTitulo.addEventListener("input", (e) => {
            document.getElementById("preview-titulo").innerText = e.target.value || "Título de la noticia";
        });
    }

    const fResumen = document.getElementById("f-resumen");
    if(fResumen) {
        fResumen.addEventListener("input", (e) => {
            document.getElementById("preview-resumen").innerText = e.target.value || "Resumen ejecutivo...";
        });
    }

    /* Modal confirmar: cerrar al clic fuera */
    const mc = document.getElementById("modal-confirmar");
    if (mc) mc.addEventListener("click", (e) => { if (e.target === mc) window.cerrarConfirmar(); });
});


/* ─────────────────────────────────────────
   LÓGICA DE GALERÍA DE IMÁGENES
───────────────────────────────────────── */

window.renderizarListaGaleria = function() {
    const listaAdjuntos = document.getElementById("lista-imagenes");
    const txtMulti      = document.getElementById("txt-multi");
    
    // Limpiamos solo los elementos temporales (nuevos)
    document.querySelectorAll(".item-nuevo-temp").forEach(el => el.remove());

    archivosGaleria.forEach((file, index) => {
        const li = document.createElement("li");
        
        // Misma estructura pero con borde punteado azul (indicador de que es nuevo)
        li.className = "item-nuevo-temp";
        li.style.cssText = "display: inline-flex; flex-direction: column; align-items: center; width: 110px; margin-right: 15px; margin-bottom: 15px; position: relative; border: 1px dashed #3b82f6; padding: 5px; border-radius: 8px; background: #eff6ff; vertical-align: top;";

        // Usamos FileReader para ver la miniatura antes de subirla
        const reader = new FileReader();
        reader.onload = (e) => {
            li.innerHTML = `
                <img src="${e.target.result}" style="width: 100%; height: 75px; object-fit: cover; border-radius: 4px; margin-bottom: 5px; border: 1px solid #bfdbfe;">
                <span title="${file.name}" style="font-size: 11px; color: #1e3a8a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: block; text-align: center;">
                    (Nuevo) ${file.name}
                </span>
                <button type="button" class="btn-eliminar-adjunto" onclick="quitarImagenNueva(${index})" style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
        };
        reader.readAsDataURL(file);
        
        listaAdjuntos.appendChild(li);
    });

    if (archivosGaleria.length > 0) {
        txtMulti.innerHTML   = `<i class="fa-solid fa-images"></i> ${archivosGaleria.length} imágenes nuevas listas`;
        txtMulti.style.color = "var(--acento)";
    } else {
        txtMulti.innerText   = "Arrastra imágenes aquí o haz clic para añadir";
        txtMulti.style.color = "";
    }
};

window.quitarImagenNueva = function(index) {
    archivosGaleria.splice(index, 1); // Quitamos del array
    window.renderizarListaGaleria();  // Refrescamos la vista
    window.sincronizarInputGaleria(); // Sincronizamos el input para PHP
};

// Función crítica para inyectar los archivos al input real del formulario
window.sincronizarInputGaleria = function() {
    const inputMulti = document.getElementById("imagenes");
    if (!inputMulti) return;

    const dt = new DataTransfer();
    archivosGaleria.forEach(file => dt.items.add(file));
    inputMulti.files = dt.files;
};


/* ─────────────────────────────────────────
   DRAG & DROP
───────────────────────────────────────── */
window.dragOver = function(e) {
    e.preventDefault();
    e.currentTarget.classList.add("drag-over");
};

window.dragLeave = function(e) {
    e.currentTarget.classList.remove("drag-over");
};

window.dropImagen = function(e) {
    e.preventDefault();
    e.currentTarget.classList.remove("drag-over");
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith("image/")) {
        window.procesarImagenPortada(file);
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById("imagen").files = dt.files;
    }
};

window.dropGaleria = function(e) {
    e.preventDefault();
    e.currentTarget.classList.remove("drag-over");
    
    const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith("image/"));
    
    if (files.length > 0) {
        archivosGaleria = [...archivosGaleria, ...files];
        window.renderizarListaGaleria();
        window.sincronizarInputGaleria(); // Vital para que PHP reciba lo que arrastras
    }
};

window.procesarImagenPortada = function(file) {
    const txtPortada = document.getElementById("txt-imagen");
    txtPortada.innerText   = "Seleccionada: " + file.name;
    txtPortada.style.color = "var(--verde)";
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById("preview-img").src = e.target.result;
    };
    reader.readAsDataURL(file);
};


/* ─────────────────────────────────────────
   MODAL CONFIRMAR ELIMINAR
───────────────────────────────────────── */
window.confirmarEliminar = function(id, titulo) {
    const modalConfirmar = document.getElementById("modal-confirmar");
    document.getElementById("confirmar-nombre").innerText = titulo;
    modalConfirmar.style.display = "flex";

    document.getElementById("btn-confirmar-ok").onclick = function() {
        window.location.href = `/IglesiaDelNazarenoBagua/public/index.php?vista=dashboard&seccion=noticias&eliminar=${id}`;
    };
};

window.cerrarConfirmar = function() {
    document.getElementById("modal-confirmar").style.display = "none";
};


/* ─────────────────────────────────────────
   TOAST NOTIFICATIONS
───────────────────────────────────────── */
window.mostrarToast = function(mensaje, tipo = "exito") {
    const iconos    = { exito: "fa-circle-check", error: "fa-circle-xmark", info: "fa-circle-info" };
    const container = document.getElementById("toast-container");
    if(!container) return;

    const toast     = document.createElement("div");
    toast.className = `toast ${tipo}`;
    toast.innerHTML = `<i class="fa-solid ${iconos[tipo]}"></i> ${mensaje}`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = "toastSalida 0.3s ease forwards";
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get("guardado")    === "1") window.mostrarToast("Noticia guardada correctamente", "exito");
    if (params.get("actualizado") === "1") window.mostrarToast("Noticia actualizada correctamente", "exito");
    if (params.get("eliminado")   === "1") window.mostrarToast("Noticia eliminada", "info");
});


/* ─────────────────────────────────────────
   CONTADOR DE CARACTERES
───────────────────────────────────────── */
window.contarCaracteres = function(inputId, contadorId, maximo) {
    const input = document.getElementById(inputId);
    if(!input) return;
    const texto    = input.value.length;
    const contador = document.getElementById(contadorId);
    contador.innerText = `${texto} / ${maximo}`;
    contador.className = "char-contador";
    if (texto >= maximo * 0.85) contador.className += " warning";
    if (texto >= maximo)        contador.className  = "char-contador danger";
};


/* ─────────────────────────────────────────
   CARD ACTIVA RESALTADA Y PREVIEW
───────────────────────────────────────── */
window.seleccionarCard = function(cardEl, n) {
    document.querySelectorAll(".noticia-card").forEach(c => c.classList.remove("activa"));
    cardEl.classList.add("activa");
    window.verNoticia(n);
};

window.verNoticia = function(n) {
    const rutaBase = "/IglesiaDelNazarenoBagua/";
    document.getElementById("preview-titulo").innerText  = n.titulo  || "Sin título";
    document.getElementById("preview-resumen").innerText = n.resumen || "Sin resumen";
    document.getElementById("preview-img").src = n.imagen_portada
        ? rutaBase + n.imagen_portada
        : "https://via.placeholder.com/400x200";

    const btnLeer = document.querySelector(".btn-leer");
    if (btnLeer) {
        btnLeer.onclick = function() {
            window.location.href = rutaBase + "public/index.php?vista=noticia&id=" + n.id + "&origen=admin";
        };
    }
};

window.animarContador = function(elementId, hasta) {
    const el = document.getElementById(elementId);
    if (!el) return;
    let actual = 0;
    const paso = Math.ceil(hasta / 20) || 1;
    const intervalo = setInterval(() => {
        actual += paso;
        if (actual >= hasta) {
            actual = hasta;
            clearInterval(intervalo);
        }
        el.innerText = actual + (hasta === 1 ? " noticia" : " noticias");
    }, 40);
};


/* ─────────────────────────────────────────
   EDITAR NOTICIA
───────────────────────────────────────── */
window.editarNoticia = function(n) {
    window.abrirModal(true);
    const rutaBase = "/IglesiaDelNazarenoBagua/";

    document.getElementById("id_noticia").value    = n.id;
    document.getElementById("imagen_actual").value = n.imagen_portada || "";
    document.getElementById("f-titulo").value      = n.titulo;
    document.getElementById("f-fecha").value       = n.fecha_creacion;
    document.getElementById("f-resumen").value     = n.resumen;
    document.getElementById('f-contenido').value   = n.contenido || '';
    
    if (window.quillInstance) {
         window.quillInstance.root.innerHTML = n.contenido || '';
    }
    
    document.getElementById("f-video").value       = n.video_link || "";

    window.contarCaracteres("f-resumen", "char-resumen", 150);

    const contenedorPreview = document.getElementById("contenedor-portada-edit");
    const labelUpload       = document.getElementById("label-upload");
    const imgPreviewModal   = document.getElementById("img-edit-preview");

    if (n.imagen_portada) {
        const url                       = rutaBase + n.imagen_portada;
        imgPreviewModal.src             = url;
        document.getElementById("preview-img").src = url;
        contenedorPreview.style.display = "block";
        labelUpload.style.display       = "none";
    } else {
        contenedorPreview.style.display = "none";
        labelUpload.style.display       = "flex";
    }

    const listaAdjuntos = document.getElementById("lista-imagenes");
    listaAdjuntos.innerHTML = "";
if (n.imagenes_adjuntas && n.imagenes_adjuntas.length > 0) {
    n.imagenes_adjuntas.forEach(img => {
        const li = document.createElement("li");
        
        // Estilos para que cada ítem parezca una "tarjeta" cuadrada
        li.style.cssText = "display: inline-flex; flex-direction: column; align-items: center; width: 110px; margin-right: 15px; margin-bottom: 15px; position: relative; border: 1px solid #e5e7eb; padding: 5px; border-radius: 8px; background: #f9fafb; vertical-align: top;";
        
        // Obtenemos el nombre del archivo
        let nombreArchivo = img.imagen.split('/').pop();

        li.innerHTML = `
            <img src="${rutaBase + img.imagen}" style="width: 100%; height: 75px; object-fit: cover; border-radius: 4px; margin-bottom: 5px; border: 1px solid #ddd;">
            
            <span title="${nombreArchivo}" style="font-size: 11px; color: #4b5563; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: block; text-align: center;">
                ${nombreArchivo}
            </span>

            <button type="button" class="btn-eliminar-adjunto" onclick="borrarImagenGaleria(${img.id}, this)" style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        listaAdjuntos.appendChild(li);
    });
}

    document.getElementById("modal-titulo").innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar Noticia';
    const btn = document.getElementById("btn-submit-noticia");
    if (btn) btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> <span>Actualizar Publicación</span>';
};


/* ─────────────────────────────────────────
   QUITAR IMAGEN ACTUAL (PORTADA)
───────────────────────────────────────── */
window.quitarImagenActual = function() {
    document.getElementById("contenedor-portada-edit").style.display = "none";
    document.getElementById("imagen_actual").value                   = "";
    document.getElementById("label-upload").style.display            = "flex";
    document.getElementById("preview-img").src = "https://via.placeholder.com/400x200";
};


/* ─────────────────────────────────────────
   FILTRAR NOTICIAS (BÚSQUEDA)
───────────────────────────────────────── */
window.filtrarNoticias = function() {
    const inputBusqueda = document.getElementById("buscar-noticia");
    if(!inputBusqueda) return;
    
    const texto    = inputBusqueda.value.toLowerCase().trim();
    const cards    = document.querySelectorAll(".noticia-card");
    const msgVacio = document.getElementById("msg-sin-busqueda");
    let   visibles = 0;

    cards.forEach(card => {
        const titulo   = card.dataset.titulo  || "";
        const resumen  = card.dataset.resumen || "";
        const coincide = titulo.includes(texto) || resumen.includes(texto);
        card.style.display = coincide ? "" : "none";
        if (coincide) visibles++;
    });

    if (msgVacio) {
        msgVacio.style.display = (visibles === 0 && texto !== "") ? "block" : "none";
    }
};



/* ─────────────────────────────────────────
   MARCAR IMAGEN PARA ELIMINAR (SIN MODAL)
───────────────────────────────────────── */
window.borrarImagenGaleria = function(idImagen, elementoBtn) {
    // 1. Buscamos el formulario al que pertenece esta imagen
    const form = elementoBtn.closest('form');

    // 2. Creamos un campo de texto oculto para guardar el ID de la imagen a borrar
    if (form) {
        const inputOculto = document.createElement('input');
        inputOculto.type = 'hidden';
        inputOculto.name = 'imagenes_a_eliminar[]'; // Los corchetes [] indican que será un array (lista) en PHP
        inputOculto.value = idImagen;
        form.appendChild(inputOculto);
    }

    // 3. Ocultamos y eliminamos el cuadrito de la imagen visualmente
    const itemImagen = elementoBtn.parentElement;
    if (itemImagen) {
        itemImagen.remove();
    }

    // 4. (Opcional) Un pequeño aviso de que se marcaron los cambios
    if (typeof window.mostrarToast === 'function') {
        window.mostrarToast("Marcada para eliminar (Guarda para aplicar)", "info");
    }
};
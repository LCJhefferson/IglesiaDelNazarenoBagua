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

    let archivosGaleria = [];

   
    const totalReal = parseInt(document.querySelector(".badge-total-real")?.innerText || "0");
    animarContador("badge-total", totalReal);

    
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
            archivosGaleria = [];
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
    };

    window.cerrarModal = function() {
        modal.classList.remove("active");
        modal.style.display = "none";
    };

    modal.addEventListener("click", (e) => {
        if (e.target === modal) cerrarModal();
    });

    /* ── PORTADA ── */
    inputPortada.addEventListener("change", function() {
        if (this.files && this.files[0]) {
            procesarImagenPortada(this.files[0]);
        }
    });

    /* ── GALERÍA ── */
    inputMulti.addEventListener("change", function() {
        archivosGaleria = [...archivosGaleria, ...Array.from(this.files)];
        renderizarListaGaleria();
    });

    window.renderizarListaGaleria = function() {
        document.querySelectorAll(".item-nuevo-temp").forEach(el => el.remove());
        archivosGaleria.forEach((file, index) => {
            const li = document.createElement("li");
            li.className = "item-galeria item-nuevo-temp";
            li.innerHTML = `
                <span class="nombre-archivo">
                    <i class="fa-solid fa-image"></i> ${file.name} (Nuevo)
                </span>
                <button type="button" class="btn-eliminar-adjunto" onclick="quitarImagenNueva(${index})">
                    <i class="fa-solid fa-xmark"></i>
                </button>`;
            listaAdjuntos.appendChild(li);
        });
        if (archivosGaleria.length > 0) {
            txtMulti.innerText   = listaAdjuntos.children.length + " imágenes en total";
            txtMulti.style.color = "var(--acento)";
        }
    };

    window.quitarImagenNueva = function(index) {
        archivosGaleria.splice(index, 1);
        renderizarListaGaleria();
    };

    /* Preview en tiempo real */
    document.getElementById("f-titulo").addEventListener("input", (e) => {
        document.getElementById("preview-titulo").innerText = e.target.value || "Título de la noticia";
    });

    document.getElementById("f-resumen").addEventListener("input", (e) => {
        document.getElementById("preview-resumen").innerText = e.target.value || "Resumen ejecutivo...";
    });

    /* Modal confirmar: cerrar al clic fuera */
    const mc = document.getElementById("modal-confirmar");
    if (mc) mc.addEventListener("click", (e) => { if (e.target === mc) cerrarConfirmar(); });
});

/* ─────────────────────────────────────────
   MEJORA 1: MODO OSCURO
───────────────────────────────────────── */
window.toggleTema = function() {
    const body  = document.body;
    const icono = document.getElementById("icono-tema");
    body.classList.toggle("dark-mode");
    const esDark = body.classList.contains("dark-mode");
    icono.className = esDark ? "fa-solid fa-sun" : "fa-solid fa-moon";
    localStorage.setItem("tema-noticias", esDark ? "dark" : "light");
};

/* ─────────────────────────────────────────
   MEJORA 2: MODAL CONFIRMAR ELIMINAR
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
    if (params.get("guardado")    === "1") mostrarToast("Noticia guardada correctamente", "exito");
    if (params.get("actualizado") === "1") mostrarToast("Noticia actualizada correctamente", "exito");
    if (params.get("eliminado")   === "1") mostrarToast("Noticia eliminada", "info");
});

/* ─────────────────────────────────────────
 CONTADOR DE CARACTERES
───────────────────────────────────────── */
window.contarCaracteres = function(inputId, contadorId, maximo) {
    const texto    = document.getElementById(inputId).value.length;
    const contador = document.getElementById(contadorId);
    contador.innerText = `${texto} / ${maximo}`;
    contador.className = "char-contador";
    if (texto >= maximo * 0.85) contador.className += " warning";
    if (texto >= maximo)        contador.className  = "char-contador danger";
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
        procesarImagenPortada(file);
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
        const txtMulti = document.getElementById("txt-multi");
        txtMulti.innerText   = files.length + " imágenes añadidas";
        txtMulti.style.color = "var(--acento)";
        files.forEach(file => {
            const li = document.createElement("li");
            li.className = "item-galeria item-nuevo-temp";
            li.innerHTML = `
                <span class="nombre-archivo">
                    <i class="fa-solid fa-image"></i> ${file.name} (Nuevo)
                </span>
                <button type="button" class="btn-eliminar-adjunto" onclick="this.closest('li').remove()">
                    <i class="fa-solid fa-xmark"></i>
                </button>`;
            document.getElementById("lista-imagenes").appendChild(li);
        });
    }
};

function procesarImagenPortada(file) {
    const txtPortada = document.getElementById("txt-imagen");
    txtPortada.innerText   = "Seleccionada: " + file.name;
    txtPortada.style.color = "var(--verde)";
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById("preview-img").src = e.target.result;
    };
    reader.readAsDataURL(file);
}

/* ─────────────────────────────────────────
    CARD ACTIVA RESALTADA
───────────────────────────────────────── */
window.seleccionarCard = function(cardEl, n) {
    document.querySelectorAll(".noticia-card").forEach(c => c.classList.remove("activa"));
    cardEl.classList.add("activa");
    verNoticia(n);
};

/* ─────────────────────────────────────────
    ANIMACIÓN CONTADOR
───────────────────────────────────────── */
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
   VER NOTICIA
───────────────────────────────────────── */
window.verNoticia = function(n) {
    const rutaBase = "/IglesiaDelNazarenoBagua/";
    console.log("Noticia seleccionada:", n); // ← agrega esta línea
    document.getElementById("preview-titulo").innerText  = n.titulo  || "Sin título";
    document.getElementById("preview-resumen").innerText = n.resumen || "Sin resumen";
    document.getElementById("preview-img").src = n.imagen_portada
        ? rutaBase + n.imagen_portada
        : "https://via.placeholder.com/400x200";

    const btnLeer = document.querySelector(".btn-leer");
    if (btnLeer) {
        btnLeer.onclick = function() {
            window.open(rutaBase + "public/index.php?vista=noticia&id=" + n.id + "&origen=admin", "_blank");
        };
    }
};



/* ─────────────────────────────────────────
   EDITAR NOTICIA
───────────────────────────────────────── */
window.editarNoticia = function(n) {
    abrirModal(true);
    const rutaBase = "/IglesiaDelNazarenoBagua/";

    document.getElementById("id_noticia").value    = n.id;
    document.getElementById("imagen_actual").value = n.imagen_portada || "";
    document.getElementById("f-titulo").value      = n.titulo;
    document.getElementById("f-fecha").value       = n.fecha_creacion;
    document.getElementById("f-resumen").value     = n.resumen;
    document.getElementById("f-contenido").value   = n.contenido;
    document.getElementById("f-video").value       = n.video_link || "";

    contarCaracteres("f-resumen", "char-resumen", 150);

    const contenedorPreview = document.getElementById("contenedor-portada-edit");
    const labelUpload       = document.getElementById("label-upload");
    const imgPreviewModal   = document.getElementById("img-edit-preview");

    if (n.imagen_portada) {
        const url               = rutaBase + n.imagen_portada;
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
            li.className = "item-galeria";
            li.innerHTML = `
                <span class="nombre-archivo">
                    <img src="${rutaBase + img.ruta}" style="width:30px; height:30px; object-fit:cover; border-radius:4px;">
                    ${img.ruta.split('/').pop()}
                </span>
                <button type="button" class="btn-eliminar-adjunto" onclick="borrarImagenGaleria(${img.id}, this)">
                    <i class="fa-solid fa-xmark"></i>
                </button>`;
            listaAdjuntos.appendChild(li);
        });
    }

    document.getElementById("modal-titulo").innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar Noticia';
    const btn = document.getElementById("btn-submit-noticia");
    if (btn) btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> <span>Actualizar Publicación</span>';
};

/* ─────────────────────────────────────────
   QUITAR IMAGEN ACTUAL
───────────────────────────────────────── */
window.quitarImagenActual = function() {
    document.getElementById("contenedor-portada-edit").style.display = "none";
    document.getElementById("imagen_actual").value                   = "";
    document.getElementById("label-upload").style.display            = "flex";
    document.getElementById("preview-img").src = "https://via.placeholder.com/400x200";
};

/* ─────────────────────────────────────────
   FILTRAR NOTICIAS
───────────────────────────────────────── */
window.filtrarNoticias = function() {
    const texto    = document.getElementById("buscar-noticia").value.toLowerCase().trim();
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
   BORRAR IMAGEN DE GALERÍA
───────────────────────────────────────── */
window.borrarImagenGaleria = function(idImagen, elementoBtn) {
    if (confirm("¿Estás seguro de eliminar esta imagen de la galería?")) {
        
        const url = `/IglesiaDelNazarenoBagua/public/index.php?vista=dashboard&seccion=noticias&eliminar_foto=${idImagen}`;
        fetch(url)
            .then(response => response.text())
            .then(text => {
                if (text.trim() === "ok") {
                    const item = elementoBtn.closest(".item-galeria");
                    if (item) item.remove();
                    mostrarToast("Imagen eliminada de la galería", "info");
                } else {
                    mostrarToast("Error al eliminar la imagen", "error");
                }
            })
            .catch(() => mostrarToast("No se pudo conectar con el servidor", "error"));
    }
};
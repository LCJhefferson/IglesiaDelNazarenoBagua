/* ───────── INICIALIZACIÓN DE VARIABLES ───────── */
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modal");
    const form = document.getElementById("form-noticia");
    const listaAdjuntos = document.getElementById("lista-imagenes");
    const inputPortada = document.getElementById("imagen");
    const txtPortada = document.getElementById("txt-imagen");
    const inputMulti = document.getElementById("imagenes");
    const txtMulti = document.getElementById("txt-multi");

    let archivosGaleria = []; // Almacena archivos NUEVOS (File objects)

    /* ───────── FUNCIONES DEL MODAL ───────── */
    window.abrirModal = function(editar = false) {
        modal.classList.add("active");
        const btnGuardar = document.getElementById("btn-submit-noticia");

        if (!editar) {
            form.reset();
            archivosGaleria = []; 
            document.getElementById("id_noticia").value = "";
            document.getElementById("imagen_actual").value = "";
            
            // Reset visual
            txtPortada.innerText = "Hacer clic para subir la imagen principal";
            txtPortada.style.color = "var(--suave)";
            txtMulti.innerText = "Hacer clic para añadir varias fotos";
            listaAdjuntos.innerHTML = "";
            
            document.getElementById("modal-titulo").innerHTML = '<i class="fa-solid fa-plus"></i> Nueva Noticia';
            document.getElementById("preview-img").src = "https://via.placeholder.com/400x200";
            
            if(btnGuardar) {
                btnGuardar.innerHTML = '<i class="fa-solid fa-save"></i> Guardar Publicación';
                btnGuardar.style.backgroundColor = ""; 
            }

            document.getElementById("label-upload").style.display = "flex";
            if(document.getElementById("contenedor-portada-edit")) {
                document.getElementById("contenedor-portada-edit").style.display = "none";
            }
        }
    };

    window.cerrarModal = function() {
        modal.classList.remove("active");
    };

    modal.addEventListener("click", (e) => {
        if (e.target === modal) cerrarModal();
    });

    /* ───────── GESTIÓN DE PORTADA ───────── */
    inputPortada.addEventListener("change", function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            txtPortada.innerText = "Seleccionada: " + file.name;
            txtPortada.style.color = "var(--verde)";

            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById("preview-img").src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    /* ───────── GESTIÓN DE GALERÍA (Nuevas imágenes) ───────── */
    inputMulti.addEventListener("change", function() {
        // Importante: Convertimos a Array para que no se pierdan al seleccionar más después
        const seleccionados = Array.from(this.files);
        archivosGaleria = [...archivosGaleria, ...seleccionados];
        renderizarListaGaleria();
    });

    window.renderizarListaGaleria = function() {
        // Esta función NO borra las imágenes que ya están en el servidor, 
        // solo maneja la previsualización de las NUEVAS antes de subir.
        const itemsNuevos = document.querySelectorAll(".item-nuevo-temp");
        itemsNuevos.forEach(el => el.remove());

        if (archivosGaleria.length > 0) {
            archivosGaleria.forEach((file, index) => {
                const li = document.createElement("li");
                li.className = "item-galeria item-nuevo-temp";
                li.innerHTML = `
                    <span class="nombre-archivo"><i class="fa-solid fa-image"></i> ${file.name} (Nuevo)</span>
                    <button type="button" class="btn-eliminar-adjunto" onclick="quitarImagenNueva(${index})">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;
                listaAdjuntos.appendChild(li);
            });
            actualizarContadorGaleria();
        }
    };

    window.quitarImagenNueva = function(index) {
        archivosGaleria.splice(index, 1);
        renderizarListaGaleria();
    };

    function actualizarContadorGaleria() {
        const total = listaAdjuntos.children.length;
        txtMulti.innerText = total + " imágenes en total";
        txtMulti.style.color = "var(--acento)";
    }

    /* ───────── PREVIEW EN TIEMPO REAL ───────── */
    document.getElementById("f-titulo").addEventListener("input", (e) => {
        document.getElementById("preview-titulo").innerText = e.target.value || "Título de la noticia";
    });

    document.getElementById("f-resumen").addEventListener("input", (e) => {
        document.getElementById("preview-resumen").innerText = e.target.value || "Resumen ejecutivo...";
    });
});

/* ───────── EDITAR NOTICIA ───────── */
window.editarNoticia = function(n) {
    abrirModal(true);
    const rutaBase = "/IglesiaDelNazarenoBagua/";

    // 1. Campos básicos
    document.getElementById("id_noticia").value = n.id;
    document.getElementById("imagen_actual").value = n.imagen_portada || "";
    document.getElementById("f-titulo").value = n.titulo;
    document.getElementById("f-fecha").value = n.fecha_creacion; 
    document.getElementById("f-resumen").value = n.resumen;
    document.getElementById("f-contenido").value = n.contenido;
    document.getElementById("f-video").value = n.video_link;

    // 2. Portada
    const contenedorPreview = document.getElementById("contenedor-portada-edit");
    const labelUpload = document.getElementById("label-upload");
    const imgPreviewModal = document.getElementById("img-edit-preview");

    if (n.imagen_portada) {
        const urlImagen = rutaBase + n.imagen_portada;
        document.getElementById("preview-img").src = urlImagen;
        contenedorPreview.style.display = "block";
        imgPreviewModal.src = urlImagen;
        labelUpload.style.display = "none"; 
    }

        // 3. Galería de imágenes existentes
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
                    </button>
                `;
                listaAdjuntos.appendChild(li);
            });
        }

    // 4. Cambios visuales del botón
    document.getElementById("modal-titulo").innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar Noticia';
    const btnGuardar = document.getElementById("btn-submit-noticia");
        if (btnGuardar) {
            // Forzamos que se vea y cambiamos el contenido
            btnGuardar.style.display = "inline-flex"; 
            btnGuardar.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> <span>Actualizar Publicación</span>';
            btnGuardar.classList.add("modo-edicion"); // Para darle un color distinto si quieres
        }
};

/* ───────── BORRAR IMAGEN DEL SERVIDOR ───────── */
window.borrarImagenServidor = function(ruta, btn) {
    if(confirm("¿Deseas eliminar esta imagen de la galería permanentemente?")) {
        // Aquí llamarías a un fetch o AJAX a un archivo PHP que haga el DELETE
        // Por ahora, solo lo removemos visualmente
        btn.parentElement.remove();
        console.log("Solicitar borrar: " + ruta);
        // Actualizar contador
        const txtMulti = document.getElementById("txt-multi");
        const total = document.getElementById("lista-imagenes").children.length;
        txtMulti.innerText = total + " imágenes en galería";
    }
};

window.quitarImagenActual = function() {
    document.getElementById("contenedor-portada-edit").style.display = "none";
    document.getElementById("imagen_actual").value = ""; 
    document.getElementById("label-upload").style.display = "flex";
    document.getElementById("preview-img").src = "https://via.placeholder.com/400x200";
};

window.verNoticia = function(n) {
    document.getElementById("preview-titulo").innerText = n.titulo;
    document.getElementById("preview-resumen").innerText = n.resumen;
    const rutaBase = "/IglesiaDelNazarenoBagua/";
    const foto = n.imagen_portada ? rutaBase + n.imagen_portada : "https://via.placeholder.com/400x200";
    document.getElementById("preview-img").src = foto;
};

window.filtrarNoticias = function() {
    const textoBusqueda = document.getElementById("buscar-noticia").value.toLowerCase().trim();
    const filas = document.querySelectorAll(".tabla-box tbody tr");

    filas.forEach(fila => {
        // Verificamos que la fila no sea la de "No hay noticias" (que tiene un colspan)
        if (fila.cells.length > 1) {
            const titulo = fila.cells[0].textContent.toLowerCase();
            const resumen = fila.cells[1].textContent.toLowerCase();

            if (titulo.includes(textoBusqueda) || resumen.includes(textoBusqueda)) {
                fila.style.display = ""; 
            } else {
                fila.style.display = "none"; 
            }
        }
    });
};

window.borrarImagenGaleria = function(idImagen, elementoBtn) {
    if (confirm("¿Estás seguro de eliminar esta imagen de la galería?")) {
        // Usamos la ruta completa al dashboard que procesa la petición
        const url = `/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=noticias&eliminar_foto=${idImagen}`;
        
        fetch(url)
            .then(response => {
                if (response.ok) {
                    // Si el servidor responde bien, eliminamos el elemento visualmente
                    // Buscamos el contenedor .item-galeria más cercano al botón
                    const item = elementoBtn.closest('.item-galeria');
                    if (item) {
                        item.remove();
                        // Actualizamos el contador visual si existe
                        const txtMulti = document.getElementById("txt-multi");
                        const total = document.getElementById("lista-imagenes").children.length;
                        if(txtMulti) txtMulti.innerText = total + " imágenes en total";
                    }
                } else {
                    alert("Error al intentar eliminar la imagen del servidor.");
                }
            })
            .catch(error => {
                console.error("Error en la petición:", error);
                alert("No se pudo conectar con el servidor.");
            });
    }
};
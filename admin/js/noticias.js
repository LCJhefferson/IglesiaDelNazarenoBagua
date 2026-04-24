/* ───────── INICIALIZACIÓN DE VARIABLES ───────── */
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modal");
    const form = document.getElementById("form-noticia");
    const listaAdjuntos = document.getElementById("lista-imagenes");
    const inputPortada = document.getElementById("imagen");
    const txtPortada = document.getElementById("txt-imagen");
    const inputMulti = document.getElementById("imagenes");
    const txtMulti = document.getElementById("txt-multi");

    // Array global temporal para la galería
    let archivosGaleria = [];

    /* ───────── FUNCIONES DEL MODAL ───────── */
    window.abrirModal = function(editar = false) {
        modal.classList.add("active");
        if (!editar) {
            form.reset();
            archivosGaleria = []; // Limpiar array de fotos
            document.getElementById("id_noticia").value = "";
            document.getElementById("imagen_actual").value = "";
            
            // Reset visual
            txtPortada.innerText = "Hacer clic para subir la imagen principal";
            txtPortada.style.color = "var(--suave)";
            txtMulti.innerText = "Hacer clic para añadir varias fotos a la galería";
            listaAdjuntos.innerHTML = "";
            
            document.getElementById("modal-titulo").innerHTML = '<i class="fa-solid fa-plus"></i> Nueva Noticia';
            document.getElementById("preview-img").src = "https://via.placeholder.com/400x200";
        }
    };

    window.cerrarModal = function() {
        modal.classList.remove("active");
    };

    // Cerrar al hacer clic fuera del recuadro blanco
    modal.addEventListener("click", (e) => {
        if (e.target === modal) cerrarModal();
    });

    /* ───────── GESTIÓN DE PORTADA ───────── */
    inputPortada.addEventListener("change", function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            txtPortada.innerText = "Seleccionada: " + file.name;
            txtPortada.style.color = "var(--verde)";

            // Actualizar miniatura lateral
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById("preview-img").src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    /* ───────── GESTIÓN DE GALERÍA (CON BORRADO) ───────── */
    inputMulti.addEventListener("change", function() {
        // Combinar archivos nuevos con los que ya estaban (opcional) o reemplazar
        const nuevosArchivos = Array.from(this.files);
        archivosGaleria = [...archivosGaleria, ...nuevosArchivos];
        renderizarListaGaleria();
    });

    window.renderizarListaGaleria = function() {
        listaAdjuntos.innerHTML = "";
        
        if (archivosGaleria.length > 0) {
            txtMulti.innerText = archivosGaleria.length + " imágenes listas para la galería";
            txtMulti.style.color = "var(--acento)";

            archivosGaleria.forEach((file, index) => {
                const li = document.createElement("li");
                li.className = "item-galeria";
                li.innerHTML = `
                    <span class="nombre-archivo"><i class="fa-solid fa-image"></i> ${file.name}</span>
                    <button type="button" class="btn-eliminar-adjunto" onclick="quitarImagenGaleria(${index})">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;
                listaAdjuntos.appendChild(li);
            });
        } else {
            txtMulti.innerText = "Hacer clic para añadir varias fotos a la galería";
            txtMulti.style.color = "var(--suave)";
        }
    };

    window.quitarImagenGaleria = function(index) {
        archivosGaleria.splice(index, 1);
        renderizarListaGaleria();
        // Nota: Los inputs file son de solo lectura, pero al procesar 
        // podrías usar FormData para enviar solo 'archivosGaleria'.
    };

    /* ───────── PREVIEW EN TIEMPO REAL ───────── */
    document.getElementById("f-titulo").addEventListener("input", (e) => {
        document.getElementById("preview-titulo").innerText = e.target.value || "Título de la noticia";
    });

    document.getElementById("f-resumen").addEventListener("input", (e) => {
        document.getElementById("preview-resumen").innerText = e.target.value || "Resumen ejecutivo...";
    });

    /* ───────── FILTRADO DE TABLA ───────── */
    window.filtrarNoticias = function() {
        const texto = document.getElementById("buscar-noticia").value.toLowerCase();
        const filas = document.querySelectorAll("tbody tr");

        filas.forEach(fila => {
            const contenido = fila.innerText.toLowerCase();
            fila.style.display = contenido.includes(texto) ? "" : "none";
        });
    };
});

/* ───────── EDITAR NOTICIA ───────── */
window.editarNoticia = function(n) {
    abrirModal(true);
    document.getElementById("id_noticia").value = n.id;
    document.getElementById("imagen_actual").value = n.imagen;
    document.getElementById("f-titulo").value = n.titulo;
    document.getElementById("f-fecha").value = n.fecha;
    document.getElementById("f-resumen").value = n.resumen;
    document.getElementById("f-contenido").value = n.contenido;
    document.getElementById("f-video").value = n.video;

    // Cargar imagen en preview
    if (n.imagen) {
        document.getElementById("preview-img").src = n.imagen;
        document.getElementById("txt-imagen").innerText = "Imagen actual cargada (clic para cambiar)";
    }

    document.getElementById("modal-titulo").innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar Noticia';

    // Mostrar galería existente (visual)
    const listaAdjuntos = document.getElementById("lista-imagenes");
    listaAdjuntos.innerHTML = "";
    if (n.imagenes && n.imagenes.length > 0) {
        n.imagenes.forEach(img => {
            const li = document.createElement("li");
            li.className = "item-galeria";
            li.innerHTML = `<span><i class="fa-solid fa-file-image"></i> ${img.split('/').pop()}</span>`;
            listaAdjuntos.appendChild(li);
        });
    }
};

/* ───────── VISTA RÁPIDA (PANEL LATERAL) ───────── */
window.verNoticia = function(n) {
    document.getElementById("preview-titulo").innerText = n.titulo;
    document.getElementById("preview-resumen").innerText = n.resumen;
    document.getElementById("preview-img").src = n.imagen || "https://via.placeholder.com/400x200";
    
    // Scroll suave en móviles
    if(window.innerWidth < 1100) {
        document.querySelector(".preview").scrollIntoView({ behavior: 'smooth' });
    }
};
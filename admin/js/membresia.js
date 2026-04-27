// MODAL: Abrir para nuevo registro
function abrirModal(){
    const modal = document.getElementById("modal");
    if(modal) {
        modal.classList.add("active");
        modal.style.display = "flex"; // Asegura visibilidad si usas display en lugar de clases
        
        document.querySelector("form").reset(); // Limpia el formulario
        
        // Configurar para modo "Nuevo"
        document.getElementById("btnAgregar").style.display = "inline-block";
        document.getElementById("btnActualizar").style.display = "none";
        document.getElementById("tituloModal").innerHTML = '<i class="fa-solid fa-user-plus"></i> Nuevo Miembro';
        
        // Resetear campos ocultos o específicos si es necesario
        document.getElementsByName("id")[0].value = "";
    } else {
        console.error("No se encontró el elemento con id='modal'");
    }
}

// MODAL: Cerrar
function cerrarModal(){
    const modal = document.getElementById("modal");
    modal.classList.remove("active");
    modal.style.display = "none";
}

// EDITAR: Llena el modal con datos existentes
function editar(m) {
    abrirModal();

    // Cambiar título y botones
    document.getElementById("tituloModal").innerHTML = '<i class="fa-solid fa-pen"></i> Editar Miembro';
    document.getElementById("btnAgregar").style.display = "none";
    document.getElementById("btnActualizar").style.display = "inline-block";

    // Llenar los campos (Usando el objeto 'm')
    document.getElementsByName("id")[0].value = m.id;
    document.getElementsByName("nombres")[0].value = m.nombres;
    document.getElementsByName("apellidos")[0].value = m.apellidos;
    document.getElementsByName("telefono")[0].value = m.telefono;
    document.getElementsByName("direccion")[0].value = m.direccion;
    document.getElementsByName("fecha_nacimiento")[0].value = m.fecha_nacimiento;
    document.getElementsByName("cargo_id")[0].value = m.cargo_id;
    document.getElementsByName("condicion_id")[0].value = m.condicion_id;
    document.getElementsByName("latitud")[0].value = m.latitud || "";
    document.getElementsByName("longitud")[0].value = m.longitud || "";
    
    // Corregido: m.estado en lugar de datos.estado
    const inputEstado = document.getElementById("inputEstado");
    if(inputEstado) inputEstado.value = m.estado; 
}

// FILTRO INTEGRAL (Buscador + Roles + Condición + Estado)
function filtrarTabla() {
    const texto = document.getElementById("buscar").value.toLowerCase();
    const rol = document.getElementById("filtroRol").value.toLowerCase();
    const condicion = document.getElementById("filtroCondicion").value.toLowerCase();
    const estado = document.getElementById("filtroEstado").value;
    
    const filas = document.querySelectorAll("#tablaCuerpo tr");

    filas.forEach(f => {
        // Obtenemos los textos de las celdas específicas
        const nombreCompleto = f.cells[0].innerText.toLowerCase();
        const rolTexto = f.querySelector(".col-rol").innerText.toLowerCase();
        const condicionTexto = f.querySelector(".col-condicion").innerText.toLowerCase();
        const estadoID = f.getAttribute("data-estado"); // Obtenido del tr data-estado

        // Lógica de coincidencia
        const coincideNombre = nombreCompleto.includes(texto);
        const coincideRol = rol === "" || rolTexto.includes(rol);
        const coincideCondicion = condicion === "" || condicionTexto.includes(condicion);
        const coincideEstado = estado === "" || estadoID === estado;

        // Mostrar solo si cumple todos los filtros
        if (coincideNombre && coincideRol && coincideCondicion && coincideEstado) {
            f.style.display = "";
        } else {
            f.style.display = "none";
        }
    });
}

// CERRAR AL HACER CLIC FUERA DEL MODAL
window.onclick = function(event) {
    const modal = document.getElementById("modal");
    if (event.target == modal) {
        cerrarModal();
    }
}

// FUNCIÓN PARA MAPA
function abrirMapa(){
    // Aquí puedes disparar la lógica de Leaflet/Google Maps
    console.log("Abriendo mapa...");
}
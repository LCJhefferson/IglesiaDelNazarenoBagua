// MODAL
function abrirModal(){
    document.getElementById("modal").classList.add("active");

    // limpiar formulario
    document.querySelector("form").reset();

    // mostrar botón agregar
    document.getElementById("btnAgregar").style.display = "inline-block";

    // ocultar botón actualizar
    document.getElementById("btnActualizar").style.display = "none";
    // titulo del modal
    document.getElementById("tituloModal").innerHTML =
    '<i class="fa-solid fa-user-plus"></i> Nuevo Miembro';

   
}

function cerrarModal(){
    document.getElementById("modal").classList.remove("active");
}

// EDITAR
function editar(id,nombres,apellidos,telefono,direccion,fecha,cargo,condicion,lat,lon){
    abrirModal();

    document.getElementsByName("id")[0].value=id;
    document.getElementsByName("nombres")[0].value=nombres;
    document.getElementsByName("apellidos")[0].value=apellidos;
    document.getElementsByName("telefono")[0].value=telefono;
    document.getElementsByName("direccion")[0].value=direccion;
    document.getElementsByName("fecha_nacimiento")[0].value=fecha;
    document.getElementsByName("cargo_id")[0].value=cargo;
    document.getElementsByName("condicion_id")[0].value=condicion;
    document.getElementsByName("latitud")[0].value=lat;
    document.getElementsByName("longitud")[0].value=lon;

    // ocultar agregar
    document.getElementById("btnAgregar").style.display = "none";

    // mostrar actualizar
    document.getElementById("btnActualizar").style.display = "inline-block";
     // titulo
    document.getElementById("tituloModal").innerHTML =
    '<i class="fa-solid fa-pen"></i> Editar Miembro';
}

// BUSCADOR
function buscar(){
    let texto=document.getElementById("buscar").value.toLowerCase();
    let filas=document.querySelectorAll("tbody tr");

    filas.forEach(f=>{
        f.style.display = f.innerText.toLowerCase().includes(texto) ? "" : "none";
    });
}

// CERRAR MODAL AL HACER CLICK FUERA
document.getElementById("modal").addEventListener("click",function(e){
    if(e.target===this){
        cerrarModal();
    }
});


function abrirMapa(){
    alert("Aquí irá el mapa interactivo próximamente");
}
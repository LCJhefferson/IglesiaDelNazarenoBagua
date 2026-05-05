function mostrarAlerta(mensaje, tipo) {
    const alerta = document.getElementById("alerta");

    alerta.textContent = mensaje;
    alerta.className = "alerta show " + tipo;

    setTimeout(() => {
        alerta.classList.remove("show");
    }, 3000);
}
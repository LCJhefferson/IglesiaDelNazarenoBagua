// Opcional: Pausar en móviles al tocar la pantalla
document.addEventListener("DOMContentLoaded", () => {
    const wrapper = document.getElementById("tickerWrapper");
    if (!wrapper) return;

    wrapper.addEventListener("touchstart", () => {
        wrapper.classList.add("paused");
    }, { passive: true });

    wrapper.addEventListener("touchend", () => {
        wrapper.classList.remove("paused");
    }, { passive: true });
});
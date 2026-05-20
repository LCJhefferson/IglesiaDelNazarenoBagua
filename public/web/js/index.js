/* ========================= */
/* CARRUSEL HERO (slides)   */
/* ========================= */
const slides = document.querySelectorAll(".slide");

if (slides.length > 0) {
    let current = 0;
    setInterval(() => {
        slides[current].classList.remove("active");
        current = (current + 1) % slides.length;
        slides[current].classList.add("active");
    }, 5000);
}

/* ========================= */
/* CARRUSEL NOTICIAS        */
/* ========================= */
(function() {
    const track = document.getElementById("carrusel-track");
    if (!track) return;

    const items   = Array.from(track.querySelectorAll(".carrusel-item"));
    const total   = items.length;
    const dots    = document.querySelectorAll(".carrusel-dots .dot");
    const btnPrev = document.querySelector(".carrusel-btn.prev");
    const btnNext = document.querySelector(".carrusel-btn.next");
    const GAP     = 24;

    let actual   = 0;
    let autoPlay = null;

    /* Cuántas cards caben según el ancho */
    function calcPorVista() {
        const w = window.innerWidth;
        if (w <= 480)  return 1;
        if (w <= 768)  return 2;
        if (w <= 1024) return 3;
        if (w <= 1400) return 4;
        return 5;
    }

    /* Ajusta el ancho de cada item dinámicamente */
    function ajustarAnchosItems() {
        const porVista   = calcPorVista();
        const contenedor = track.parentElement.offsetWidth;
        const anchoItem  = (contenedor - GAP * (porVista - 1)) / porVista;
        items.forEach(item => {
            item.style.width = anchoItem + "px";
        });
    }

    /* Desplazamiento de un item */
    function getDesplazamiento() {
        if (items.length === 0) return 0;
        return items[0].offsetWidth + GAP;
    }

    /* Máximo índice posible */
    function maxSlide() {
        return Math.max(0, total - calcPorVista());
    }

    /* Ir a un slide específico */
    function irA(index) {
        actual = Math.max(0, Math.min(index, maxSlide()));
        track.style.transform = `translateX(-${actual * getDesplazamiento()}px)`;

        /* Actualizar dots */
        dots.forEach((d, i) => d.classList.toggle("activo", i === actual));

        /* Deshabilitar botones en los extremos */
        if (btnPrev) btnPrev.disabled = actual === 0;
        if (btnNext) btnNext.disabled = actual >= maxSlide();
    }

    /* Auto-play */
    function iniciarAutoPlay() {
        autoPlay = setInterval(() => {
            irA(actual >= maxSlide() ? 0 : actual + 1);
        }, 4000);
    }

    function detenerAutoPlay() {
        clearInterval(autoPlay);
    }

    /* Funciones globales para los botones y dots del HTML */
    window.moverCarrusel = function(direccion) {
        detenerAutoPlay();
        irA(actual + direccion);
        iniciarAutoPlay();
    };

    window.irASlide = function(index) {
        detenerAutoPlay();
        irA(index);
        iniciarAutoPlay();
    };

    /* Soporte táctil (swipe) */
    let touchStartX = 0;
    track.addEventListener("touchstart", (e) => {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });

    track.addEventListener("touchend", (e) => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            detenerAutoPlay();
            irA(diff > 0 ? actual + 1 : actual - 1);
            iniciarAutoPlay();
        }
    }, { passive: true });

    /* Recalcular al cambiar tamaño de ventana */
    window.addEventListener("resize", () => {
        ajustarAnchosItems();
        irA(actual);
    });

    /* Inicializar */
    ajustarAnchosItems();
    irA(0);
    iniciarAutoPlay();
})();
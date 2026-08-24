/* ==========================================================================
   COMPONENTE: HERO CARRUSEL (Lógica independiente)
   ========================================================================== */
(function() {
    document.addEventListener("DOMContentLoaded", function() {
        const heroSlides = document.querySelectorAll("#heroCarrusel .slide");
        const heroDots = document.querySelectorAll("#heroCarrusel .hero-dot");
        const heroContainer = document.getElementById("heroCarrusel");

        if (heroSlides.length === 0) return;

        let heroCurrent = 0;
        let heroTimer = null;

        function mostrarSlide(index) {
            heroSlides[heroCurrent].classList.remove("active");
            if (heroDots[heroCurrent]) heroDots[heroCurrent].classList.remove("active");

            heroCurrent = (index + heroSlides.length) % heroSlides.length;

            heroSlides[heroCurrent].classList.add("active");
            if (heroDots[heroCurrent]) heroDots[heroCurrent].classList.add("active");
        }

        function iniciarAutoPlay() {
            detenerAutoPlay();
            heroTimer = setInterval(() => {
                mostrarSlide(heroCurrent + 1);
            }, 5000);
        }

        function detenerAutoPlay() {
            if (heroTimer) clearInterval(heroTimer);
        }

        window.moverHeroSlide = function(direccion) {
            detenerAutoPlay();
            mostrarSlide(heroCurrent + direccion);
            iniciarAutoPlay();
        };

        window.irAHeroSlide = function(index) {
            detenerAutoPlay();
            mostrarSlide(index);
            iniciarAutoPlay();
        };

        // Soporte táctil para smartphones (Swipe izquierdo/derecho)
        if (heroContainer) {
            let touchStartX = 0;
            heroContainer.addEventListener("touchstart", (e) => {
                touchStartX = e.touches[0].clientX;
            }, { passive: true });

            heroContainer.addEventListener("touchend", (e) => {
                const diff = touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 40) {
                    moverHeroSlide(diff > 0 ? 1 : -1);
                }
            }, { passive: true });
        }

        iniciarAutoPlay();
    });
})();
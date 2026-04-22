document.addEventListener("DOMContentLoaded", function () {

    // Solo procesamos los .menu-title que NO son enlaces directos
    const titles = document.querySelectorAll('.menu-title:not(.direct-link)');

    titles.forEach(title => {
        title.addEventListener('click', function (e) {
            // Si es un <a> directo, no prevenimos nada (deja que navegue)
            if (this.tagName === 'A') return;

            e.preventDefault(); // ← solo si es div (acordeón)

            const currentSubmenu = this.nextElementSibling;

            // Cerrar otros submenús
            document.querySelectorAll('.submenu.active').forEach(sub => {
                if (sub !== currentSubmenu) {
                    sub.classList.remove('active');
                }
            });

            // Toggle el actual (si existe)
            if (currentSubmenu && currentSubmenu.classList.contains('submenu')) {
                currentSubmenu.classList.toggle('active');
            }
        });
    });

    // Dropdown usuario (sin cambios)
    const dropdown = document.querySelector('.dropdown');
    const btn = document.querySelector('.user-btn');

    if (btn) {
        btn.addEventListener('click', function () {
            dropdown.classList.toggle('active');
        });
    }

});



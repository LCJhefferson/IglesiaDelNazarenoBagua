document.addEventListener("DOMContentLoaded", function () {

    // --- LÓGICA DE SUBMENÚS (Discipulado y Visitas) ---
    const menuTitles = document.querySelectorAll('.menu-item > .menu-title:not(.direct-link)');

    menuTitles.forEach(title => {
        title.style.cursor = 'pointer';

        title.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const currentSubmenu = this.nextElementSibling;
            
            if (currentSubmenu && currentSubmenu.classList.contains('submenu')) {
                const isActive = currentSubmenu.classList.contains('active');

                // Cerramos otros abiertos para efecto acordeón
                document.querySelectorAll('.submenu.active').forEach(sub => {
                    if (sub !== currentSubmenu) {
                        sub.classList.remove('active');
                    }
                });

                // Toggle del actual
                currentSubmenu.classList.toggle('active');
                
                console.log("Submenú estado activo:", currentSubmenu.classList.contains('active'));
            }
        });
    });

    // --- LÓGICA DE BOTÓN DE USUARIO (Topbar) ---
    const dropdown = document.querySelector('.dropdown');
    const userBtn = document.querySelector('.user-btn');

    if (userBtn && dropdown) {
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('active');
        });
    }

    // Cerrar todo al hacer clic fuera
    window.addEventListener('click', function () {
        if (dropdown) dropdown.classList.remove('active');
    });

});
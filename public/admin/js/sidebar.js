document.addEventListener("DOMContentLoaded", function () {

    const btnToggle = document.getElementById('btnToggleSidebar');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleMobileMenu(e) {
        if (e) e.preventDefault();
        if (sidebar && overlay) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('active');
        }
    }

    if (btnToggle) {
        btnToggle.addEventListener('click', toggleMobileMenu);
    }

    if (overlay) {
        overlay.addEventListener('click', toggleMobileMenu);
    }

    // Cerrar el menú al presionar cualquier enlace en móvil
    const linksSidebar = document.querySelectorAll('.sidebar a');
    linksSidebar.forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 991 && sidebar && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('active');
            }
        });
    });

    // --- LÓGICA DE SUBMENÚS ---
    const menuTitles = document.querySelectorAll('.menu-item > .menu-title:not(.direct-link)');

    menuTitles.forEach(title => {
        title.style.cursor = 'pointer';

        title.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const currentSubmenu = this.nextElementSibling;
            
            if (currentSubmenu && currentSubmenu.classList.contains('submenu')) {
                document.querySelectorAll('.submenu.active').forEach(sub => {
                    if (sub !== currentSubmenu) {
                        sub.classList.remove('active');
                    }
                });

                currentSubmenu.classList.toggle('active');
            }
        });
    });

    // --- LÓGICA DE BOTÓN DE USUARIO ---
    const dropdown = document.querySelector('.dropdown');
    const userBtn = document.querySelector('.user-btn');

    if (userBtn && dropdown) {
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('active');
        });
    }

    window.addEventListener('click', function () {
        if (dropdown) dropdown.classList.remove('active');
    });

});
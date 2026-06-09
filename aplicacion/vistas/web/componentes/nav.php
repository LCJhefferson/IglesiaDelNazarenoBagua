<header class="header">
    <div class="logo">
        <a href="<?= URL ?>inicio">
            <img src="<?= URL ?>public/web/imagenes/SelloOficial.png" alt="Logo Iglesia">
        </a>
    </div>

    <div class="nav-container" id="navContainer">
        <nav class="nav">
            <a href="<?= URL ?>inicio">Inicio</a>
            <a href="<?= URL ?>historia">Historia</a>
            
            <div class="dropdown">
                <a href="#" class="dropdown-link" id="dropMinisterios">
                    Ministerios <i class="fa-solid fa-chevron-down"></i>
                </a>
                <div class="dropdown-menu">
                    <a href="<?= URL ?>ministerios/compasion">Compasión</a>
                    <a href="<?= URL ?>ministerios/comunicaciones">Comunicaciones</a>
                    <a href="<?= URL ?>ministerios/cdc">CDC</a>
                    <a href="<?= URL ?>ministerios/educacion-teologica">Educación Teológica</a>
                    <a href="<?= URL ?>ministerios/jni">JNI</a>
                    <a href="<?= URL ?>ministerios/mni">MNI</a>
                </div>
            </div>

            <a href="<?= URL ?>trasmisionPublica">Transmisión</a>
            <a href="<?= URL ?>recursos">Recursos</a>
        </nav>
        <a href="<?= URL ?>login" class="login">Ingresar</a>
    </div>

    <button class="menu-toggle" id="menuToggle" aria-label="Menú">
        <i class="fa-solid fa-bars"></i>
    </button>
</header>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const navContainer = document.getElementById('navContainer');
    const dropMinisterios = document.getElementById('dropMinisterios');

    // 1. Abrir/Cerrar menú hamburguesa (Móvil)
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation(); // Evita que el clic salte al documento
        navContainer.classList.toggle('active');
        
        const icon = this.querySelector('i');
        if (navContainer.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-xmark');
        } else {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
        }
    });

    // 2. Cerrar el menú al dar clic en cualquier parte fuera de la barra
    document.addEventListener('click', function(e) {
        if (!navContainer.contains(e.target) && !menuToggle.contains(e.target)) {
            navContainer.classList.remove('active');
            const icon = menuToggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        }
    });

    // 3. Abrir submenú de Ministerios en Móvil (Acordeón)
    dropMinisterios.addEventListener('click', (e) => {
        if (window.innerWidth <= 1024) {
            e.preventDefault(); // Evita que recargue la página
            dropMinisterios.parentElement.classList.toggle('active');
        }
    });
</script>
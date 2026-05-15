<header class="header">
    <div class="logo">
        <a href="<?= URL ?>inicio">
            <img src="<?= URL ?>public/web/imagenes/SelloOficial.png" alt="Logo Iglesia">
        </a>
    </div>

    <div class="nav-container" id="navContainer">
        <nav class="nav">
            <a href="<?= URL ?>inicio">Inicio</a>
            <a href="<?= URL ?>nosotros">Nosotros</a>
            <a href="<?= URL ?>ministerios">Ministerios</a>
            <a href="<?= URL ?>contacto">Contacto</a>
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
    document.getElementById('menuToggle').addEventListener('click', function(e) {
        e.stopPropagation();
        const navContainer = document.getElementById('navContainer');
        const icon = this.querySelector('i');
        
        navContainer.classList.toggle('active');
        
        if (navContainer.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-xmark');
        } else {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
        }
    });

    // Cierra el menú al dar clic en cualquier parte fuera de la barra
    document.addEventListener('click', function(e) {
        const navContainer = document.getElementById('navContainer');
        const menuToggle = document.getElementById('menuToggle');
        if (!navContainer.contains(e.target) && !menuToggle.contains(e.target)) {
            navContainer.classList.remove('active');
            const icon = menuToggle.querySelector('i');
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
        }
    });
</script>
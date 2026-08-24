<?php
// Detectamos la ruta actual para saber si estamos en la página de inicio
$uri = $_SERVER['REQUEST_URI'];
$esInicio = (strpos($uri, 'inicio') !== false || $uri === '/' || $uri === '/index.php');
?>

<header class="header <?= !$esInicio ? 'header-solido' : '' ?>" id="mainHeader">
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
                <a href="javascript:void(0);" class="dropdown-link" id="dropMinisterios">
                    Ministerios <i class="fa-solid fa-chevron-down"></i>
                </a>
                <div class="dropdown-menu">
                    <a href="<?= URL ?>ministerios/educacion-teologica">Educación Teológica</a>
                    <a href="<?= URL ?>ministerios/compasion">Compasión</a>
                    <a href="<?= URL ?>ministerios/dni">DNI</a>
                    <a href="<?= URL ?>ministerios/jni">JNI</a>
                    <a href="<?= URL ?>ministerios/mni">MNI</a>
                    
                </div>
            </div>

            <a href="<?= URL ?>trasmisionPublica">Transmisión</a>
            <a href="<?= URL ?>Todas_noticias">Noticias</a>
            <a href="<?= URL ?>recursos">Recursos</a>
        </nav>
        <a href="<?= URL ?>login" class="login">Ingresar</a>
    </div>

    <button class="menu-toggle" id="menuToggle" aria-label="Menú">
        <i class="fa-solid fa-bars"></i>
    </button>
</header>

<script>
    const header = document.getElementById('mainHeader');
    const menuToggle = document.getElementById('menuToggle');
    const navContainer = document.getElementById('navContainer');
    const dropMinisterios = document.getElementById('dropMinisterios');

    window.addEventListener('scroll', function() {
        if (window.scrollY > 700) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // 1. Abrir/Cerrar menú hamburguesa (Móvil)
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
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

    // 2. Cerrar menú al dar clic fuera
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

    // 3. Submenú
    dropMinisterios.addEventListener('click', (e) => {
        e.preventDefault();
        
        if (window.innerWidth <= 1024) {
            dropMinisterios.parentElement.classList.toggle('active');
        }
    });
</script>
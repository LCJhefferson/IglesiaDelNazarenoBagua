<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="sidebar-logo">
        <img src="public/imagenes/selloOficial.png" alt="Logo">
        <h3>Iglesia Del Nazareno</h3>
        <h3>Bagua</h3>
    </div>

    <div class="menu">

        <div class="menu-item">
            <a href="aplicacion/vistas/admin/dashboard.php" class="menu-title direct-link">Inicio</a>
        </div>

        <div class="menu-item">
            <a href="aplicacion/vistas/admin/dashboard.php?vista=usuarios_admin" class="menu-title direct-link">Usuarios</a>
        </div>

        <div class="menu-item">
            <a href="aplicacion/vistas/admin/dashboard.php?vista=recurso_admin" class="menu-title direct-link">Recursos</a>
        </div>

        <div class="menu-item">
            <a href="aplicacion/vistas/admin/dashboard.php?vista=membresia" class="menu-title direct-link">Membresia</a>
        </div>

        <div class="menu-item">
            <a href="aplicacion/vistas/admin/dashboard.php?vista=trasmision" class="menu-title direct-link">Trasmision</a>
        </div>

        <div class="menu-item">
            <a href="aplicacion/vistas/admin/dashboard.php?vista=#" class="menu-title direct-link">Dicipulado</a>
        </div>

        <div class="menu-item">
            <a href="aplicacion/vistas/admin/dashboard.php?vista=noticias" class="menu-title direct-link">Noticias</a>
        </div>

        <div class="menu-item">
            <div class="menu-title">Visitas</div>
            <div class="submenu">
                <a href="aplicacion/vistas/admin/dashboard.php?vista=visitasListar">Listar</a>
                <a href="aplicacion/vistas/admin/dashboard.php?vista=visitasMap">Ver Mapa</a>
            </div>
        </div>

        <!-- CERRAR SESIÓN -->
        <div class="menu-item" style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="/IglesiaDelNazarenoBagua/logout.php" class="menu-title direct-link" style="color: #e74c3c;">
                🔒 Cerrar sesión
            </a>
        </div>

    </div>

    <script src="admin/js/sidebar.js"></script>

</aside>
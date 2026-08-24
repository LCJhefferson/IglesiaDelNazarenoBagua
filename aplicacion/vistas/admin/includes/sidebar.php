<?php
/**
 * SIDEBAR CON RBAC GRANULAR
 * Cada opción del menú solo se muestra si el rol del usuario lo permite.
 * Roles: 1=Admin, 2=Pastor, 9=Discipulador, 11=Secretaria, 12=Grupo de Visitas
 */
$rolActual = (int)($_SESSION['rol_id'] ?? 0);
?>
<aside class="sidebar">

<div class="sidebar-logo">
    <img src="web/imagenes/selloOficial.png" alt="Logo">
    <h3>Iglesia Del Nazareno</h3>
    <h3>Bagua</h3>
</div>

<div class="menu">

    <!-- INICIO: Todos los roles -->
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=inicioAdmin" class="menu-title direct-link">
            <i class="fas fa-house"></i>
            Inicio
        </a>
    </div>

    <!-- USUARIOS: Solo Admin y Pastor -->
    <?php if (in_array($rolActual, [1, 2])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=usuarios_admin" class="menu-title direct-link">
            <i class="fas fa-users"></i>
            Usuarios
        </a>
    </div>
    <?php endif; ?>

    <!-- RECURSOS: Admin, Pastor, Secretaria -->
    <?php if (in_array($rolActual, [1, 2, 11])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=recurso_admin" class="menu-title direct-link">
            <i class="fas fa-box-archive"></i>
            Recursos
        </a>
    </div>
    <?php endif; ?>

    <!-- MEMBRESÍA: Admin, Pastor, Secretaria -->
    <?php if (in_array($rolActual, [1, 2, 11])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=membresia" class="menu-title direct-link">
            <i class="fas fa-id-card"></i>
            Membresía
        </a>
    </div>
    <?php endif; ?>

    <!-- TRANSMISIÓN: Solo Admin y Pastor -->
    <?php if (in_array($rolActual, [1, 2])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=transmision" class="menu-title direct-link">
            <i class="fas fa-tower-broadcast"></i>
            Transmisión
        </a>
    </div>
    <?php endif; ?>

    <!-- NOTICIAS: Admin, Pastor, Secretaria -->
    <?php if (in_array($rolActual, [1, 2, 11])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=noticias" class="menu-title direct-link">
            <i class="fas fa-newspaper"></i>
            Noticias
        </a>
    </div>
    <?php endif; ?>

    <!-- DISCIPULADO GRUPOS: Admin, Pastor, Secretaria, Discipulador -->
    <?php if (in_array($rolActual, [1, 2, 9, 11])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=DiscipuladoGrupos" class="menu-title direct-link">
            <i class="fas fa-people-group"></i>
            Grupos de Discipulado
        </a>
    </div>
    <?php endif; ?>

    <!-- DISCIPULADO INTEGRANTES: Admin, Pastor, Secretaria, Discipulador -->
    <?php if (in_array($rolActual, [1, 2, 9, 11])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=DiscipuladoIntegrantes" class="menu-title direct-link">
            <i class="fas fa-user-group"></i>
            Integrantes del Discipulado
        </a>
    </div>
    <?php endif; ?>

    <!-- LISTA DE VISITAS: Admin, Pastor, Grupo de Visitas -->
    <?php if (in_array($rolActual, [1, 2, 12])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=visitasListar" class="menu-title direct-link">
            <i class="fas fa-clipboard-list"></i>
            Lista de Visitas
        </a>
    </div>
    <?php endif; ?>

    <!-- MAPA DE VISITAS: Admin, Pastor, Grupo de Visitas -->
    <?php if (in_array($rolActual, [1, 2, 12])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=visitasMap" class="menu-title direct-link">
            <i class="fas fa-map-location-dot"></i>
            Mapa de Visitas
        </a>
    </div>
    <?php endif; ?>

    <!-- REPORTES: Admin, Pastor, Secretaria -->
    <?php if (in_array($rolActual, [1, 2, 11])): ?>
    <div class="menu-item">
        <a href="index.php?vista=dashboard&seccion=reportes" class="menu-title direct-link">
            <i class="fa-solid fa-file-invoice"></i>
            Reportes
        </a>
    </div>
    <?php endif; ?>

    <!-- CERRAR SESIÓN: Todos -->
    <div class="menu-item" style="margin-top: auto !important; padding: 20px 0;">
        <a href="index.php?vista=logout" class="menu-title direct-link"
           style="color: #4d6eff; font-weight: bold;">
            <i class="fas fa-right-from-bracket"></i>
            Cerrar sesión
        </a>
    </div>

</div>

<!-- <script src="admin/js/sidebar.js"></script> -->
</aside>

<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/publico/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin</title>

<base href="/IglesiaDelNazarenoBagua/">



<link rel="stylesheet" href="admin/css/dashboard.css">
<link rel="stylesheet" href="admin/css/sidebar.css">





<?php
if (isset($_GET['vista'])) {
    $vista = $_GET['vista'];
    if ($vista === 'NewUsuarioForm') {
        echo '<link rel="stylesheet" href="admin/css/NewUsuarioForm.css">';
    }
    if ($vista === 'NoticiasForm') {
        echo '<link rel="stylesheet" href="admin/css/NoticiasForm.css">';
    }
        if ($vista === 'crearnoticia') {
        echo '<link rel="stylesheet" href="admin/css/crearnoticia.css">';
    }
     if ($vista === 'gestionarnoticias') {
        echo '<link rel="stylesheet" href="admin/css/gestionarnoticias.css">';
    }
    if ($vista === 'membresia') {
        echo '<link rel="stylesheet" href="admin/css/membresia.css">';
    }
    if ($vista === 'recurso_admin') {
        echo '<link rel="stylesheet" href="admin/css/recurso_admin.css">';
    }
          if ($vista === 'reguistro_usuario') { //reenderizar mejor hay un problema con la renderizacion
        echo '<link rel="stylesheet" href="admin/css/reguistro_usuario.css">';
    }
    if ($vista === 'usuarios_admin') { 
        echo '<link rel="stylesheet" href="admin/css/usuarios_admin.css">';
    }
    if ($vista === 'visitasListar') { 
        echo '<link rel="stylesheet" href="admin/css/visitasListar.css">';
    }
    if ($vista === 'visitasMap') { 
        echo '<link rel="stylesheet" href="admin/css/visitasMap.css">';
    }
    if ($vista === 'transmision') { 
        echo '<link rel="stylesheet" href="admin/css/transmision.css">';
    }

    // y así con cada vista
}
?>
</head>

<body>

<div class="admin-container">
    <!-- SIDEBAR -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-area">
        <section class="content" id="contenedor-vista">
            <?php
            // Aquí decides qué contenido mostrar según el botón o parámetro
            if (isset($_GET['vista'])) {
                $vista = $_GET['vista'];
                $ruta = "contenidos/" . $vista . ".php";
                if (file_exists($ruta)) {
                    include $ruta;
                } else {
                    echo "<p style='color:red;'>No se encontró la vista solicitada.</p>";
                }
            } else {
                echo '<div class="contenedor-tarjeta"><div class="tarjeta"><h3>Bienvenido al panel</h3></div></div>';
            }
            ?>
        </section>
    </main>

</div>
<script src="admin/js/sidebar.js"></script>

<?php
if (isset($_GET['vista'])) {
    $vista = $_GET['vista'];
    if ($vista === 'NewUsuarioForm') {
        echo '<script src="admin/js/NewUsuarioForm.js"></script>';
    }
    if ($vista === 'NoticiasForm') {
        echo '<script src="admin/js/NoticiasForm.js"></script>';
    }
    if ($vista === 'usuarios_admin') {
        echo '<script src="admin/js/usuarios_admin.js"></script>';
    }
    if ($vista === 'visitasMap') {
        echo '<script src="admin/js/visitasMap.js"></script>';
    }    
    if ($vista === 'recurso_admin') {
        echo '<script src="admin/js/recurso_admin.js"></script>';
    }   
    if ($vista === 'recurso_admin') {
        echo '<script src="admin/js/recurso_admin.js"></script>';
    }  
    if ($vista === 'reguistro_usuario') {
        echo '<script src="admin/js/reguistro_usuario.js"></script>';
    }            
}
?>

</body>
</html>

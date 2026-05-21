<?php
/**
 * ARCHIVO: aplicacion/vistas/web/login.php
 */

// 1. Manejo de sesión seguro (evita errores de 'headers already sent')
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Definir URL si por alguna razón no viene del index
if (!defined('URL')) {
    define('URL', '/IglesiaDelNazarenoBagua/');
}

// 3. Redirección si ya existe sesión
if (isset($_SESSION['usuario'])) {
    header("Location: " . URL . "dashboard");
    exit;
}

$csrfToken = \aplicacion\core\Middleware::csrfGenerate();

// 4. Captura de errores de la URL
$error = isset($_GET['error']) ? (int)$_GET['error'] : 0;


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Iglesia del Nazareno Bagua</title>
    
    <link rel="stylesheet" href="<?php echo URL; ?>public/web/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>

<div id="alerta" class="alerta"></div>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">
            <img src="<?php echo URL; ?>public/web/imagenes/selloOficial.png" alt="Logo Iglesia">
        </div>

        <h2>Bienvenido</h2>
        <p class="login-subtitulo">Iglesia del Nazareno — Bagua</p>

        <form method="POST" action="<?php echo URL; ?>procesar_login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
            <div class="form-group">
                <i class="fa-solid fa-user icono-input"></i>
                <input type="text" name="usuario" placeholder="Usuario" required autocomplete="username">
            </div>

            <div class="form-group">
                <i class="fa-solid fa-lock icono-input"></i>
                <input type="password" name="password" id="inputPassword" placeholder="Contraseña" required autocomplete="current-password">
                <i class="fa-solid fa-eye icono-ojo" onclick="togglePassword()"></i>
            </div>

            <button type="submit" class="btn-login">
                <i class="fa-solid fa-right-to-bracket"></i> Ingresar
            </button>

            <div class="recuperar">
                <a href="#">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo URL; ?>public/admin/js/login.js"></script>

<script>
    const mensajes = {
        1: "Completa todos los campos ❌",
        2: "Credenciales incorrectas ❌",
        3: "Usuario no encontrado ❌"
    };
    
    const errorNum = <?php echo $error; ?>;
    
    if (errorNum > 0 && mensajes[errorNum]) {
        // Intentar usar la función del login.js, si no, usar alert
        if(typeof mostrarAlerta === 'function') {
            mostrarAlerta(mensajes[errorNum], "error");
        } else {
            alert(mensajes[errorNum]);
        }
    }

    function togglePassword() {
        const input = document.getElementById('inputPassword');
        const ojo = document.querySelector('.icono-ojo');
        if (input.type === 'password') {
            input.type = 'text';
            ojo.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            ojo.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
</body>
</html>
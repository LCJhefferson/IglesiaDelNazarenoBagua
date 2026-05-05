<?php
session_start();
// Redirección si ya está logueado (Ruta relativa al servidor)
if (isset($_SESSION['usuario'])) {
    header("Location: /IglesiaDelNazarenoBagua/index.php?vista=dashboard");
    exit;
}
$error = (int)($_GET['error'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Iglesia del Nazareno Bagua</title>
    
    <base href="/IglesiaDelNazarenoBagua/public/">
    
    <link rel="stylesheet" href="web/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>

<div id="alerta" class="alerta"></div>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">
            <img src="web/imagenes/selloOficial.png" alt="Logo Iglesia">
        </div>

        <h2>Bienvenido</h2>
        <p class="login-subtitulo">Iglesia del Nazareno — Bagua</p>

        <form method="POST" action="index.php?vista=procesar_login">
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

<script src="admin/js/login.js"></script>

<script>
    const mensajes = {
        1: "Completa todos los campos ❌",
        2: "Credenciales incorrectas ❌",
        3: "Usuario no encontrado ❌"
    };
    const error = <?= $error ?>;
    if (error && mensajes[error]) {
        // Asegúrate de que esta función exista en login.js
        if(typeof mostrarAlerta === 'function') {
            mostrarAlerta(mensajes[error], "error");
        } else {
            alert(mensajes[error]);
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
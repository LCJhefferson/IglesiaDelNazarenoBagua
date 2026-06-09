<?php
/**
 * ARCHIVO: aplicacion/vistas/web/login.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('URL')) {
    define('URL', '/IglesiaDelNazarenoBagua/');
}

if (isset($_SESSION['usuario'])) {
    header("Location: " . URL . "dashboard");
    exit;
}

$csrfToken = \aplicacion\core\Middleware::csrfGenerate();
$error = isset($_GET['error']) ? (int)$_GET['error'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Iglesia del Nazareno Bagua</title>
    
    <link rel="stylesheet" href="<?= URL ?>public/web/css/login.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    
    <style>
        /* --- ESTILOS DEL MODAL --- */
        .modal-overlay {
            display: none; /* Oculto por defecto */
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center; justify-content: center;
        }
        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            width: 90%; max-width: 400px;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-icon { font-size: 50px; color: #ef4444; margin-bottom: 15px; }
        .modal-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
        .modal-text { color: #64748b; margin-bottom: 25px; line-height: 1.5; }
        .btn-modal {
            background: #1e293b; color: #fff; border: none;
            padding: 12px 30px; border-radius: 8px; cursor: pointer;
            font-weight: 600; width: 100%; transition: background 0.3s;
        }
        .btn-modal:hover { background: #0f172a; }
    </style>
</head>
<body>

<?php include 'componentes/nav.php'; ?>

<div id="modalError" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="modal-title" id="modalTitulo">¡Atención!</div>
        <div class="modal-text" id="modalMensaje">Algo salió mal.</div>
        <button class="btn-modal" onclick="cerrarModal()">Entendido</button>
    </div>
</div>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">
            <img src="<?= URL ?>public/web/imagenes/selloOficial.png" alt="Logo Iglesia">
        </div>

        <h2>Bienvenido</h2>
        <p class="login-subtitulo">Panel Administrativo</p>

        <form method="POST" action="<?= URL ?>procesar_login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
            
            <div class="form-group">
                <i class="fa-solid fa-user icono-input"></i>
                <input type="text" name="usuario" placeholder="Nombre de usuario" required>
            </div>

            <div class="form-group">
                <i class="fa-solid fa-lock icono-input"></i>
                <input type="password" name="password" id="inputPassword" placeholder="Contraseña" required>
                <i class="fa-solid fa-eye icono-ojo" onclick="togglePassword()"></i>
            </div>

            <button type="submit" class="btn-login">
                Ingresar <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left: 8px;"></i>
            </button>

            <div class="recuperar">
                <a href="#">¿Problemas de acceso? Contactar soporte</a>
            </div>
        </form>
    </div>
</div>

<script>
    // 1. GESTIÓN DEL MODAL
    const errorNum = <?= $error ?>;
    const modal = document.getElementById('modalError');
    const txtMensaje = document.getElementById('modalMensaje');

    // Mapeo de errores con mensajes más genéricos por seguridad
    const mensajes = {
        1: "Por favor, completa todos los campos del formulario.",
        2: "Usuario o contraseña incorrectos. Inténtalo de nuevo.",
        3: "Acceso denegado. Verifica tus credenciales o el estado de tu cuenta."
    };

    if (errorNum > 0 && mensajes[errorNum]) {
        txtMensaje.innerText = mensajes[errorNum];
        modal.style.display = 'flex';
    }

    function cerrarModal() {
        modal.style.display = 'none';
        // Limpiamos la URL para que el modal no vuelva a salir al recargar
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // 2. TOGGLE PASSWORD
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

    // Cerrar modal al hacer clic fuera del contenido
    window.onclick = function(event) {
        if (event.target == modal) cerrarModal();
    }
</script>

</body>
</html>
<?php
// registro.php - Formulario de Registro de Usuarios

$error   = '';   // Mensaje de error al enviar el formulario
$exito   = '';   // Mensaje de éxito al crear la cuenta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura y limpieza de los datos enviados por el formulario
    $nombre    = trim($_POST['nombre']    ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $usuario   = trim($_POST['usuario']   ?? '');
    $correo    = trim($_POST['correo']    ?? '');
    $contrasena       = $_POST['contrasena']       ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    // Validaciones del formulario
    if (!$nombre || !$apellidos || !$usuario || !$correo || !$contrasena || !$confirmar) {
        $error = 'Por favor completa todos los campos.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (strlen($contrasena) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($contrasena !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Aquí iría la lógica de guardado en base de datos
        // Ejemplo: INSERT INTO usuarios (nombre, apellidos, usuario, correo, contrasena) VALUES (...)
        $exito = '¡Cuenta creada exitosamente! Ya puedes iniciar sesión.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — Mi Organización</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
</head>
<body>

<!-- ═══════════════════════════════════════
     TARJETA PRINCIPAL DE REGISTRO
════════════════════════════════════════ -->
<div class="contenedor-tarjeta">
    <div class="tarjeta">

        <!-- Cabecera con ícono y título -->
        <div class="cabecera-tarjeta">
            <div class="icono-avatar">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            </div>
            <div>
                <h1>Reguistro</h1>
                <p>Únete y forma parte de esta gran familia.</p>
            </div>
        </div>

        <!-- Alerta de error (solo si $error tiene contenido) -->
        <?php if ($error): ?>
        <div class="alerta alerta-error">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Alerta de éxito (solo si $exito tiene contenido) -->
        <?php if ($exito): ?>
        <div class="alerta alerta-exito">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <?= htmlspecialchars($exito) ?>
        </div>
        <?php endif; ?>

        <!-- ─── Formulario de registro ─── -->
        <form method="POST" action="" id="formulario-registro">

            <!-- Nombre y Apellidos en dos columnas -->
            <div class="fila-doble">
                <div class="campo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ana"
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                        autocomplete="given-name" required>
                </div>
                <div class="campo">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" placeholder="García"
                        value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>"
                        autocomplete="family-name" required>
                </div>
            </div>

            <!-- Nombre de usuario -->
            <div class="campo">
                <label for="usuario">Nombre de usuario</label>
                <input type="text" id="usuario" name="usuario" placeholder="ana_garcia"
                    value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                    autocomplete="username" required>
            </div>

            <!-- Correo electrónico -->
            <div class="campo">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" placeholder="ana@correo.com"
                    value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                    autocomplete="email" required>
            </div>

            <!-- Contraseña con botón mostrar/ocultar y barra de fuerza -->
            <div class="campo">
                <label for="contrasena">Contraseña</label>
                <div class="campo-interior">
                    <input type="password" id="contrasena" name="contrasena"
                        placeholder="Mínimo 8 caracteres" class="tiene-ojo"
                        autocomplete="new-password" required
                        oninput="evaluarFuerza(this.value)">
                    <!-- Botón del ojo: llama a mostrarOcultar() con el id del campo -->
                    <button type="button" class="boton-ojo"
                        onclick="mostrarOcultar('contrasena', this)"
                        aria-label="Ver contraseña">
                        <svg id="ojo-contrasena" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <!-- Barra de fuerza: 4 segmentos que se colorean según la puntuación -->
                <div class="barra-fuerza" id="barra-fuerza">
                    <span id="seg1"></span>
                    <span id="seg2"></span>
                    <span id="seg3"></span>
                    <span id="seg4"></span>
                </div>
            </div>

            <!-- Confirmar contraseña -->
            <div class="campo">
                <label for="confirmar">Confirmar contraseña</label>
                <div class="campo-interior">
                    <input type="password" id="confirmar" name="confirmar"
                        placeholder="Repite tu contraseña" class="tiene-ojo"
                        autocomplete="new-password" required>
                    <button type="button" class="boton-ojo"
                        onclick="mostrarOcultar('confirmar', this)"
                        aria-label="Ver contraseña">
                        <svg id="ojo-confirmar" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Botón principal para enviar el formulario -->
            <button type="submit" class="boton boton-primario">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                Crear cuenta
            </button>

        </form>

        <!-- Divisor decorativo -->
        <div class="divisor">o regístrate con tu cuenta de google</div>

        <!-- Botón de Google -->
        <a href="?google=1" style="text-decoration:none; display:block;">
            <button class="boton boton-google" type="button">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                Continuar con Google
            </button>
        </a>

        <!-- Pie: enlace a login y términos -->
        <div class="pie-tarjeta">
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
            <p class="terminos">
                Al registrarte aceptas los
                <a href="#">Términos de uso</a> y la <a href="#">Política de privacidad</a>.
            </p>
        </div>

    </div><!-- fin .tarjeta -->
</div><!-- fin .contenedor-tarjeta -->

</body>
</html>
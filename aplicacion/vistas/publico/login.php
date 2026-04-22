<?php if (isset($_GET['error'])): ?>
<script>
    <?php if ($_GET['error'] == 1): ?>
        mostrarAlerta("Completa todos los campos ❌", "error");
    <?php elseif ($_GET['error'] == 2): ?>
        mostrarAlerta("Contraseña incorrecta ❌", "error");
    <?php elseif ($_GET['error'] == 3): ?>
        mostrarAlerta("Usuario no encontrado ❌", "error");
    <?php endif; ?>
</script>
<?php endif; ?>



<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <base href="/IglesiaDelNazarenoBagua/">
    <link rel="stylesheet" href="public/css/login.css">
</head>
<body>



<div id="alerta" class="alerta"></div>
<div class="login-wrapper">
    <div class="login-card">
        <h2>Iniciar Sesión</h2>

        

        <form method="POST" action="procesar_login.php">

            <div class="form-group">
                <input type="text" name="usuario" placeholder="Usuario" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>

            <!-- NUEVO: SELECT DE ROLES -->
            <div class="form-group">
                <select name="rol" required>
                    <option value="">Selecciona tu rol</option>
                    <option value="1">Administrador</option>
                    <option value="2">Pastor</option>
                    <option value="3">Usuario</option>
                </select>
            </div>

            <button type="submit" class="btn-login">
                Ingresar
            </button>

            <!-- RECUPERAR CONTRASEÑA -->
            <div class="recuperar">
                <a href="#">¿Olvidaste tu contraseña?</a>
            </div>

        </form>
    </div>
</div>
<script src="public/js/login.js"></script>
</body>
</html>




//mostrarAlerta("Datos incorrectos ❌", "error");

// mostrarAlerta("Acceso correcto ✔️", "success");

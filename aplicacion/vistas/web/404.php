<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página no encontrada - 404</title>
    <style>
        .container-404 {
            text-align: center;
            padding: 100px 20px;
            font-family: sans-serif;
        }
        .container-404 h1 { font-size: 80px; color: #ccc; margin: 0; }
        .container-404 h2 { font-size: 24px; color: #333; }
        .container-404 p { color: #666; margin-bottom: 30px; }
        .btn-volver {
            background-color: #0056b3;
            color: white;
            padding: 10px 25px;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .btn-volver:hover { background-color: #003d80; }
    </style>
</head>
<body>



<div class="container-404">
    <h1>404</h1>
    <h2>¡Oops! Página no encontrada</h2>
    <p>Lo sentimos, pero la página que buscas no existe o ha sido movida.</p>
    <a href="<?= URL ?>inicio" class="btn-volver">Volver al Inicio</a>
</div>


</body>
</html>
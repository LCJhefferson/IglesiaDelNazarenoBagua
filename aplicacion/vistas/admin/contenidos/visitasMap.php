<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mapa de Visitas</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="<?= URL ?>public/web/css/visitasMap.css"/>
</head>
<body>

<header>
    <h1>Vista General de Miembros</h1>
    <div class="filtros">
        <div class="contenedor">
            <label for="buscarNombre">Filtrar por nombre</label>
            <i class="fa-solid fa-user"></i>
            <input type="text" id="buscarNombre" placeholder="Escribe el nombre o apellido..."/>
        </div>
        <div class="contenedor">
            <label for="buscarEstado">Filtrar por estado</label>
            <i class="fa-solid fa-traffic-light"></i>
            <select id="buscarEstado">
                <option value="todos">Todos los estados</option>
                <option value="reciente">Visitado reciente</option>
                <option value="intermedio">Visitado intermedio</option>
                <option value="proximo">Pendiente próximo</option>
                <option value="critico">Pendiente crítico</option>
                <option value="ninguno">Sin visitas registrados</option>
            </select>
        </div>
    </div>
</header>

<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    const URL_BASE_PROYECTO = '<?= URL ?>';
</script>

<script src="<?= URL ?>public/web/js/visitasMap.js"></script>

</body>
</html>
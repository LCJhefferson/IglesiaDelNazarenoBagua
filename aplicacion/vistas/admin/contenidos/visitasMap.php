<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mapa de Visitas</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

</head>
<body>

<header>
  <h1>Vista General de Miembros</h1>
  <div class="filtros">
    <!-- Filtro por nombre -->
    <div class="contenedor">
      <label for="buscarNombre">Filtrar por nombre</label>
      <i class="fa-solid fa-user"></i>
      <input type="text" id="buscarNombre" placeholder="Escribe el nombre..."/>
    </div>
    <!-- Filtro por estado -->
    <div class="contenedor">
      <label for="buscarEstado">Filtrar por estado</label>
      <i class="fa-solid fa-traffic-light"></i>
      <select id="buscarEstado">
        <option value="todos">Todos</option>
        <option value="Visitado">Realizadas</option>
        <option value="Cercano">Pendientes</option>
        <option value="Retrasada">Retrasadas</option>
      </select>
    </div>
  </div>
</header>

<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

</body>
</html>

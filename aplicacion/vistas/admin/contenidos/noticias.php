<?php
include("layout.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Noticias</title>
  <!-- Estilos específicos de noticias -->
  <link rel="stylesheet" href="../../../public/css/noticias.css">
</head>
<body>

<div class="contenedor-noticias">
  <!-- Tabla de noticias -->
  <div class="tabla-noticias">
    <h2>Noticias</h2>
    <button id="btn-nuevo" class="btn-nuevo">+ Nuevo</button>
    <table>
      <thead>
        <tr>
          <th>Título</th>
          <th>Resumen</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Misión Social</td>
          <td>Jornada de ayuda comunitaria</td>
          <td>
            <button class="btn-eliminar">❌</button>
            <button class="btn-editar">✏️</button>
            <button class="btn-ver">👁️</button>
          </td>
        </tr>
        <tr>
          <td>Nuevo Estudio Bíblico</td>
          <td>Iniciaremos un nuevo curso de estudio bíblico</td>
          <td>
            <button class="btn-eliminar">❌</button>
            <button class="btn-editar">✏️</button>
            <button class="btn-ver">👁️</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Panel de previsualización -->
  <div id="preview-noticia" class="preview">
    <div class="card-noticia">
      <h2>Misión Social</h2>
      <p class="fecha">24 de Mayo de 2024</p>
      <img src="../../../public/img/noticia.jpg" alt="Imagen noticia">
      <p class="contenido">
        La Iglesia del Nazareno realizó una jornada de ayuda comunitaria entregando víveres a las familias necesitadas.
      </p>
    </div>
  </div>
</div>

<!-- Modal para crear/editar -->
<div id="modal-noticia" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h3 id="modal-titulo">Nueva noticia</h3>
    <form>
      <label>Título</label>
      <input type="text" id="titulo">
      <label>Fecha</label>
      <input type="date" id="fecha">
      <label>Resumen</label>
      <textarea id="resumen"></textarea>
      <label>Contenido</label>
      <textarea id="contenido"></textarea>
      <label>Imagen</label>
      <input type="file" id="imagen">
      <button type="button" id="btn-guardar">Guardar</button>
    </form>
  </div>
</div>

<!-- Scripts -->
<link rel="stylesheet" href="../../../public/css/noticias.css">
<script src="../../../public/js/noticias.js"></script>

</body>
</html>

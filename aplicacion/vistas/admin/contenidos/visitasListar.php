<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lista de Visitas</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>

<h1>Visitas últimos 3 meses</h1>

<!-- Tarjetas de estadísticas -->
<div class="cuadricula-estadisticas">
  <div class="tarjeta-estadistica">
    <div class="icono-estadistica azul"><i class="fa-solid fa-users"></i></div>
    <div class="datos-estadistica"><div class="valor">12</div><div class="etiqueta">Total visitas</div></div>
  </div>
  <div class="tarjeta-estadistica">
    <div class="icono-estadistica verde"><i class="fa-solid fa-circle-check"></i></div>
    <div class="datos-estadistica"><div class="valor">6</div><div class="etiqueta">Realizadas</div></div>
  </div>
  <div class="tarjeta-estadistica">
    <div class="icono-estadistica naranja"><i class="fa-solid fa-clock"></i></div>
    <div class="datos-estadistica"><div class="valor">5</div><div class="etiqueta">Pendientes</div></div>
  </div>
  <div class="tarjeta-estadistica">
    <div class="icono-estadistica rojo"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div class="datos-estadistica"><div class="valor">1</div><div class="etiqueta">Retrasadas</div></div>
  </div>
</div>

<!-- Tabla -->
<table>
  <thead>
    <tr><th>Miembro</th><th>Dirección</th><th>Última visita</th><th>Estado</th><th style="text-align:center">Acciones</th></tr>
  </thead>
  <tbody>
    <tr>
      <td>Carlos Mendoza</td><td>Av. Principal 123</td><td>Hace 1 mes</td>
      <td><span class="badge-estado estado-verde"><i class="fa-solid fa-circle-check"></i> Realizada</span></td>
      <td class="acciones" style="text-align:center;">
        <button title="Marcar como visitado">✔️</button>
        <button title="Cancelar visita">❌</button>
      </td>
    </tr>
    <tr>
      <td>María López</td><td>Jr. Amazonas 456</td><td>Hace 2 meses y 25 días</td>
      <td><span class="badge-estado estado-amarillo"><i class="fa-solid fa-clock"></i> Pendiente</span></td>
      <td class="acciones" style="text-align:center;">
        <button>✔️</button><button>❌</button>
      </td>
    </tr>
    <tr>
      <td>Juan Pérez</td><td>Av. Bagua Grande</td><td>Hace 4 meses</td>
      <td><span class="badge-estado estado-rojo"><i class="fa-solid fa-triangle-exclamation"></i> Retrasada</span></td>
      <td class="acciones" style="text-align:center;">
        <button>✔️</button><button>❌</button>
      </td>
    </tr>
  </tbody>
</table>

</body>
</html>

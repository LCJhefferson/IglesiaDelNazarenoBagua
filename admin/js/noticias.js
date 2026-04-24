// 
document.getElementById('btn-nuevo').addEventListener('click', () => {
  document.getElementById('modal-noticia').style.display = 'block';
  document.getElementById('modal-titulo').innerText = "Nueva noticia";
});

// Cerrar modal
document.querySelector('.close').addEventListener('click', () => {
  document.getElementById('modal-noticia').style.display = 'none';
});

// Previsualizar noticia
document.querySelectorAll('.btn-ver').forEach(btn => {
  btn.addEventListener('click', function() {
    document.getElementById('preview-noticia').innerHTML = `
      <div class="card-noticia">
        <h2>Misión Social</h2>
        <p class="fecha">24 de Mayo de 2024</p>
        <img src="../public/img/noticia.jpg" alt="Imagen noticia">
        <p class="contenido">
          La Iglesia del Nazareno realizó una jornada de ayuda comunitaria entregando víveres a las familias necesitadas.
        </p>
      </div>
    `;
  });
});

// Editar noticia
document.querySelectorAll('.btn-editar').forEach(btn => {
  btn.addEventListener('click', function() {
    document.getElementById('modal-noticia').style.display = 'block';
    document.getElementById('modal-titulo').innerText = "Editar noticia";
  });
});

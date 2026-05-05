
  // Inicializar mapa con zoom más cercano
  const map = L.map('map').setView([-5.637, -78.528], 15); // zoom 15 en vez de 13

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:'&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Íconos tipo pin de colores (estilo Google Maps)
  const iconVerde = L.icon({
    iconUrl:'https://maps.gstatic.com/mapfiles/ms2/micons/green-dot.png',
    iconSize:[32,32], iconAnchor:[16,32], popupAnchor:[0,-32]
  });
  const iconAmarillo = L.icon({
    iconUrl:'https://maps.gstatic.com/mapfiles/ms2/micons/yellow-dot.png',
    iconSize:[32,32], iconAnchor:[16,32], popupAnchor:[0,-32]
  });
  const iconRojo = L.icon({
    iconUrl:'https://maps.gstatic.com/mapfiles/ms2/micons/red-dot.png',
    iconSize:[32,32], iconAnchor:[16,32], popupAnchor:[0,-32]
  });

  // Datos de ejemplo
  const personas = [
    {nombre:"Carlos Mendoza", direccion:"Av. Principal 123", estado:"Visitado", coords:[-5.637,-78.528]},
    {nombre:"María López", direccion:"Jr. Amazonas 456", estado:"Cercano", coords:[-5.640,-78.530]},
    {nombre:"Juan Pérez", direccion:"Av. Bagua Grande", estado:"Retrasada", coords:[-5.635,-78.532]},
    {nombre:"Ana Torres", direccion:"Av. Colán 789", estado:"Visitado", coords:[-5.639,-78.529]},
    {nombre:"Pedro Ramírez", direccion:"Jr. Bagua 321", estado:"Cercano", coords:[-5.638,-78.531]},
    {nombre:"Sofía Vargas", direccion:"Av. Amazonas 222", estado:"Retrasada", coords:[-5.636,-78.534]}
  ];

  // Función para mostrar estado con ícono y color
  function badgeEstado(estado){
    if(estado==="Visitado") return "<span style='color:#2f9e44'><i class='fa-solid fa-circle-check'></i> Visitado</span>";
    if(estado==="Cercano") return "<span style='color:#e67700'><i class='fa-solid fa-clock'></i> Cercano</span>";
    if(estado==="Retrasada") return "<span style='color:#c92a2a'><i class='fa-solid fa-triangle-exclamation'></i> Retrasada</span>";
    return estado;
  }

  // Guardar marcadores para filtrado
  const marcadores = [];
  personas.forEach(p=>{
    let icono = iconVerde;
    if(p.estado==="Cercano") icono = iconAmarillo;
    if(p.estado==="Retrasada") icono = iconRojo;

    const marker = L.marker(p.coords,{icon:icono}).addTo(map)
      .bindPopup(`<b>${p.nombre}</b><br>${p.direccion}<br>${badgeEstado(p.estado)}`);
    marcadores.push({marker, ...p});
  });

  // Filtro por nombre
  document.getElementById('buscarNombre').addEventListener('input', e=>{
    const val = e.target.value.toLowerCase();
    const persona = personas.find(p=>p.nombre.toLowerCase().includes(val));
    if(persona) map.setView(persona.coords,17); // zoom más cercano al buscar
  });

  // Filtro por estado
  document.getElementById('buscarEstado').addEventListener('change', e=>{
    const estadoSel = e.target.value;
    marcadores.forEach(m=>{
      if(estadoSel==="todos" || m.estado===estadoSel){
        map.addLayer(m.marker);
      } else {
        map.removeLayer(m.marker);
      }
    });
  });

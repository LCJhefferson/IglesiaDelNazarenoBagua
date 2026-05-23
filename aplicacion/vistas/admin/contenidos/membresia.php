<?php
use aplicacion\controladores\MiembroController;

$controller = new MiembroController();
$controller->manejarPeticion(); 

$miembros = $controller->listarMiembros();
$cargos = $controller->obtenerCargos();
$condiciones = $controller->obtenerCondiciones();
$tipos = $controller->obtenerTipos();

// Estadísticas dinámicas con mitigación de nulos
$activos = count(array_filter($miembros, fn($m) => (int)($m['estado'] ?? 1) === 1));
$inactivos = count(array_filter($miembros, fn($m) => (int)($m['estado'] ?? 1) === 0));
$externos = count(array_filter($miembros, fn($m) => (int)($m['tipo_miembro_id'] ?? 1) !== 1 && (int)($m['estado'] ?? 1) === 1));

// Pastores y Líderes basados en el texto del cargo (más seguro si los IDs cambian)
$pastores = count(array_filter($miembros, fn($m) => (str_contains(strtolower($m['cargo_nombre'] ?? ''), 'pastor') && (int)$m['estado'] === 1)));
$lideres = count(array_filter($miembros, fn($m) => (str_contains(strtolower($m['cargo_nombre'] ?? ''), 'lider') && (int)$m['estado'] === 1)));

$fechaHoy = date('Y-m-d'); 
?>


<div class="header">
    <h2><i class="fa-solid fa-users"></i> Gestión de Membresía</h2>
    <button class="nuevo" onclick="abrirModal()">
        <i class="fa-solid fa-user-plus"></i> Nuevo Miembro
    </button>
</div>

<div class="cards">
    <div class="card" style="border-left: 5px solid #28a745;">
        <i class="fa-solid fa-user-check icono" style="color: #28a745;"></i>
        <div><h3><?= $activos ?></h3><p>Activos</p></div>
    </div>
    <div class="card" style="border-left: 5px solid #4f6ef7;">
        <i class="fa-solid fa-user-tie icono" style="color: #4f6ef7;"></i>
        <div><h3><?= $pastores ?></h3><p>Pastores</p></div>
    </div>
    <div class="card" style="border-left: 5px solid #ffc107;">
        <i class="fa-solid fa-star icono" style="color: #ffc107;"></i>
        <div><h3><?= $lideres ?></h3><p>Líderes</p></div>
    </div>
    <div class="card" style="border-left: 5px solid #6f42c1;">
        <i class="fa-solid fa-earth-americas icono" style="color: #6f42c1;"></i>
        <div><h3><?= $externos ?></h3><p>Externos</p></div>
    </div>
    <div class="card" style="border-left: 5px solid #dc3545;">
        <i class="fa-solid fa-user-xmark icono" style="color: #dc3545;"></i>
        <div><h3><?= $inactivos ?></h3><p>Inactivos</p></div>
    </div>
</div>

<div class="buscador-container">
    <div class="input-group">
        <input class="buscador" type="text" id="buscar" onkeyup="filtrarTabla()" placeholder="Buscar por nombre o apellido..." style="width: 100%;">
    </div>
    
    <select id="filtroTipo" onchange="filtrarTabla()">
        <option value="">Todos los Orígenes</option>
        <?php foreach($tipos as $t): ?>
            <option value="<?= htmlspecialchars($t['nombre'] ?? '') ?>"><?= htmlspecialchars($t['nombre'] ?? '') ?></option>
        <?php endforeach; ?>
    </select>

    <select id="filtroRol" onchange="filtrarTabla()">
        <option value="">Todos los Roles</option>
        <?php foreach($cargos as $c): ?>
            <option value="<?= htmlspecialchars($c['nombre'] ?? '') ?>"><?= htmlspecialchars(ucfirst($c['nombre'] ?? '')) ?></option>
        <?php endforeach; ?>
    </select>

    <select id="filtroEstado" onchange="filtrarTabla()">
        <option value="1">Solo Activos</option>
        <option value="0">Solo Inactivos</option>
        <option value="">Ver Todos</option>
    </select>
</div>

<table>
    <thead>
        <tr>
            <th>Nombre y Origen</th>
            <th>Teléfono</th>
            <th>Roles / Cargos</th>
            <th>Condición</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="tablaCuerpo">
        <?php foreach($miembros as $m): ?>
        <tr data-estado="<?= $m['estado'] ?? '1' ?>">
            <td>
                <div style="display: flex; flex-direction: column;">
                    <strong><?= htmlspecialchars(($m['nombres'] ?? '') . " " . ($m['apellidos'] ?? '')) ?></strong>
                    <small style="color: #64748b;">
                        <i class="fa-solid fa-church"></i> 
                        <span class="col-tipo"><?= htmlspecialchars($m['tipo_nombre'] ?? 'Local') ?></span> 
                    </small>
                </div>
            </td>
            <td><?= htmlspecialchars($m['telefono'] ?? 'S/N') ?></td>
            <td class="col-rol">
                <?php 
                if (!empty($m['cargo_nombre'])) {
                    // Separar los cargos si hay varios
                    $listaCargos = explode(', ', $m['cargo_nombre']);
                    foreach ($listaCargos as $nombreCargo) {
                        $nombreCargo = trim($nombreCargo);
                        $slug = strtolower($nombreCargo);
                        // Determinar clase de color
                        $claseColor = 'cargo-default';
                        if(str_contains($slug, 'pastor')) $claseColor = 'cargo-pastor';
                        elseif(str_contains($slug, 'lider')) $claseColor = 'cargo-lider';
                        elseif(str_contains($slug, 'maestro')) $claseColor = 'cargo-maestro';
                        elseif(str_contains($slug, 'evangelista')) $claseColor = 'cargo-evangelista';
                        elseif(str_contains($slug, 'discipulador')) $claseColor = 'cargo-discipulador';
                        elseif(str_contains($slug, 'miembro')) $claseColor = 'cargo-miembro';
                        
                        echo "<span class='badge-cargo " . htmlspecialchars($claseColor, ENT_QUOTES) . "'>" . htmlspecialchars($nombreCargo, ENT_QUOTES) . "</span>";
                    }
                } else {
                    // Si no tiene cargos asignados, se muestra como Miembro por defecto
                    echo "<span class='badge-cargo cargo-miembro'>Miembro</span>";
                }
                ?>
            </td>
            <td class="col-condicion"><?= htmlspecialchars($m['condicion_nombre'] ?? 'Saludable') ?></td>
            <td>
                <?php if((int)($m['estado'] ?? 1) === 1): ?>
                    <span style="color: #28a745; font-weight: bold;"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Activo</span>
                <?php else: ?>
                    <span style="color: #dc3545; font-weight: bold;"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Inactivo</span>
                <?php endif; ?>
            </td>
            <td>
                <button class="btn editar" onclick='editar(<?= json_encode($m) ?>)'>
                    <i class="fa-solid fa-pen"></i>
                </button>
                <a class="btn <?= (int)($m['estado'] ?? 1) === 1 ? 'eliminar' : 'editar' ?>" 
                   href="index.php?vista=dashboard&seccion=membresia&<?= (int)($m['estado'] ?? 1) === 1 ? 'eliminar' : 'activar' ?>=<?= $m['id'] ?>" 
                   style="<?= (int)($m['estado'] ?? 1) === 0 ? 'background: #28a745; color: white;' : '' ?>"
                   onclick="return confirm('¿Confirmar cambio de estado?')">
                    <i class="fa-solid <?= (int)($m['estado'] ?? 1) === 1 ? 'fa-user-slash' : 'fa-user-plus' ?>"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="modal" id="modal">
    <div class="modal-box">
        <h3 id="tituloModal"><i class="fa-solid fa-user-plus"></i> Gestionar Miembro</h3>
       
        <form method="POST" id="formMiembro" action="index.php?vista=dashboard&seccion=membresia">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
            <input type="hidden" name="id">
            <div class="grid">
                <div class="form-group">
                    <label>Nombres:</label>
                    <input type="text" name="nombres" placeholder="Escriba los nombres" required>
                </div>
                <div class="form-group">
                    <label>Apellidos:</label>
                    <input type="text" name="apellidos" placeholder="Escriba los apellidos" required>
                </div>
                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" placeholder="Ej. 987654321">
                </div>
                <div class="form-group">
                    <label>Fecha de Nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" value="<?= $fechaHoy ?>">
                </div>
                <div class="form-group">
                    <label>Origen / Tipo:</label>
                    <select name="tipo_miembro_id" id="tipo_miembro_id" required>
                        <?php foreach($tipos as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cargos / Funciones:</label>
                    <select name="cargos[]" id="cargos_select" class="form-control" multiple="multiple" style="width: 100%;">
                        <?php foreach($cargos as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Condición:</label>
                    <select name="condicion_id" required>
                        <option value="">Seleccione Condición</option>
                        <?php foreach($condiciones as $con): ?>
                            <option value="<?= $con['id'] ?>"><?= htmlspecialchars(ucfirst($con['nombre'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado:</label>
                    <select name="estado" id="inputEstado">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Dirección:</label>
                    <div class="campo-mapa">
                        <input type="text"
                        name="direccion"
                        id="direccion"
                        placeholder="Calle, Jr o Av.">
                        <button type="button" class="btn-mapa" onclick="abrirMapa()">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Latitud:</label>
                    <input type="text" name="latitud" id="latitud" placeholder="Automático" readonly>
                </div>
                <div class="form-group">
                    <label>Longitud:</label>
                    <input type="text" name="longitud" id="longitud" placeholder="Automático" readonly>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px; justify-content: flex-end;">
                <button type="button" onclick="cerrarModal()" style="background:#dee2e6; color:#495057; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">Cancelar</button>
                <button type="submit" name="registrar" id="btnAgregar" class="nuevo">Guardar Miembro</button>
                <button type="submit" name="editar" id="btnActualizar" class="nuevo" style="display:none; background:#38d9a9;">Actualizar Datos</button>
            </div>
        </form>
    </div>


<!-- Modal para selección de ubicación en el mapa usando leaflet-->
<!-- LEAFLET -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<!-- MODAL MAPA -->
<div id="modalMapa" class="modal-mapa">
    <div class="modal-mapa-box">

        <h3>
            <i class="fa-solid fa-map-location-dot"></i>
            Seleccionar Ubicación
        </h3>

        <p style="font-size: 0.85rem; color:#64748b; margin-bottom:10px;">
            Usa el buscador o haz clic en el mapa para seleccionar la dirección.
        </p>

        <div id="mapa-seleccionar"></div>

        <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">

            <button type="button"
                    onclick="cerrarModalMapa()"
                    style="background:#dee2e6; color:#495057; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">

                Cancelar

            </button>

            <button type="button"
                    onclick="confirmarUbicacion()"
                    style="background:#4f6ef7; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">

                Guardar Ubicación

            </button>

        </div>

    </div>
</div>

<style>

.modal-mapa{
    display:none;
    position:fixed;
    z-index:2000;
    left:0;
    top:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.7);
    backdrop-filter:blur(4px);
}

.modal-mapa-box{
    background:white;
    width:95%;
    max-width:1400px;
    margin:2vh auto;
    padding:20px;
    border-radius:15px;
    box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);
}

#mapa-seleccionar{
    height:75vh;
    width:100%;
    border-radius:10px;
    margin-top:10px;
    border:1px solid #cbd5e1;
}

.leaflet-control-geocoder{
    min-width:450px !important;
    font-size:16px;
    border:2px solid #4f6ef7 !important;
    border-radius:8px !important;
}

.leaflet-container{
    background:#f1f5f9 !important;
}

.leaflet-marker-icon{
    filter:drop-shadow(0 4px 6px rgba(0,0,0,0.3));
}

</style>

<script>

function abrirModal(){
    document.getElementById('modal').style.display = 'flex';
}

function cerrarModal(){
    document.getElementById('modal').style.display = 'none';
}

function cerrarModalMapa(){
    document.getElementById('modalMapa').style.display = 'none';
}

function confirmarUbicacion(){
    cerrarModalMapa();
}

let mapa;
let marcador;

function abrirMapa(){

    document.getElementById('modalMapa').style.display = 'block';

    setTimeout(() => {

        if(!mapa){

            // BAGUA CHICA
            const latInicial = -5.6419;
            const lngInicial = -78.5317;

            mapa = L.map('mapa-seleccionar')
                .setView([latInicial, lngInicial], 16);

            // MAPA MODERNO
            L.tileLayer(
                'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
                {
                    attribution:'&copy; OpenStreetMap & CartoDB'
                }
            ).addTo(mapa);

            // MARCADOR
            marcador = L.marker(
                [latInicial, lngInicial],
                {
                    draggable:true
                }
            ).addTo(mapa);

            // VALORES INICIALES
            document.getElementById('latitud').value = latInicial;
            document.getElementById('longitud').value = lngInicial;

            obtenerDireccion(latInicial, lngInicial);

            // CLICK EN EL MAPA
            mapa.on('click', async function(e){

                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                marcador.setLatLng([lat, lng]);

                document.getElementById('latitud').value =
                    lat.toFixed(6);

                document.getElementById('longitud').value =
                    lng.toFixed(6);

                await obtenerDireccion(lat, lng);

            });

            // MOVER MARCADOR
            marcador.on('dragend', async function(){

                const pos = marcador.getLatLng();

                document.getElementById('latitud').value =
                    pos.lat.toFixed(6);

                document.getElementById('longitud').value =
                    pos.lng.toFixed(6);

                await obtenerDireccion(pos.lat, pos.lng);

            });

            // BUSCADOR
            L.Control.geocoder({

                defaultMarkGeocode:false

            })

            .on('markgeocode', async function(e){

                const center = e.geocode.center;

                mapa.setView(center, 18);

                marcador.setLatLng(center);

                document.getElementById('latitud').value =
                    center.lat.toFixed(6);

                document.getElementById('longitud').value =
                    center.lng.toFixed(6);

                document.getElementById('direccion').value =
                    e.geocode.name;

            })

            .addTo(mapa);

        }

        mapa.invalidateSize();

    }, 300);

}

// OBTENER DIRECCIÓN AUTOMÁTICA
async function obtenerDireccion(lat, lng){

    try{

        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
        );

        const data = await response.json();

        let direccion = '';

if(data.address){

    const road =
        data.address.road ||
        data.address.pedestrian ||
        data.address.residential ||
        '';

    // Detectar tipo de vía
    let prefijo = '';

    if(road.toLowerCase().includes('avenida') || road.toLowerCase().includes('av')){
        prefijo = 'Av. ';
    }
    else if(road.toLowerCase().includes('jiron') || road.toLowerCase().includes('jr')){
        prefijo = 'Jr. ';
    }
    else if(road.toLowerCase().includes('calle')){
        prefijo = 'Calle ';
    }

    direccion = prefijo + road;

    // Si no encuentra calle
    if(direccion.trim() === ''){
        direccion =
            data.address.suburb ||
            data.address.neighbourhood ||
            data.display_name ||
            '';
    }

}else{

    direccion = data.display_name || '';

}

        // LLENAR INPUT DIRECCIÓN
        const inputDireccion = document.getElementById('direccion');

        if(inputDireccion){
            inputDireccion.value = direccion;
        }

    }catch(error){

        console.error('Error obteniendo dirección:', error);

    }

}

</script>
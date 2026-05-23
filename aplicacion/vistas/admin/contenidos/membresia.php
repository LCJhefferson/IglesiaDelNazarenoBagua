<?php
use aplicacion\controladores\MiembroController;

$controller = new MiembroController();
$controller->manejarPeticion(); 

$miembros = $controller->listarMiembros(); 
$cargos = $controller->obtenerCargos();
$condiciones = $controller->obtenerCondiciones();
$tipos = $controller->obtenerTipos();

// Función ayudante para evitar errores fatales si es Objeto o Array
function getProp($item, $key) {
    return is_object($item) ? ($item->$key ?? '') : ($item[$key] ?? '');
}

$activos = $miembros->where('estado', 1)->count();
$inactivos = $miembros->where('estado', 0)->count();

$externos = $miembros->filter(function($m) {
    return (int)getProp($m, 'tipo_miembro_id') !== 1 && (int)getProp($m, 'estado') === 1;
})->count();

$pastores = $miembros->filter(function($m) {
    return getProp($m, 'estado') == 1 && $m->cargos->contains(fn($c) => str_contains(strtolower(getProp($c, 'nombre')), 'pastor'));
})->count();

$lideres = $miembros->filter(function($m) {
    return getProp($m, 'estado') == 1 && $m->cargos->contains(fn($c) => str_contains(strtolower(getProp($c, 'nombre')), 'lider'));
})->count();

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
            <option value="<?= htmlspecialchars(getProp($t, 'nombre')) ?>"><?= htmlspecialchars(getProp($t, 'nombre')) ?></option>
        <?php endforeach; ?>
    </select>

    <select id="filtroRol" onchange="filtrarTabla()">
        <option value="">Todos los Roles</option>
        <?php foreach($cargos as $c): ?>
            <option value="<?= htmlspecialchars(getProp($c, 'nombre')) ?>"><?= htmlspecialchars(ucfirst(getProp($c, 'nombre'))) ?></option>
        <?php endforeach; ?>
    </select>

    <select id="filtroEstado" onchange="filtrarTabla()">
        <option value="">Ver Todos</option>
        <option value="1">Solo Activos</option>
        <option value="0">Solo Inactivos</option>
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
    <tr data-estado="<?= getProp($m, 'estado') ?>">
        <td>
            <div style="display: flex; flex-direction: column;">
                <strong><?= htmlspecialchars(getProp($m, 'nombres') . " " . getProp($m, 'apellidos')) ?></strong>
                <small style="color: #64748b;">
                    <i class="fa-solid fa-church"></i> 
                    <span class="col-tipo"><?= htmlspecialchars($m->tipo->nombre ?? 'Sin Origen') ?></span>
                </small>
            </div>
        </td>
        <td><?= htmlspecialchars(getProp($m, 'telefono') ?: 'S/N') ?></td>

        <td class="col-rol">
            <?php if ($m->cargos && $m->cargos->count() > 0): ?>
                <?php foreach ($m->cargos as $cargo): ?>
                    <?php 
                        $slug = strtolower(getProp($cargo, 'nombre'));
                        $claseColor = 'cargo-default';
                        if(str_contains($slug, 'pastor')) $claseColor = 'cargo-pastor';
                        elseif(str_contains($slug, 'lider')) $claseColor = 'cargo-lider';
                    ?>
                    <span class="badge-cargo <?= $claseColor ?>"><?= htmlspecialchars(getProp($cargo, 'nombre')) ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="badge-cargo cargo-miembro">Miembro</span>
            <?php endif; ?>
        </td>

        <td class="col-condicion"><?= htmlspecialchars($m->condicion->nombre ?? 'Saludable') ?></td>
        
        <td>
            <?php if((int)getProp($m, 'estado') === 1): ?>
                <span style="color: #28a745; font-weight: bold;"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Activo</span>
            <?php else: ?>
                <span style="color: #8d8b8b; font-weight: bold;"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Inactivo</span>
            <?php endif; ?>
        </td>
        <td>
            <button class="btn editar" onclick='editar(<?= json_encode($m) ?>)'>
                <i class="fa-solid fa-pen"></i>
            </button>
            
            <a class="btn <?= (int)getProp($m, 'estado') === 1 ? 'eliminar' : 'editar' ?>" 
            href="javascript:void(0)" 
            style="<?= (int)getProp($m, 'estado') === 0 ? 'background: #28a745; color: white;' : '' ?>"
            onclick="showConfirm('index.php?vista=dashboard&seccion=membresia&<?= (int)getProp($m, 'estado') === 1 ? 'eliminar' : 'activar' ?>=<?= getProp($m, 'id') ?>')">
                <i class="fa-solid <?= (int)getProp($m, 'estado') === 1 ? 'fa-user-slash' : 'fa-user-plus' ?>"></i>
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>

<div class="modal" id="modal" style="display:none;">
    <div class="modal-box">
        <h3 id="tituloModal"><i class="fa-solid fa-user-plus"></i> Gestionar Miembro</h3>
        <form method="POST" id="formMiembro" action="index.php?vista=dashboard&seccion=membresia">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
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
                            <option value="<?= getProp($t, 'id') ?>"><?= htmlspecialchars(getProp($t, 'nombre')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cargos / Funciones:</label>
                    <select name="cargos[]" id="cargos_select" class="form-control" multiple="multiple" style="width: 100%;">
                        <?php foreach($cargos as $c): ?>
                            <option value="<?= getProp($c, 'id') ?>"><?= htmlspecialchars(getProp($c, 'nombre')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Condición:</label>
                    <select name="condicion_id" required>
                        <option value="">Seleccione Condición</option>
                        <?php foreach($condiciones as $con): ?>
                            <option value="<?= getProp($con, 'id') ?>"><?= htmlspecialchars(ucfirst(getProp($con, 'nombre'))) ?></option>
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
                    <div class="campo-mapa" style="display: flex; gap:10px;">
                        <input type="text" name="direccion" placeholder="Calle, Jr o Av." style="flex-grow: 1;">
                        <button type="button" class="btn-mapa" onclick="abrirMapa()" style="padding: 10px; background:#4f6ef7; color:white; border:none; border-radius:5px; cursor:pointer;">
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
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<div id="modalMapa" class="modal-mapa">
    <div class="modal-mapa-box">
        <h3><i class="fa-solid fa-map-location-dot"></i> Seleccionar Ubicación</h3>
        <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 10px;">Usa el buscador o haz clic en el mapa para marcar la ubicación exacta.</p>
        <div id="mapa-seleccionar"></div>
        <div style="display:flex; gap:10px; margin-top:20px; justify-content: flex-end;">
            <button type="button" onclick="cerrarModalMapa()" style="background:#dee2e6; color:#495057; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">Cancelar</button>
            <button type="button" onclick="confirmarUbicacion()" style="background:#4f6ef7; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:bold;">Guardar Ubicación</button>
        </div>

    </div>
</div>

<div id="customConfirm" class="modal-confirm">
    <div class="modal-confirm-box">
        <div class="modal-confirm-icon">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>

        <h3 class="modal-confirm-title">¿Estás seguro?</h3>
        
        <p id="confirmMessage" class="modal-confirm-text">
            ¿Realmente deseas cambiar el estado de este miembro?
        </p>
        
        <div class="modal-confirm-buttons">
            <button onclick="closeConfirm()" class="btn-cancel">
                Cancelar
            </button>
            <button id="btnConfirmAction" class="btn-confirm">
                Sí, confirmar
            </button>
        </div>
    </div>
</div>

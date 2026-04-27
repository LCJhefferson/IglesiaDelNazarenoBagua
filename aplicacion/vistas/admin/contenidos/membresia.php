<?php
require_once __DIR__ . '/../../../core/Autoload.php';
use aplicacion\controladores\MiembroController;

$controller = new MiembroController();
$controller->manejarPeticion(); 

$miembros = $controller->listarMiembros();
$cargos = $controller->obtenerCargos();
$condiciones = $controller->obtenerCondiciones();

// Estadísticas filtradas por estado activo (1)
$activos = count(array_filter($miembros, fn($m) => (int)($m['estado'] ?? 1) === 1));
$inactivos = count(array_filter($miembros, fn($m) => (int)($m['estado'] ?? 1) === 0));

// Lógica de cargos: ID 1 = Pastor, ID 2 = Líder, ID 3 = Miembro
$pastores = count(array_filter($miembros, fn($m) => ((int)$m['cargo_id'] === 1 && (int)$m['estado'] === 1)));
$lideres = count(array_filter($miembros, fn($m) => ((int)$m['cargo_id'] === 2 && (int)$m['estado'] === 1)));

$fechaHoy = date('Y-m-d'); 
?>

<div class="header">
    <h2><i class="fa-solid fa-users"></i> Gestión de Miembros</h2>
    <button class="nuevo" onclick="abrirModal()">
        <i class="fa-solid fa-user-plus"></i> Nuevo Miembro
    </button>
</div>

<div class="cards">
    <div class="card" style="border-left: 5px solid #28a745;">
        <i class="fa-solid fa-user-check icono" style="color: #28a745;"></i>
        <div><h3><?= $activos ?></h3><p>Miembros Activos</p></div>
    </div>
    <div class="card" style="border-left: 5px solid #dc3545;">
        <i class="fa-solid fa-user-xmark icono" style="color: #dc3545;"></i>
        <div><h3><?= $inactivos ?></h3><p>Miembros Inactivos</p></div>
    </div>
    <div class="card" style="border-left: 5px solid #007bff;">
        <i class="fa-solid fa-user-tie icono" style="color: #007bff;"></i>
        <div><h3><?= $pastores ?></h3><p>Pastores Activos</p></div>
    </div>
    <div class="card" style="border-left: 5px solid #ffc107;">
        <i class="fa-solid fa-star icono" style="color: #ffc107;"></i>
        <div><h3><?= $lideres ?></h3><p>Líderes Activos</p></div>
    </div>
</div>

<div style="background: #fff; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
    <div style="flex: 1; min-width: 200px;">
        <input class="buscador" type="text" id="buscar" onkeyup="filtrarTabla()" placeholder="Buscar por nombre..." style="width: 100%;">
    </div>
    
    <select id="filtroEstado" onchange="filtrarTabla()" style="padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; font-weight: bold;">
        <option value="1">Solo Activos</option>
        <option value="0">Solo Inactivos</option>
        <option value="">Todos</option>
    </select>

    <select id="filtroRol" onchange="filtrarTabla()" style="padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <option value="">Todos los Roles</option>
        <?php foreach($cargos as $c): ?>
            <option value="<?= htmlspecialchars($c['nombre']) ?>"><?= htmlspecialchars(ucfirst($c['nombre'])) ?></option>
        <?php endforeach; ?>
    </select>

    <select id="filtroCondicion" onchange="filtrarTabla()" style="padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <option value="">Todas las Condiciones</option>
        <?php foreach($condiciones as $con): ?>
            <option value="<?= htmlspecialchars($con['nombre']) ?>"><?= htmlspecialchars(ucfirst($con['nombre'])) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Rol</th>
            <th>Condición</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="tablaCuerpo">
        <?php foreach($miembros as $m): ?>
        <tr data-estado="<?= $m['estado'] ?>">
            <td><?= htmlspecialchars(($m['nombres'] ?? '') . " " . ($m['apellidos'] ?? '')) ?></td>
            <td><?= htmlspecialchars($m['telefono'] ?? '') ?></td>
            <td class="col-rol">
                <?php 
                    // Asignación de clase CSS según el ID del cargo
                    $claseBadge = 'discipulo'; 
                    if($m['cargo_id'] == 1) $claseBadge = 'pastor';
                    if($m['cargo_id'] == 2) $claseBadge = 'lider';
                    if($m['cargo_id'] == 3) $claseBadge = 'miembro';
                ?>
                <span class="badge <?= $claseBadge ?>">
                    <?= htmlspecialchars($m['cargo_nombre'] ?? 'Miembro') ?>
                </span>
            </td>
            <td class="col-condicion"><?= htmlspecialchars($m['condicion_nombre'] ?? 'saludable') ?></td>
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
                
                <?php if((int)($m['estado'] ?? 1) === 1): ?>
                    <a class="btn eliminar" href="dashboard.php?vista=membresia&eliminar=<?= $m['id'] ?>" onclick="return confirm('¿Desactivar miembro?')">
                        <i class="fa-solid fa-user-slash"></i>
                    </a>
                <?php else: ?>
                    <a class="btn" href="dashboard.php?vista=membresia&activar=<?= $m['id'] ?>" style="background: #28a745; color: white; padding: 5px 8px; border-radius: 4px;">
                        <i class="fa-solid fa-user-plus"></i>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="modal" id="modal">
    <div class="modal-box">
        <h3 id="tituloModal"><i class="fa-solid fa-user-plus"></i> Gestionar Miembro</h3>
        <form method="POST">
            <input type="hidden" name="id">
            <div class="grid">
                <input type="text" name="nombres" placeholder="Nombres" required>
                <input type="text" name="apellidos" placeholder="Apellidos" required>
                <input type="text" name="telefono" placeholder="Teléfono">
                <input type="date" name="fecha_nacimiento" value="<?= $fechaHoy ?>">

                <select name="cargo_id" required>
                    <option value="">Seleccione Rol</option>
                    <?php foreach($cargos as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars(ucfirst($c['nombre'])) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="condicion_id" required>
                    <option value="">Seleccione Condición</option>
                    <?php foreach($condiciones as $con): ?>
                        <option value="<?= $con['id'] ?>"><?= htmlspecialchars(ucfirst($con['nombre'])) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="estado" id="inputEstado">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>

                <div class="campo-mapa" style="grid-column: span 2;">
                    <input type="text" name="direccion" placeholder="Dirección" style="width: 100%;">
                    <button type="button" class="btn-mapa" onclick="abrirMapa()">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </button>
                </div>

                <input type="text" name="latitud" id="latitud" placeholder="Latitud (Auto)">
                <input type="text" name="longitud" id="longitud" placeholder="Longitud (Auto)">
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" name="registrar" id="btnAgregar" class="nuevo">Agregar miembro</button>
                <button type="submit" name="editar" id="btnActualizar" class="nuevo" style="display:none; background:#007bff;">Actualizar</button>
                <button type="button" onclick="cerrarModal()" style="background:#dee2e6; color:#495057;">Cancelar</button>
            </div>
        </form>
    </div>
</div>
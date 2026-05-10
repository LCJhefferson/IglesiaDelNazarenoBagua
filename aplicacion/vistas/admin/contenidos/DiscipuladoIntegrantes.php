<?php
use aplicacion\controladores\DiscipuladoController;

$controller = new DiscipuladoController();
$controller->manejarPeticion();

$datos = $controller->obtenerDatosVista('DiscipuladoIntegrantes');

// Aseguramos que siempre sean arreglos, incluso si el DAO falla
$integrantes = $datos['integrantes'] ?? [];
$todos_miembros = $datos['todos_miembros'] ?? []; 
$todos_grupos = $datos['todos_grupos'] ?? [];    
$discipuladores = $datos['discipuladores'] ?? []; 

$total_integrantes = count($integrantes);
?>

<link rel="stylesheet" href="public/css/Discipulado.css">

<div class="gestion-container">
    <div class="header-section">
        <div>
            <h2><i class="fas fa-user-graduate"></i> Integrantes de Discipulado</h2>
            <p style="color: #6b7a99; font-size: 0.9rem; margin-top: 5px;">
                Mostrando <span id="filasMostradas"><?= $total_integrantes ?></span> de <?= $total_integrantes ?> registros
            </p>
        </div>
        <button class="btn-nuevo" onclick="abrirModalAsignar()">
            <i class="fas fa-user-plus"></i> Asignar Miembro a Grupo
        </button>
    </div>

    <div class="filter-bar">
        <div style="flex: 3; position: relative;">
            <input type="text" id="inputBusq" 
                class="search-input"
                placeholder="Buscar integrante por nombre..." 
                oninput="filtrarTablaIntegrantes()">
        </div>
        
        <div class="select-group">
            <select id="filtroNivel" onchange="filtrarTablaIntegrantes()">
                <option value="todos">Todos los Niveles</option>
                <option value="I">Nivel I</option>
                <option value="II">Nivel II</option>
                <option value="III">Nivel III</option>
            </select>

            <select id="filtroLider" onchange="filtrarTablaIntegrantes()">
                <option value="todos">Todos los Discipuladores</option>
                <?php foreach($discipuladores as $d): ?>
                    <option value="<?= $d['id'] ?>">
                        <?= htmlspecialchars($d['nombre'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="tabla-container">
        <table class="tabla-moderna" id="tablaIntegrantes">
            <thead>
                <tr>
                    <th>Nombre del Integrante</th>
                    <th>Grupo Asignado</th>
                    <th>Nivel</th>
                    <th>Discipulador</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTablaIntegrantes">
                <?php if (empty($integrantes)): ?>
                    <tr class="no-data-row">
                        <td colspan="5" class="no-data-table" style="text-align:center; padding:30px;">No hay integrantes asignados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($integrantes as $i): 
                        // Limpiamos los datos para evitar errores de null
                        $nombreMiembro = $i['miembro_nombre'] ?? 'Sin Nombre';
                        $nombreGrupo   = $i['grupo_nombre'] ?? 'Sin Grupo';
                        $nivelGrupo    = $i['grupo_nivel'] ?? '-';
                        $idLider       = $i['discipulador_id'] ?? '';
                        $nombreLider   = $i['discipulador_nombre'] ?? 'No asignado';
                        $relacionId    = $i['relacion_id'] ?? 0;
                    ?>
                    <tr class="fila-integrante" 
                        data-nombre="<?= strtolower(htmlspecialchars($nombreMiembro)) ?>"
                        data-nivel="<?= htmlspecialchars($nivelGrupo) ?>"
                        data-lider="<?= htmlspecialchars($idLider) ?>">
                        
                        <td>
                            <div class="user-info">
                                <div class="avatar-circle"><?= strtoupper(substr($nombreMiembro, 0, 1)) ?></div>
                                <span><?= htmlspecialchars($nombreMiembro) ?></span>
                            </div>
                        </td>
                        <td><span class="badge-grupo-name"><?= htmlspecialchars($nombreGrupo) ?></span></td>
                        <td><span class="badge-nivel-small"><?= htmlspecialchars($nivelGrupo) ?></span></td>
                        <td>
                            <span class="lider-name">
                                <i class="fas fa-chalkboard-teacher"></i> <?= htmlspecialchars($nombreLider) ?>
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <a href="dashboard?seccion=DiscipuladoIntegrantes&quitar_integrante=<?= $relacionId ?>" 
                               class="btn-remove" 
                               onclick="return confirm('¿Quitar a este miembro del grupo?')">
                                <i class="fas fa-user-minus"></i> Quitar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalAsignar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-link"></i> Vincular Miembro a Grupo</h3>
            <span class="close-modal" onclick="cerrarModalAsignar()">&times;</span>
        </div>

        <form method="POST" action="dashboard?seccion=DiscipuladoIntegrantes">
            <div class="modal-body-unified">
                <div class="form-group">
                    <label><i class="fas fa-users"></i> 1. Busque y Seleccione los Miembros</label>
                    <select name="miembro_id[]" class="select2-buscable" multiple="multiple" required style="width:100%">
                        <?php foreach($todos_miembros as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="position: relative;">
                    <label><i class="fas fa-layer-group"></i> 2. Escriba o seleccione el Grupo de Destino</label>
                    <input type="text" id="buscarGrupoInput" class="form-select-standard" placeholder="Escriba el nombre del grupo..." autocomplete="off" required>
                    <div id="listaGruposResultados" class="custom-dropdown-results">
                        <?php foreach($todos_grupos as $g): ?>
                            <div class="grupo-item" data-id="<?= $g['id'] ?>">
                                <?= htmlspecialchars($g['nombre'] ?? '') ?> <span>(Nivel <?= htmlspecialchars($g['nivel'] ?? '') ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="grupo_id" id="grupo_id_real">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="cerrarModalAsignar()">Cancelar</button>
                    <button type="submit" name="asignar_integrante" class="btn-save">Vincular al Grupo</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="public/js/DiscipuladoIntegrantes.js"></script>
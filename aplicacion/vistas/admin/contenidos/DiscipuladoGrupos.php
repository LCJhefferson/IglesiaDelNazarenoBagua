<?php
use aplicacion\controladores\DiscipuladoController;
use aplicacion\core\Middleware;

$controller = new DiscipuladoController();

$controller->manejarPeticion();

$datos = $controller->obtenerDatosVista('DiscipuladoGrupos');
$grupos = $datos['grupos']; 
$discipuladores = $datos['discipuladores'];
$estados = $datos['estados'];

// NOTA: Si esta línea te sigue dando error de "Undefined method", 
// cámbiala temporalmente por: $csrfToken = '';
$csrfToken = Middleware::csrfGenerate();
?>

<link rel="stylesheet" href="css/DiscipuladoGrupos.css">

<div class="gestion-container">
    <div class="header-section">
        <div>
            <h2><i class="fas fa-users-cog"></i> Gestión de Grupos</h2>
            <p style="color: #6b7a99; font-size: 0.9rem;">Administra los grupos de estudio y sus niveles.</p>
        </div>
        <button class="btn-nuevo" onclick="abrirModalGrupo()">
            <i class="fas fa-plus"></i> Crear Nuevo Grupo
        </button>
    </div>

    <div class="filter-bar">
        <input type="text" id="buscarGrupo" onkeyup="filtrarGrupos()" placeholder="Buscar por nombre de grupo...">
        
        <select id="filtroNivel" onchange="filtrarGrupos()">
            <option value="">Todos los Niveles</option>
            <option value="I">Nivel I</option>
            <option value="II">Nivel II</option>
            <option value="III">Nivel III</option>
        </select>
        
        <select id="filtroDiscipulador" onchange="filtrarGrupos()">
            <option value="">Todos los Discipuladores</option>
            <?php foreach($discipuladores as $d): ?>
                <option value="<?= htmlspecialchars($d->nombres ?? '') ?>">
                    <?= htmlspecialchars(($d->nombres ?? '') . ' ' . ($d->apellidos ?? '')) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="grupos-grid" id="contenedorGrupos">
        <?php if ($grupos->isEmpty()): ?>
            <div class="no-data">No se encontraron grupos registrados.</div>
        <?php else: ?>
            <?php foreach ($grupos as $g): ?>
                <div class="card-grupo" 
                     data-nombre="<?= strtolower(htmlspecialchars($g->nombre ?? '')) ?>" 
                     data-nivel="<?= htmlspecialchars($g->nivel ?? '') ?>"
                     data-lider="<?= strtolower(htmlspecialchars($g->discipulador ? ($g->discipulador->nombres ?? '') : '')) ?>">
                    
                    <div class="card-header">
                        <span class="badge-nivel">Nivel <?= htmlspecialchars($g->nivel ?? '-') ?></span>
                        <span class="badge-estado status-<?= strtolower($g->estado ? ($g->estado->nombre ?? 'inactivo') : 'inactivo') ?>">
                            <?= htmlspecialchars($g->estado ? ($g->estado->nombre ?? 'N/A') : 'N/A') ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <h3><?= htmlspecialchars($g->nombre ?? 'Sin nombre') ?></h3>

                        <p class="discipulador">
                            <i class="fas fa-user-tie"></i> 
                            <?= ($g->discipulador) ? htmlspecialchars(($g->discipulador->nombres ?? '') . ' ' . ($g->discipulador->apellidos ?? '')) : 'Sin líder' ?>
                        </p>
                        <div class="info-stats">
                            <span><i class="fas fa-users"></i> <?= $g->integrantes_count ?? 0 ?> Integrantes</span>
                        </div>
                    </div>

                    <div class="card-actions">
                        <button class="btn-edit" onclick='editarGrupo(<?= json_encode($g) ?>)'>
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        
                        <a href="?seccion=DiscipuladoGrupos&eliminar_grupo=<?= $g->id ?>" 
                           class="btn-delete" 
                           onclick="return confirm('¿Está seguro de eliminar este grupo?')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div id="modalGrupo" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Configuración de Grupo</h3>
            <span class="close-modal" onclick="cerrarModalGrupo()">&times;</span>
        </div>

        <form id="formGrupo" action="?seccion=DiscipuladoGrupos" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="id" id="grupo_id">
            
            <div class="modal-body-unified">
                <div class="form-group">
                    <label>Nombre del Grupo</label>
                    <input type="text" name="nombre" id="nombre_grupo" placeholder="Ej. Célula Norte 1" required>
                </div>

                <div class="form-row" style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Nivel de Estudio</label>
                        <select name="nivel" id="nivel_grupo" required>
                            <option value="I">Nivel I</option>
                            <option value="II">Nivel II</option>
                            <option value="III">Nivel III</option>
                        </select>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label>Estado del Grupo</label>
                        <select name="estado_id" id="estado_id" required>
                            <?php foreach($estados as $e): ?>
                                <option value="<?= $e->id ?>"><?= htmlspecialchars($e->nombre ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Líder Encargado (Discipulador)</label>                    
                    <select name="discipulador_id" id="discipulador_id"  required style="width: 100%;">
                        <option value="">Seleccione un líder...</option>
                        <?php foreach($discipuladores as $d): ?>
                            <option value="<?= $d->id ?>">
                                <?= htmlspecialchars(($d->nombres ?? '') . ' ' . ($d->apellidos ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="cerrarModalGrupo()">Cancelar</button>
                    <button type="submit" name="registrar_grupo" id="btnGuardarAction" class="btn-save">Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>
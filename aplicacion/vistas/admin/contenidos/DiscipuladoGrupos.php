<?php
use aplicacion\controladores\DiscipuladoController;

$controller = new DiscipuladoController();
// Procesar cualquier post/get antes de renderizar
$controller->manejarPeticion();

// Obtener datos específicos para esta sección
$datos = $controller->obtenerDatosVista('DiscipuladoGrupos');
$grupos = $datos['grupos'];
$discipuladores = $datos['discipuladores'];
$estados = $datos['estados'];
?>

<link rel="stylesheet" href="public/css/Discipulado.css">

<div class="gestion-container">
    <div class="header-section">
        <h2><i class="fas fa-users-cog"></i> Gestión de Grupos</h2>
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
                <option value="<?= htmlspecialchars($d['nombre']) ?>"><?= htmlspecialchars($d['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="grupos-grid">
        <?php if (empty($grupos)): ?>
            <div class="no-data">No se encontraron grupos registrados.</div>
        <?php else: ?>
            <?php foreach ($grupos as $g): ?>
                <div class="card-grupo">
                    <div class="card-header">
                        <span class="badge-nivel">Nivel <?= htmlspecialchars($g['nivel']) ?></span>
                        <span class="badge-estado status-<?= strtolower($g['estado_nombre']) ?>">
                            <?= htmlspecialchars($g['estado_nombre']) ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <h3><?= htmlspecialchars($g['nombre']) ?></h3>
                        <p class="discipulador">
                            <i class="fas fa-user-tie"></i> <?= htmlspecialchars($g['discipulador_nombre']) ?>
                        </p>
                        <div class="info-stats">
                            <span><i class="fas fa-users"></i> <?= $g['num_integrantes'] ?> Integrantes</span>
                        </div>
                    </div>

                    <div class="card-actions">
                        <button class="btn-edit" onclick='editarGrupo(<?= json_encode($g) ?>)'>
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <a href="dashboard?seccion=DiscipuladoGrupos&eliminar_grupo=<?= $g['id'] ?>" 
                           class="btn-delete" 
                           onclick="return confirm('¿Eliminar grupo?')">
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

        <form id="formGrupo" action="dashboard?seccion=DiscipuladoGrupos" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
            <input type="hidden" name="id" id="grupo_id">
            
            <div class="modal-body-unified">
                <div class="form-group">
                    <label>Nombre del Grupo</label>
                    <input type="text" name="nombre" id="nombre_grupo" placeholder="Ej. Célula Norte 1" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nivel de Estudio</label>
                        <select name="nivel" id="nivel_grupo">
                            <option value="I">Nivel I</option>
                            <option value="II">Nivel II</option>
                            <option value="III">Nivel III</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado_id" id="estado_id">
                            <?php foreach($estados as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= $e['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Líder Encargado (Discipulador)</label>
                    <select name="discipulador_id" id="discipulador_id" class="select" required style="width: 100%;">
                        <option value="">Seleccione un líder...</option>
                        <?php foreach($discipuladores as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="cerrarModalGrupo()">Cancelar</button>
                    <button type="submit" name="registrar_grupo" id="btnGuardarAction" class="btn-save">Guardar Grupo</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="public/js/DiscipuladoGrupos.js"></script>
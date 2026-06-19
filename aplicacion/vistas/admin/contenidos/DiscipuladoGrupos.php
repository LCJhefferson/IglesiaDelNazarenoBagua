<?php
use aplicacion\controladores\DiscipuladoController;
use aplicacion\core\Middleware;

$controller = new DiscipuladoController();

$controller->manejarPeticion();

$datos = $controller->obtenerDatosVista('DiscipuladoGrupos');
$grupos = $datos['grupos']; 

// CORRECCIÓN DEL ARREGLO NUEVO DE TU CONTROLADOR
$discipuladores_filtros = $datos['discipuladores_filtros'];
$discipuladores_todos   = $datos['discipuladores_todos'];

$estados = $datos['estados'];
$csrfToken = Middleware::csrfGenerate();
?>

<link rel="stylesheet" href="css/DiscipuladoGrupos.css">

<header class="barra-superior">
    <div class="barra-info">
        <h1><i class="fas fa-users-cog"></i>Gestión de Grupos</h1>
        <p>Administra los grupos de discipulado, niveles de formación y responsables.</p>
    </div>
    <div class="barra-acciones">
        <button class="boton boton-primario" onclick="abrirModalGrupo()">
            <i class="fas fa-plus"></i>
            Crear Nuevo Grupo
        </button>
    </div>
</header>

<div class="gestion-container">
    <div class="filter-bar" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <input type="text" id="buscarGrupo" onkeyup="filtrarGrupos()" placeholder="Buscar por nombre de grupo...">
        
        <select id="filtroNivel" onchange="filtrarGrupos()">
            <option value="">Nivel</option>
            <option value="I">Nivel I</option>
            <option value="II">Nivel II</option>
            <option value="III">Nivel III</option>
        </select>
        
        <select id="filtroDiscipulador" onchange="filtrarGrupos()">
            <option value="">Discipulador</option>
            <?php foreach($discipuladores_filtros as $d): ?>
                <option value="<?= htmlspecialchars(($d->nombres ?? '') . ' ' . ($d->apellidos ?? '')) ?>">
                    <?= htmlspecialchars(($d->nombres ?? '') . ' ' . ($d->apellidos ?? '')) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select id="filtroEstado" onchange="filtrarGrupos()">
            <option value="">Estado</option>
            <?php foreach($estados as $e): ?>
                <option value="<?= strtolower(htmlspecialchars($e->nombre ?? '')) ?>">
                    <?= htmlspecialchars($e->nombre ?? '') ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="button" class="boton" style="background-color: #64748b; color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 0.88rem;" onclick="limpiarFiltros()">
            <i class="fas fa-undo"></i> Limpiar
        </button>
    </div>

    <div class="grupos-grid" id="contenedorGrupos">
        <?php if ($grupos->isEmpty()): ?>
            <div class="no-data">No se encontraron grupos registrados.</div>
        <?php else: ?>
            <?php foreach ($grupos as $g): ?>
               <div class="card-grupo" 
                    data-nombre="<?= strtolower(htmlspecialchars($g->nombre ?? '')) ?>" 
                    data-nivel="<?= htmlspecialchars($g->nivel ?? '') ?>"
                    data-lider="<?= strtolower(htmlspecialchars($g->discipulador ? (($g->discipulador->nombres ?? '') . ' ' . ($g->discipulador->apellidos ?? '')) : '')) ?>"
                    data-estado="<?= strtolower(htmlspecialchars($g->estado ? ($g->estado->nombre ?? '') : '')) ?>">
                    
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
                        
                        <button type="button" class="btn-delete" onclick="confirmarEliminarGrupo(<?= $g->id ?>, '<?= htmlspecialchars($g->nombre ?? 'este grupo') ?>')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
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
                    <select name="discipulador_id" id="discipulador_id" required style="width: 100%;">
                        <option value="">Seleccione un líder...</option>
                        <?php foreach($discipuladores_todos as $d): ?>
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

<div id="toastContainer" class="toast-container"></div>

<div id="modalConfirmarEliminar" class="modal">
    <div class="modal-content modal-confirm-content">
        <div style="color: #ef4444; font-size: 2.5rem; margin-bottom: 10px;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h3 style="margin: 0 0 8px 0; color: #0f172a; font-size: 1.25rem;">¿Eliminar Grupo?</h3>
        <p style="color: #64748b; font-size: 0.88rem; line-height: 1.4; margin: 0 0 20px 0;">
            Estás a punto de eliminar el grupo <strong id="nombreGrupoEliminar" style="color: #0f172a;"></strong>.<br>
            <span style="color: #ef4444; font-weight: 500;">Se desvincularán los integrantes automáticamente.</span>
        </p>
        
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" class="btn-cancel" onclick="cerrarModalEliminar()">Cancelar</button>
            <a id="enlaceEliminarSeguro" href="#" class="btn-save" style="background-color: #ef4444; text-decoration: none; display: inline-flex; align-items: center;">
                Sí, Eliminar
            </a>
        </div>
    </div>
</div>

<script src="js/DiscipuladoGrupos.js"></script>

<?php if (isset($_GET['status'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const status = "<?= htmlspecialchars($_GET['status']) ?>";
        
        if (status === 'creado') {
            mostrarNotificacion('¡Grupo creado correctamente!', 'success');
        } else if (status === 'actualizado') {
            mostrarNotificacion('¡Configuración actualizada correctamente!', 'success');
        } else if (status === 'eliminado') {
            mostrarNotificacion('El grupo ha sido eliminado de forma correcta.', 'info');
        } else if (status === 'error') {
            mostrarNotificacion('Hubo un problema al procesar la solicitud.', 'error');
        }
    });
</script>
<?php endif; ?>
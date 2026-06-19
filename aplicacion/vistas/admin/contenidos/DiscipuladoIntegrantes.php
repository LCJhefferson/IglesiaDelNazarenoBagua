<?php
use aplicacion\controladores\DiscipuladoController;
use aplicacion\core\Middleware;

$controller = new DiscipuladoController();
$controller->manejarPeticion();

$datos = $controller->obtenerDatosVista('DiscipuladoIntegrantes');

$integrantes = $datos['integrantes']; 
$todos_miembros = $datos['todos_miembros'];
$todos_grupos = $datos['todos_grupos'];
$discipuladores = $datos['discipuladores'];
$estados_alumno = $datos['estados_alumno']; // Traemos los estados individuales

$total_integrantes = $integrantes->count();
$csrfToken = Middleware::csrfGenerate();
?>

<link rel="stylesheet" href="public/css/Discipulado.css">

<header class="barra-superior">
    <div class="barra-info">
        <h1><i class="fas fa-user-graduate"></i>Integrantes de Discipulado</h1>
        <p>Gestiona la asignación y seguimiento de los miembros en los grupos de discipulado.</p>
    </div>
    <div class="barra-acciones">
        <div class="badge-info">
            <i class="fas fa-users"></i>
            <span id="filasMostradas"><?= $total_integrantes ?></span>
            integrantes registrados
        </div>
        <button class="boton boton-primario" onclick="abrirModalAsignar()">
            <i class="fas fa-user-plus"></i>
            Asignar Miembro a Grupo
        </button>
    </div>
</header>

<div class="gestion-container">
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
                    <option value="<?= $d->id ?>">
                        <?= htmlspecialchars($d->nombres . ' ' . $d->apellidos) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="filtroEstado" onchange="filtrarTablaIntegrantes()">
                <option value="todos">Todos los Estados</option>
                <?php foreach ($estados_alumno as $est): ?>
                    <option value="<?= $est->id; ?>"><?= htmlspecialchars($est->nombre); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="button" class="btn-cancel" style="padding: 8px 15px; display: inline-flex; align-items: center; gap: 5px; height: 100%;" onclick="limpiarFiltrosIntegrantes()">
                <i class="fas fa-undo-alt"></i> Limpiar
            </button>
        </div>
    </div>

    <div class="tabla-container">
        <table class="tabla-moderna" id="tablaIntegrantes">
            <thead>
                <tr>
                    <th>Nombre del Integrante</th>
                    <th>Grupo Asignado</th>
                    <th>Nivel</th>
                    <th>Estado Alumno</th>
                    <th>Discipulador</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTablaIntegrantes">
                <?php if ($integrantes->isEmpty()): ?>
                    <tr class="no-data-row">
                        <td colspan="6" class="no-data-table" style="text-align:center; padding:30px;">No hay integrantes asignados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($integrantes as $i): 
                        $nombreMiembro = $i->miembro->nombres . ' ' . $i->miembro->apellidos;
                        $nombreGrupo   = $i->grupo->nombre ?? 'Sin Grupo';
                        $nivelGrupo    = $i->grupo->nivel ?? '-';
                        $idLider       = $i->grupo->discipulador_id ?? '';
                        $nombreLider   = $i->grupo->discipulador ? ($i->grupo->discipulador->nombres . ' ' . $i->grupo->discipulador->apellidos) : 'No asignado';
                        
                        // Obtenemos los datos del estado del alumno
                        $idEstadoAlumno = $i->estado_discipulo_id ?? 1;
                        $nombreEstadoAlumno = $i->estadoAlumno->nombre ?? 'En Proceso';
                    ?>
                    <tr class="fila-integrante" 
                        data-nombre="<?= strtolower(htmlspecialchars($nombreMiembro)) ?>"
                        data-nivel="<?= htmlspecialchars($nivelGrupo) ?>"
                        data-lider="<?= htmlspecialchars($idLider) ?>"
                        data-estado="<?= htmlspecialchars($idEstadoAlumno) ?>">
                        
                        <td>
                            <div class="user-info">
                                <div class="avatar-circle"><?= strtoupper(substr($i->miembro->nombres, 0, 1)) ?></div>
                                <span><?= htmlspecialchars($nombreMiembro) ?></span>
                            </div>
                        </td>
                        <td><span class="badge-grupo-name"><?= htmlspecialchars($nombreGrupo) ?></span></td>
                        <td><span class="badge-nivel-small"><?= htmlspecialchars($nivelGrupo) ?></span></td>
                        <td>
                            <span class="badge-status state-<?= $idEstadoAlumno ?>"><?= htmlspecialchars($nombreEstadoAlumno) ?></span>
                        </td>
                        <td>
                            <span class="lider-name">
                                <i class="fas fa-chalkboard-teacher"></i> <?= htmlspecialchars($nombreLider) ?>
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <button type="button" class="btn-edit" style="padding: 5px 10px; font-size: 13px;"
                                        onclick="abrirModalEstadoAlumno(<?= $i->id ?>, <?= $idEstadoAlumno ?>, '<?= htmlspecialchars($nombreMiembro) ?>')">
                                    <i class="fas fa-user-cog"></i> Estado
                                </button>

                                <button type="button" class="btn-remove" 
                                        onclick="confirmarQuitarIntegrante(<?= $i->id ?>, '<?= htmlspecialchars($nombreMiembro, ENT_QUOTES) ?>')">
                                    <i class="fas fa-user-minus"></i> Quitar
                                </button>
                            </div>
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
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="modal-body-unified">
                <div class="form-group">
                    <label><i class="fas fa-users"></i> 1. Busque y Seleccione los Miembros</label>
                    <select name="miembro_id[]" class="select2-buscable" multiple="multiple" required style="width:100%">
                        <?php foreach($todos_miembros as $m): ?>
                            <option value="<?= $m->id ?>"><?= htmlspecialchars($m->nombres . ' ' . $m->apellidos) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="position: relative;">
                    <label><i class="fas fa-layer-group"></i> 2. Seleccione el Grupo de Destino</label>
                    <input type="text" id="buscarGrupoInput" class="form-select-standard" placeholder="Escriba el nombre del grupo..." autocomplete="off" required>
                    <div id="listaGruposResultados" class="custom-dropdown-results">
                        <?php foreach($todos_grupos as $g): ?>
                            <div class="grupo-item" data-id="<?= $g->id ?>">
                                <?= htmlspecialchars($g->nombre) ?> <span>(Nivel <?= htmlspecialchars($g->nivel) ?>)</span>
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

<div id="modalEstadoAlumno" class="modal">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3><i class="fas fa-user-cog"></i> Estado del Discípulo</h3>
            <span class="close-modal" onclick="cerrarModalEstadoAlumno()">&times;</span>
        </div>

        <form method="POST" action="dashboard?seccion=DiscipuladoIntegrantes">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="integrante_id" id="modal_integrante_id">
            
            <div class="modal-body-unified">
                <div class="form-group">
                    <label>Discípulo:</label>
                    <input type="text" id="modal_alumno_nombre" class="form-select-standard" readonly style="background-color: #f8fafc; font-weight: 600; color:#334155;">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-info-circle"></i> Nuevo Estado del Alumno</label>
                    <select name="estado_discipulo_id" id="modal_estado_select" class="form-select-standard" required>
                        <?php foreach($estados_alumno as $est): ?>
                            <option value="<?= $est->id ?>"><?= htmlspecialchars($est->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-footer" style="margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="cerrarModalEstadoAlumno()">Cancelar</button>
                    <button type="submit" name="actualizar_estado_integrante" class="btn-save">Actualizar Estado</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="modalConfirmarQuitar" class="modal">
    <div class="modal-content" style="max-width: 420px; text-align: center;">
        <div class="modal-header" style="justify-content: center; border-bottom: none; padding-bottom: 10px;">
            <h3 style="flex-direction: column; gap: 8px;">
                <i class="fas fa-exclamation-triangle" style="color: #ef4444; background: #fef2f2; font-size: 1.8rem; padding: 15px; border-radius: 50px;"></i>
                ¿Quitar del Grupo?
            </h3>
        </div>
        <div class="modal-body-unified" style="padding: 10px 35px 25px 35px;">
            <p style="color: #4a5568; font-size: 0.95rem; line-height: 1.5; margin: 0;">
                ¿Estás seguro de que deseas remover a <strong id="nombreIntegranteQuitar" style="color: #1a1f36;"></strong> de su grupo de discipulado asignado?
            </p>
        </div>
        <div class="modal-footer" style="background-color: #ffffff; border-top: none; justify-content: center; gap: 12px; padding-bottom: 30px;">
            <button type="button" class="btn-cancel" onclick="cerrarModalQuitar()" style="padding: 12px 24px;">Cancelar</button>
            <a id="enlaceQuitarSeguro" href="#" class="btn-save" style="background: linear-gradient(135deg, #e03131, #f03e3e); box-shadow: 0 4px 15px rgba(224, 49, 49, 0.3); text-decoration: none; padding: 12px 24px; display: inline-flex; align-items: center; justify-content: center;">
                Quitar
            </a>
        </div>
    </div>
</div>

<script src="public/js/DiscipuladoIntegrantes.js"></script>
<div id="toast-container" class="toast-container"></div>

<?php if (isset($_GET['status'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mapeo preciso según la acción detectada por el controlador PHP
        const estados = {
            'asignado': { 
                msg: '¡Vínculo establecido con éxito en el grupo de discipulado!', 
                tipo: 'success', 
                icono: 'fa-link' 
            },
            'actualizado': { 
                msg: '¡El estado del discípulo se actualizó correctamente!', 
                tipo: 'success', 
                icono: 'fa-user-check' 
            },
            'eliminado': { 
                msg: '¡El integrante fue removido del grupo de discipulado!', 
                tipo: 'success', 
                icono: 'fa-trash-alt' 
            },
            'error': { 
                msg: 'Hubo un error al procesar la solicitud. Intente nuevamente.', 
                tipo: 'error', 
                icono: 'fa-times-circle' 
            }
        };

        const statusKey = '<?= htmlspecialchars($_GET['status'], ENT_QUOTES) ?>';
        if (estados[statusKey]) {
            // Invoca la función global del Toast
            mostrarToastNotificacion(estados[statusKey].msg, estados[statusKey].tipo, estados[statusKey].icono);
            
            // Limpia los parámetros de la URL para evitar re-ejecuciones al recargar la página
            const nuevaUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search.replace(/&status=[^&]*/g, "");
            window.history.replaceState({ path: nuevaUrl }, '', nuevaUrl);
        }
    });
</script>
<?php endif; ?>
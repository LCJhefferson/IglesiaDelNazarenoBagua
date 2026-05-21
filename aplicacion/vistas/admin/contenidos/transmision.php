<?php
require_once __DIR__ . '/../../../../aplicacion/core/Autoload.php';

if (file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../../vendor/autoload.php';
}

use aplicacion\controladores\TransmisionController;
use aplicacion\config\Conexion;

$auth = new TransmisionController();
$auth->registrar();
$transmisiones = $auth->listarTransmisiones();

// Lógica de detección de vivo activo (ID 1 según nuevos requerimientos)
$preview = null;
$hayVivo = false;

foreach($transmisiones as $t) {
    if($t['estado_id'] == 1) { 
        $preview = $t; 
        $hayVivo = true;
        break; 
    }
}
// Si no hay vivo, el monitor y el formulario cargan vacíos (sin persistencia de basura)
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Transmisión - Iglesia del Nazareno</title>
    <link rel="stylesheet" href="/IglesiaDelNazarenoBagua/public/css/trasmision.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="contenedor">
    <div class="header">
        <div class="titulo"><i class="fa-solid fa-video"></i> Panel de Control de Transmisión</div>
        <div style="display:flex; gap:10px; align-items:center;">
            <button type="button" class="btn-nueva" onclick="prepararNueva()" style="background:#2563eb; color:white; border:none; padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:600;">
                <i class="fa-solid fa-plus"></i> Nueva Transmisión
            </button>
            <div class="badge <?= $hayVivo ? 'activo' : 'inactivo' ?>">
                <i class="fa-solid fa-circle <?= $hayVivo ? 'blink' : '' ?>"></i> 
                <?= $hayVivo ? 'En vivo ahora' : 'Sin transmisión activa' ?>
            </div>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <div class="video-container">
                <iframe id="monitorVideo" class="video" src="<?= $preview['link_video'] ?? '' ?>" allowfullscreen allow="autoplay" style="<?= empty($preview['link_video']) ? 'display:none;' : '' ?>"></iframe>
                <div id="monitorPlaceholder" style="<?= !empty($preview['link_video']) ? 'display:none;' : 'height:400px; background:#2d3748; display:flex; align-items:center; justify-content:center; color:white; border-radius:10px;' ?>">
                    <p><i class="fa-solid fa-video-slash fa-2x"></i><br>Sin vista previa</p>
                </div>
            </div>
            <div class="info">
                <h3 id="monitorTitulo"><?= htmlspecialchars($preview['titulo'] ?? 'Sin Transmisión Activa') ?></h3>
                <p id="monitorDesc"><?= htmlspecialchars($preview['descripcion'] ?? 'Cargue una nueva transmisión o edite la actual.') ?></p>
            </div>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-gear"></i> <span id="formActionTitle"><?= $hayVivo ? 'Control de Vivo' : 'Nueva Transmisión' ?></span></h3>
            <form id="formTransmision" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
                <input type="hidden" name="id_transmision" id="formId" value="<?= $preview['id'] ?? '' ?>">

                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="titulo" id="formTitulo" value="<?= htmlspecialchars($preview['titulo'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="formDesc" rows="3"><?= htmlspecialchars($preview['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>URL de YouTube</label>
                    <input type="text" name="link_video" id="formLink" value="<?= htmlspecialchars($preview['link_video'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado_id" id="formEstado">
                        <option value="1" <?= (isset($preview['estado_id']) && $preview['estado_id'] == 1) ? 'selected' : '' ?>>En Vivo</option>
                        <option value="2" <?= (isset($preview['estado_id']) && $preview['estado_id'] == 2) ? 'selected' : '' ?>>Finalizado</option>
                    </select>
                </div>

                <button type="button" id="btnAccionPrincipal" onclick="confirmarAccion()" class="btn btn-primary w-100" style="margin-top: 10px;">
                    <?= ($hayVivo) ? '<i class="fa-solid fa-save"></i> Guardar Cambios' : '<i class="fa-solid fa-play"></i> Iniciar y Notificar' ?>
                </button>
            </form>
        </div>
    </div>

    <div id="modalConfirmar" class="modal-simple" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; justify-content:center; align-items:center;">
        <div class="modal-contenido" style="background:white; padding:30px; border-radius:15px; max-width:400px; text-align:center;">
            <i class="fa-solid fa-triangle-exclamation fa-3x" style="color:#f59e0b; margin-bottom:15px;"></i>
            <h3>¿Confirmar acción?</h3>
            <p id="textoModal" style="margin: 15px 0; color: #4b5563;"></p>
            <div style="display:flex; gap:10px; justify-content:center;">
                <button id="btnConfirmarModal" class="btn btn-primary">Sí, confirmar</button>
                <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            </div>
        </div>
    </div>

    <div class="card-tabla">
        <h3>Historial de Transmisiones</h3>
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaTransmisiones">
                <?php foreach($transmisiones as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['titulo']) ?></strong></td>
                    <td>
                        <?php
                        $claseEstado = ($t['estado_id'] == 1) ? 'est-envivo' : 'est-finalizado';
                        $nombreEstado = ($t['estado_id'] == 1) ? 'En Vivo' : 'Finalizado';
                        ?>
                        <span class="badge-estado <?= $claseEstado ?>">
                            <i class="dot-estado"></i> <?= $nombreEstado ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($t['fecha'])) ?></td>
                    <td style="text-align:center;">
                        <div style="display: flex; justify-content: center; gap: 10px;">
                            <?php if($t['estado_id'] == 2): // Solo lápiz para Finalizadas ?>
                                <button title="Editar" class="btn-edit" onclick='cargarParaEditar(<?= json_encode($t) ?>)'>
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            <?php else: // Para la que está en vivo, botón de finalizar rápido ?>
                                <button title="Finalizar" class="btn-finalizar" 
                                        onclick="abrirModalFinalizar('<?= $t['id'] ?>', '<?= htmlspecialchars($t['titulo']) ?>')" 
                                        style="color: #ef4444; background: #fee2e2; border: none; padding: 5px 8px; border-radius: 5px; cursor: pointer;">
                                    <i class="fa-solid fa-circle-stop"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="/IglesiaDelNazarenoBagua/public/js/transmision.js"></script>

</body>
</html>
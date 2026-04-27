<?php
require_once __DIR__ . '/../../../../aplicacion/core/Autoload.php';
use aplicacion\controladores\TransmisionController;
use aplicacion\config\Conexion;

$auth = new TransmisionController();
$auth->registrar();
$transmisiones = $auth->listarTransmisiones();

$db = Conexion::conectar();
$estados_db = $db->query("SELECT * FROM estados_transmision")->fetchAll(PDO::FETCH_ASSOC);

$preview = null;
foreach($transmisiones as $t) {
    if($t['estado_id'] == 2) { $preview = $t; break; }
}
if(!$preview) $preview = $transmisiones[0] ?? null;
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
        <div class="titulo"><i class="fa-solid fa-video"></i> Panel de Control</div>
        <div class="badge <?= ($preview && $preview['estado_id'] == 2) ? 'activo' : 'inactivo' ?>">
            <i class="fa-solid fa-circle"></i> <?= ($preview && $preview['estado_id'] == 2) ? 'En vivo' : 'Desconectado' ?>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <div class="video-container">
                <iframe id="monitorVideo" class="video" src="<?= $preview ? $preview['link_video'] : '' ?>" allowfullscreen></iframe>
            </div>
            <div class="info">
                <h3><?= htmlspecialchars($preview['titulo'] ?? 'Sin Transmisión') ?></h3>
                <p><?= htmlspecialchars($preview['descripcion'] ?? 'No hay descripción disponible.') ?></p>
            </div>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-gear"></i> <span id="formActionTitle">Nueva Transmisión</span></h3>
            <form id="formTransmision" method="POST">
                <input type="hidden" name="id_transmision" id="formId">

                <div class="form-group">
                    <label>Título del Evento</label>
                    <input type="text" name="titulo" id="formTitulo" placeholder="Ej: Culto Dominical" required>
                </div>

                <div class="form-group">
                    <label>Descripción / Mensaje</label>
                    <textarea name="descripcion" id="formDesc" rows="3" placeholder="Bienvenidos hermanos..."></textarea>
                </div>

                <div class="form-group">
                    <label>URL de YouTube (Embed)</label>
                    <input type="text" name="link_video" id="formLink" placeholder="https://www.youtube.com/embed/..." required>
                </div>

                <div class="form-group" id="containerEstado">
                    <label>Estado Inicial</label>
                    <select name="estado_id" id="formEstado">
                        <?php foreach($estados_db as $est): ?>
                            <option value="<?= $est['id'] ?>"><?= $est['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="btn_guardar" class="btn">
                    <i class="fa-solid fa-save"></i> <span id="btnText">Iniciar y Guardar</span>
                </button>
                <button type="button" id="btnCancelar" class="btn" style="background:#6c757d; display:none;" onclick="resetForm()">Cancelar</button>
            </form>
        </div>
    </div>

    <div class="card-tabla" style="margin-top:30px;">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($transmisiones as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['titulo']) ?></strong></td>
                    <td><span class="badge-status <?= strtolower($t['estado_nombre']) ?>"><?= $t['estado_nombre'] ?></span></td>
                    <td><?= date('d/m/Y', strtotime($t['fecha'])) ?></td>
                    <td style="text-align:center;">
                        <?php if($t['estado_id'] == 2): ?>
                            <button class="btn-stop" onclick="finalizarTransmision('<?= $t['id'] ?>')"><i class="fa-solid fa-circle-stop"></i></button>
                        <?php endif; ?>
                        <button class="btn-edit" onclick="cargarParaEditar(<?= htmlspecialchars(json_encode($t)) ?>)"><i class="fa-solid fa-pen-to-square"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


</body>
</html>
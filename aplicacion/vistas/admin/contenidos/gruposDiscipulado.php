<?php
// aplicacion/vistas/admin/contenidos/gruposDiscipulado.php
require_once __DIR__ . '/../../../core/Autoload.php'; 
use aplicacion\controladores\GrupoController;

$controller = new GrupoController();
$grupos = $controller->listar();
?>

<div class="page-header">
    <div>
        <h1>Grupos de Discipulado</h1>
        <p>Administra los grupos y sus discipuladores</p>
    </div>
    <button class="btn btn-primary" onclick="abrirModal('modal-nuevo-grupo')">
        <i class="fa-solid fa-plus"></i> Nuevo Grupo
    </button>
</div>

<div class="grupos-grid">
    <?php if (empty($grupos)): ?>
        <p>No hay grupos registrados aún.</p>
    <?php else: ?>
        <?php foreach ($grupos as $g): ?>
            <div class="grupo-card" style="border-left: 5px solid <?= $g['color_nivel'] ?>;">
                <div class="grupo-card-header">
                    <div class="grupo-nombre"><?= htmlspecialchars($g['nombre']) ?></div>
                    <span class="badge">Nivel <?= $g['nivel'] ?></span>
                </div>
                </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
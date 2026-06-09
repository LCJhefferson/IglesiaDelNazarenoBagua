<?php
/**
 * ARCHIVO: aplicacion/vistas/admin/contenidos/inicioAdmin.php
 */

// Usamos el Manager de Eloquent para las consultas rápidas
use Illuminate\Database\Capsule\Manager as DB;

// 1. Obtener Estadísticas con Eloquent (Consultas limpias)
$stats = [
    'miembros' => DB::table('miembros')->count(),
    'grupos'   => DB::table('discipulado_grupos')->where('estado_id', '1')->count(),
    'visitas'  => DB::table('visitas')->where('estado_id', 1)->count(),
    'recursos' => DB::table('recursos')->count()
];

// 2. Obtener Próximas Visitas (Join con miembros)
$visitas = DB::table('visitas as v')
    ->join('miembros as m', 'v.miembro_id', '=', 'm.id')
    ->select('v.fecha_visita', 'v.motivo', 'm.nombres', 'm.apellidos')
    ->where('v.estado_id', 1)
    ->orderBy('v.fecha_visita', 'asc')
    ->limit(5)
    ->get();

$nombreUsuario = $_SESSION['usuario'] ?? 'Administrador';
?>

<div class="welcome-banner">
    <div class="welcome-text">
        <h1>¡Bienvenido de nuevo, <span><?php echo htmlspecialchars($nombreUsuario); ?></span>!</h1>
        <p>Resumen de actividad - Iglesia del Nazareno Bagua</p>
    </div>
    <div class="welcome-image">
        <i class="fas fa-church"></i>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon miembros"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['miembros']; ?></span>
            <span class="stat-label">Miembros</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon grupos"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['grupos']; ?></span>
            <span class="stat-label">Grupos de Discipulado</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon visitas"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['visitas']; ?></span>
            <span class="stat-label">Visitas Pendientes</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon recursos"><i class="fas fa-folder-open"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['recursos']; ?></span>
            <span class="stat-label">Recursos Compartidos</span>
        </div>
    </div>
</div>

<div class="recent-activity">
    <h3>Próximas Visitas Programadas</h3>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Miembro</th>
                <th>Fecha</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($visitas->isEmpty()): ?>
                <tr><td colspan="3">No hay visitas pendientes en la base de datos.</td></tr>
            <?php else: foreach ($visitas as $v): ?>
                <tr>
                    <td><?php echo htmlspecialchars($v->nombres . " " . $v->apellidos); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($v->fecha_visita)); ?></td>
                    <td><?php echo htmlspecialchars($v->motivo); ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
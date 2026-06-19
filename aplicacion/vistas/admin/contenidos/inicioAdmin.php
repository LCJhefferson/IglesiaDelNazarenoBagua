<?php
/**
 * ARCHIVO: aplicacion/vistas/admin/contenidos/inicioAdmin.php
 */

use Illuminate\Database\Capsule\Manager as DB;

// =========================================================================
// 1. KPI SUPERIORES (CONTEOS EN TIEMPO REAL - ACTUALIZADO)
// =========================================================================
$stats = [
    'miembros'         => DB::table('miembros')->where('estado', 1)->count(),
    'miembros_inact'   => DB::table('miembros')->where('estado', 0)->count(),
    'grupos'           => DB::table('discipulado_grupos')->where('estado_id', 1)->count(),
    
    // ¡CAMBIO AQUÍ! Contamos solo las visitas de los últimos 30 días
    'visitas_recientes'=> DB::table('visitas')
                            ->where('fecha_visita', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'))
                            ->count(), 
                            
    'recursos'         => DB::table('recursos')->count()
];

// =========================================================================
// 2. DATA DINÁMICA: GRÁFICO 1 - MIEMBROS POR CARGO
// =========================================================================
$miembrosPorCargo = DB::table('miembro_cargos as mc')
    ->join('cargos as c', 'mc.cargo_id', '=', 'c.id')
    ->join('miembros as m', 'mc.miembro_id', '=', 'm.id')
    ->where('m.estado', 1) 
    ->select('c.nombre as cargo', DB::raw('count(mc.miembro_id) as total'))
    ->groupBy('c.nombre')
    ->get();

$cargosLabels = [];
$cargosData   = [];
foreach ($miembrosPorCargo as $row) {
    $cargosLabels[] = $row->cargo;
    $cargosData[]   = (int)$row->total;
}

// =========================================================================
// 3. DATA DINÁMICA: GRÁFICO 2 - MIEMBROS POR CONDICIÓN DE SALUD
// =========================================================================
$miembrosPorCondicion = DB::table('condiciones_miembro as cm')
    ->join('miembros as m', 'm.condicion_id', '=', 'cm.id')
    ->where('m.estado', 1)
    ->select('cm.nombre as condicion', DB::raw('count(m.id) as total'))
    ->groupBy('cm.nombre')
    ->get();

$condicionLabels = [];
$condicionData   = [];
foreach ($miembrosPorCondicion as $row) {
    $condicionLabels[] = ucfirst(strtolower($row->condicion));
    $condicionData[]   = (int)$row->total;
}

// =========================================================================
// NUEVA DATA ACUMULATIVA: GRÁFICO 3 - MOTIVOS DE VISITA ESTÁNDAR
// =========================================================================
$visitasRaw = DB::table('visitas')
    ->select('motivo', DB::raw('count(*) as total'))
    ->whereNotNull('motivo')
    ->where('motivo', '<>', '')
    ->groupBy('motivo')
    ->get();

// Definimos la estructura limpia basada estrictamente en tus categorías oficiales
$estructuraMotivos = [
    'Visita regular' => 0,
    'Por enfermedad' => 0,
    'Evangelística'  => 0,
    'Otros'          => 0
];

// Mapeamos y agrupamos los datos de la base de datos
foreach ($visitasRaw as $row) {
    // Normalizamos el texto (Primera letra mayúscula, resto minúscula, quitando espacios extra)
    $motivoFormateado = ucfirst(strtolower(trim($row->motivo)));
    
    if (array_key_exists($motivoFormateado, $estructuraMotivos)) {
        // Si es un motivo oficial, le sumamos su cantidad real
        $estructuraMotivos[$motivoFormateado] += (int)$row->total;
    } else {
        // Si es un texto extraño o de prueba (ej: "Lkbkbmfk"), se acumula directo en 'Otros'
        $estructuraMotivos['Otros'] += (int)$row->total;
    }
}

// Convertimos el resultado agrupado a los arrays limpios que espera Chart.js
$motivosLabels = [];
$motivosData   = [];
foreach ($estructuraMotivos as $label => $total) {
    if ($total > 0) { // Solo enviamos al gráfico los que tengan al menos 1 registro
        $motivosLabels[] = $label;
        $motivosData[]   = $total;
    }
}

// =========================================================================
// 4. DATA DINÁMICA: GRÁFICO 4 - VISITAS REALIZADAS (BARRAS EVOLUTIVAS)
// =========================================================================
$visitasUltimos30Dias = DB::table('visitas')
    ->where('fecha_visita', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'))
    ->select(DB::raw("DATE_FORMAT(fecha_visita, '%d/%m') as fecha"), DB::raw('count(*) as total'))
    ->groupBy(DB::raw("DATE_FORMAT(fecha_visita, '%d/%m')"))
    ->orderBy('fecha_visita', 'asc')
    ->get();

if ($visitasUltimos30Dias->isEmpty()) {
    $visitasUltimos30Dias = DB::table('visitas')
        ->select(DB::raw("DATE_FORMAT(fecha_visita, '%d/%m') as fecha"), DB::raw('count(*) as total'))
        ->groupBy(DB::raw("DATE_FORMAT(fecha_visita, '%d/%m')"))
        ->orderBy('fecha_visita', 'asc')
        ->limit(10)
        ->get();
}

$visitasLabels = [];
$visitasData   = [];
foreach ($visitasUltimos30Dias as $row) {
    $visitasLabels[] = $row->fecha;
    $visitasData[]   = (int)$row->total;
}

// =========================================================================
// 5. TABLA: ÚLTIMAS 5 VISITAS REGISTRADAS
// =========================================================================
$ultimasVisitas = DB::table('visitas as v')
    ->join('miembros as m', 'v.miembro_id', '=', 'm.id')
    ->select('v.fecha_visita', 'v.motivo', 'm.nombres', 'm.apellidos')
    ->orderBy('v.fecha_visita', 'desc')
    ->limit(5)
    ->get();

$nombreUsuario = $_SESSION['usuario'] ?? 'Administrador';
?>

<div id="dashboard-charts-data" style="display:none;"
     data-cargos-labels='<?php echo json_encode($cargosLabels); ?>'
     data-cargos-data='<?php echo json_encode($cargosData); ?>'
     data-condicion-labels='<?php echo json_encode($condicionLabels); ?>'
     data-condicion-data='<?php echo json_encode($condicionData); ?>'
     data-visitas-labels='<?php echo json_encode($visitasLabels); ?>'
     data-visitas-data='<?php echo json_encode($visitasData); ?>'
     data-motivos-labels='<?php echo json_encode($motivosLabels); ?>'
     data-motivos-data='<?php echo json_encode($motivosData); ?>'>
</div>

<header class="barra-superior">
    <div class="barra-info">
        <h1>¡Bienvenido, <span><?php echo htmlspecialchars($nombreUsuario); ?></span>!</h1>
        <p><i class="fas fa-church" style="color: #3b82f6;"></i> Panel de Control - Iglesia del Nazareno Bagua</p>
    </div>
    <div class="reportes-container">
        <a href="index.php?vista=dashboard&seccion=reportes" class="btn-reportes-link">
            <i class="fa-solid fa-file-invoice"></i> Reportes
        </a>
    </div>
</header>


<div class="stats-grid">
    <!-- Card 1: Miembros Activos -->
    <div class="stat-card">
        <div class="stat-icon miembros"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['miembros']; ?></span>
            <span class="stat-label">Miembros Activos</span>
        </div>
    </div>

    <!-- ¡NUEVA! Card 2: Miembros Inactivos -->
    <div class="stat-card">
        <div class="stat-icon miembros-inactivos"><i class="fas fa-user-slash"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['miembros_inact']; ?></span>
            <span class="stat-label">Miembros Inactivos</span>
        </div>
    </div>

    <!-- Card 3: Grupos Activos -->
    <div class="stat-card">
        <div class="stat-icon grupos"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['grupos']; ?></span>
            <span class="stat-label">Grupos Activos</span>
        </div>
    </div>

    <!-- Card 4: Visitas Recientes -->
    <div class="stat-card">
        <div class="stat-icon visitas"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['visitas_recientes']; ?></span>
            <span class="stat-label">Visitas ultimos 30 días</span>
        </div>
    </div>

    <!-- Card 5: Recursos -->
    <div class="stat-card">
        <div class="stat-icon recursos"><i class="fas fa-folder-open"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['recursos']; ?></span>
            <span class="stat-label">Recursos</span>
        </div>
    </div>
</div>
<div class="charts-row">
    <div class="dashboard-card custom-chart-box">
        <h3><i class="fa-solid fa-id-card-clip" style="color: #3b82f6; margin-right: 8px;"></i> Miembros por Cargo</h3>
        <p class="widget-subtitle">Distribución activa por responsabilidades</p>
        <div class="chart-canvas-wrapper">
            <?php if(empty($cargosData)): ?>
                <div class="no-data-msg">No hay asignaciones de cargos.</div>
            <?php else: ?>
                <canvas id="chartCargos"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-card custom-chart-box">
        <h3><i class="fa-solid fa-comments" style="color: #f59e0b; margin-right: 8px;"></i> Motivos de Visita</h3>
        <p class="widget-subtitle">Principales necesidades conversadas en visitas</p>
        <div class="chart-canvas-wrapper">
            <?php if(empty($motivosData)): ?>
                <div class="no-data-msg">No hay registros de motivos.</div>
            <?php else: ?>
                <canvas id="chartMotivos"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-card custom-chart-box">
        <h3><i class="fa-solid fa-heart-pulse" style="color: #ec4899; margin-right: 8px;"></i> Estado / Condición</h3>
        <p class="widget-subtitle">Monitoreo de la condición de salud de los miembros</p>
        <div class="chart-canvas-wrapper">
            <?php if(empty($condicionData)): ?>
                <div class="no-data-msg">Sin registros de salud.</div>
            <?php else: ?>
                <canvas id="chartCondiciones"></canvas>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="dashboard-layout-bottom">
    <div class="dashboard-card widget-bar-chart">
        <h3><i class="fa-solid fa-chart-bar" style="color: #10b981; margin-right: 8px;"></i> Visitas Realizadas</h3>
        <p class="widget-subtitle">Frecuencia e intensidad de las visitas pastorales</p>
        <div class="chart-canvas-wrapper-bar">
            <?php if(empty($visitasData)): ?>
                <div class="no-data-msg">No se encontraron visitas registradas.</div>
            <?php else: ?>
                <canvas id="chartVisitasBarras"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-card widget-table-recent">
        <h3><i class="fa-solid fa-clock-rotate-left" style="color: #f59e0b; margin-right: 8px;"></i> Actividad Reciente</h3>
        <p class="widget-subtitle">Últimas interacciones guardadas en el sistema</p>
        <div class="tabla-container-mini">
            <table class="tabla-moderna-dashboard">
                <thead>
                    <tr>
                        <th>Miembro</th>
                        <th>Fecha</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ultimasVisitas->isEmpty()): ?>
                        <tr><td colspan="3" class="text-center">No hay visitas registradas.</td></tr>
                    <?php else: foreach ($ultimasVisitas as $v): ?>
                        <tr>
                            <td>
                                <div class="user-info-mini">
                                    <div class="avatar-circle-mini"><?php echo strtoupper(substr($v->nombres, 0, 1)); ?></div>
                                    <span><?php echo htmlspecialchars($v->nombres . " " . $v->apellidos); ?></span>
                                </div>
                            </td>
                            <td class="fecha-celda"><?php echo date('d/m/Y', strtotime($v->fecha_visita)); ?></td>
                            <td><span class="badge-motivo"><?php echo htmlspecialchars($v->motivo); ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
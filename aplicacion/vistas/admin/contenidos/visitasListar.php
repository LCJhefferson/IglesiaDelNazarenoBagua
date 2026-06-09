<?php
// Aseguramos iniciar la sesión si no está iniciada (necesario para el CSRF si usas sesiones)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




//////PROTECTOR PARA MULTI CONTROL Z   XD//1/2//W/D/F/S/C/F/F/







// 1. Definimos el Token CSRF (Ajusta 'csrf_token' al nombre real que uses en tu sistema)
$csrfToken = $_SESSION['csrf_token'] ?? ''; 

use aplicacion\controladores\VisitaController;

// Instanciamos el Controlador
$visitaController = new VisitaController();

$filtroNombre = $_REQUEST['nombre'] ?? '';
$filtroMotivo = $_REQUEST['motivo'] ?? '';
$filtroEstado = $_REQUEST['estado'] ?? '';
$filtroModo   = $_REQUEST['modo'] ?? 'ultimo'; 

// Usamos el Controlador para obtener los datos
$miembrosTodos = $visitaController->listarConDetalles($filtroModo);
$mesesLimiteActual = $visitaController->obtenerMesesLimite();

$miembrosFiltrados = [];
$conteo = ['reciente' => 0, 'intermedio' => 0, 'proximo' => 0, 'critico' => 0];

// Aplicar filtros y conteos
foreach ($miembrosTodos as $m) {
    if ($m['clase_css'] === 'estado-verde-reciente') $conteo['reciente']++;
    elseif ($m['clase_css'] === 'estado-azul-intermedio') $conteo['intermedio']++;
    elseif ($m['clase_css'] === 'estado-amarillo-proximo') $conteo['proximo']++;
    elseif ($m['clase_css'] === 'estado-rojo-critico') $conteo['critico']++;

    if ($filtroNombre !== '' && stripos($m['miembro_nombre'], $filtroNombre) === false) {
        continue;
    }
   // Lógica corregida para el filtro de motivos
    if ($filtroMotivo !== '') {
        $motivoActual = $m['ultimo_motivo'] ?? '';
        // Definimos cuáles son las opciones estrictas
        $motivosPredefinidos = ['Visita Regular', 'Por Enfermedad', 'Evangelística'];

        if ($filtroMotivo === 'Otros') {
            // Si elige 'Otros', descartamos los registros que tengan un motivo predefinido o que estén vacíos/Ninguno
            if (in_array($motivoActual, $motivosPredefinidos) || $motivoActual === 'Ninguno' || $motivoActual === '') {
                continue; 
            }
        } else {
            // Si elige uno de los normales, exigimos coincidencia exacta
            if ($motivoActual !== $filtroMotivo) {
                continue;
            }
        }
    }
    if ($filtroEstado !== '') {
        $claseEsperada = match($filtroEstado) {
            'reciente'   => 'estado-verde-reciente',
            'intermedio' => 'estado-azul-intermedio',
            'proximo'    => 'estado-amarillo-proximo',
            'critico'    => 'estado-rojo-critico',
            default      => ''
        };
        if ($m['clase_css'] !== $claseEsperada) continue;
    }

    $miembrosFiltrados[] = $m;
}

// ==========================================
// RESPUESTA AJAX (Para la actualización dinámica sin recargar)
// ==========================================
if (isset($_GET['ajax']) && $_GET['ajax'] == '1'):
?>
    <div id="ajax-stats-bridge">
        <div class="tarjeta-estadistica"><div class="icono-estadistica verde"><i class="fa-solid fa-circle-check"></i></div><div class="datos-estadistica"><div class="valor"><?= $conteo['reciente'] ?></div><div class="etiqueta">Visitado reciente</div></div></div>
        <div class="tarjeta-estadistica"><div class="icono-estadistica azul"><i class="fa-solid fa-user-check"></i></div><div class="datos-estadistica"><div class="valor"><?= $conteo['intermedio'] ?></div><div class="etiqueta">Visitado intermedio</div></div></div>
        <div class="tarjeta-estadistica"><div class="icono-estadistica naranja"><i class="fa-solid fa-clock"></i></div><div class="datos-estadistica"><div class="valor"><?= $conteo['proximo'] ?></div><div class="etiqueta">Pendiente próximo</div></div></div>
        <div class="tarjeta-estadistica"><div class="icono-estadistica rojo"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="datos-estadistica"><div class="valor"><?= $conteo['critico'] ?></div><div class="etiqueta">Pendiente crítico</div></div></div>
    </div>

    <table id="ajax-thead-bridge">
        <thead>
            <tr>
                <th>Miembro</th>
                <th>Dirección</th>
                <th><?= ($filtroModo === 'todos') ? 'Fecha Registro' : 'Última Visita' ?></th>
                <th><?= ($filtroModo === 'todos') ? 'Motivo Visita' : 'Motivo Último' ?></th>
                <th>Estado</th>
                <th style="text-align:center">Acciones</th>
            </tr>
        </thead>
    </table>

    <table>
        <tbody id="ajax-tbody-bridge">
            <?php foreach ($miembrosFiltrados as $m): ?>
                <tr>
                    <td style="font-weight: 500;"><?= htmlspecialchars($m['miembro_nombre']) ?></td>
                    <td><small><i class="fa-solid fa-location-dot"></i></small> <?= htmlspecialchars($m['direccion'] ?? 'Sin dirección') ?></td>
                    <td style="font-size:0.9rem; font-weight: 600;"><?= $m['ultima_fecha_formateada'] ?></td>
                    <td><?= htmlspecialchars($m['ultimo_motivo'] ?? 'Ninguno') ?></td>
                    <td>
                        <span class="badge-estado <?= $m['clase_css'] ?>">
                            <i class="fa-solid <?= $m['icono'] ?>"></i> <?= $m['estado_texto'] ?>
                        </span>
                    </td>
                    <td style="text-align:center; display: flex; justify-content: center; gap: 6px;">
                        <button class="btn-accion btn-visitar" title="Registrar" 
                                onclick="abrirModalVisita(<?= (int)$m['miembro_id'] ?>, '<?= addslashes(htmlspecialchars($m['miembro_nombre'], ENT_QUOTES)) ?>')">
                            <i class="fa-solid fa-plus"></i>
                        </button>

                        <?php if (!empty($m['ultima_visita_id'])): ?>
                            <button class="btn-accion" style="background:#f59e0b; color:white;" title="Editar"
                                    onclick="abrirModalEditar(
                                        <?= (int)($m['ultima_visita_id'] ?? 0) ?>, 
                                        <?= (int)$m['miembro_id'] ?>, 
                                        '<?= addslashes(htmlspecialchars($m['miembro_nombre'], ENT_QUOTES)) ?>', 
                                        '<?= !empty($m['fecha_real']) ? date('Y-m-d', strtotime($m['fecha_real'])) : '' ?>', 
                                        '<?= addslashes(htmlspecialchars($m['ultimo_motivo'] ?? '', ENT_QUOTES)) ?>'
                                    )">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <button class="btn-accion btn-eliminar-visita" title="Eliminar"
                                    onclick="abrirModalEliminar(<?= (int)($m['ultima_visita_id'] ?? 0) ?>, '<?= addslashes(htmlspecialchars($m['miembro_nombre'], ENT_QUOTES)) ?>')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php 
    exit; 
endif; 
// ==========================================
// FIN RESPUESTA AJAX
// ==========================================
?>

<div class="header-seccion">
    <h1>Seguimiento de Visitas a Miembros</h1>
    <a href="javascript:void(0);" class="btn-ajustes" onclick="abrirModalAjustes()">
        <i class="fa-solid fa-gear"></i> Ajustes Rango (<?= $mesesLimiteActual ?> meses)
    </a>
</div>

<div class="cuadricula-estadisticas" id="contenedor-stats">
  <div class="tarjeta-estadistica"><div class="icono-estadistica verde"><i class="fa-solid fa-circle-check"></i></div><div class="datos-estadistica"><div class="valor"><?= $conteo['reciente'] ?></div><div class="etiqueta">Visitado reciente</div></div></div>
  <div class="tarjeta-estadistica"><div class="icono-estadistica azul"><i class="fa-solid fa-user-check"></i></div><div class="datos-estadistica"><div class="valor"><?= $conteo['intermedio'] ?></div><div class="etiqueta">Visitado intermedio</div></div></div>
  <div class="tarjeta-estadistica"><div class="icono-estadistica naranja"><i class="fa-solid fa-clock"></i></div><div class="datos-estadistica"><div class="valor"><?= $conteo['proximo'] ?></div><div class="etiqueta">Pendiente próximo</div></div></div>
  <div class="tarjeta-estadistica"><div class="icono-estadistica rojo"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="datos-estadistica"><div class="valor"><?= $conteo['critico'] ?></div><div class="etiqueta">Pendiente crítico</div></div></div>
</div>

<div class="filtros">
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; width: 100%;">
        <input type="text" id="filtroNombre" placeholder="Buscar por nombre..." value="<?= htmlspecialchars($filtroNombre) ?>" oninput="filtrarVisitas()" style="flex: 1; min-width: 180px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">

        <select id="filtroMotivo" onchange="filtrarVisitas()" style="padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
            <option value="">Todos los motivos</option>
            <option value="Visita Regular" <?= $filtroMotivo == 'Visita Regular' ? 'selected' : '' ?>>Visita Regular</option>
            <option value="Por Enfermedad" <?= $filtroMotivo == 'Por Enfermedad' ? 'selected' : '' ?>>Por Enfermedad</option>
            <option value="Evangelística" <?= $filtroMotivo == 'Evangelística' ? 'selected' : '' ?>>Evangelística</option>
            <option value="Otros" <?= $filtroMotivo == 'Otros' ? 'selected' : '' ?>>Otros</option>
        </select>

        <select id="filtroEstado" onchange="filtrarVisitas()" style="padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
            <option value="">Todos los estados</option>
            <option value="reciente" <?= $filtroEstado == 'reciente' ? 'selected' : '' ?>>Visitado reciente</option>
            <option value="intermedio" <?= $filtroEstado == 'intermedio' ? 'selected' : '' ?>>Visitado intermedio</option>
            <option value="proximo" <?= $filtroEstado == 'proximo' ? 'selected' : '' ?>>Pendiente próximo</option>
            <option value="critico" <?= $filtroEstado == 'critico' ? 'selected' : '' ?>>Pendiente crítico</option>
        </select>

        <select id="filtroModo" onchange="filtrarVisitas()" style="padding: 8px; border-radius: 6px; border: 2px solid #3b82f6; font-weight: 600; color: #1e3a8a;">
            <option value="ultimo" <?= $filtroModo == 'ultimo' ? 'selected' : '' ?>>Último registro por miembro</option>
            <option value="todos" <?= $filtroModo == 'todos' ? 'selected' : '' ?>>Todos los registros</option>
        </select>
        
        <button onclick="limpiarFiltros()" class="btn-accion" style="background:#ef4444; color:white; padding:8px 15px; border:none; cursor:pointer; border-radius:6px; display: flex; align-items: center; gap: 5px;" title="Limpiar filtros">
             Limpiar
        </button>
    </div>
</div>

<table>
  <thead id="tabla-visitas-head">
    <tr>
        <th>Miembro</th>
        <th>Dirección</th>
        <th id="th-fecha"><?= ($filtroModo === 'todos') ? 'Fecha Registro' : 'Última Visita' ?></th>
        <th id="th-motivo"><?= ($filtroModo === 'todos') ? 'Motivo Visita' : 'Motivo Último' ?></th>
        <th>Estado</th>
        <th style="text-align:center">Acciones</th>
    </tr>
  </thead>
  <tbody id="tabla-visitas-cuerpo">
    <?php foreach ($miembrosFiltrados as $m): ?>
        <tr>
          <td style="font-weight: 500;"><?= htmlspecialchars($m['miembro_nombre']) ?></td>
          <td><small><i class="fa-solid fa-location-dot"></i></small> <?= htmlspecialchars($m['direccion'] ?? 'Sin dirección') ?></td>
          <td style="font-size:0.9rem; font-weight: 600;"><?= $m['ultima_fecha_formateada'] ?></td>
          <td><?= htmlspecialchars($m['ultimo_motivo'] ?? 'Ninguno') ?></td>
          <td>
              <span class="badge-estado <?= $m['clase_css'] ?>">
                  <i class="fa-solid <?= $m['icono'] ?>"></i> <?= $m['estado_texto'] ?>
              </span>
          </td>
          <td style="text-align:center; display: flex; justify-content: center; gap: 6px;">
              <button class="btn-accion btn-visitar" title="Registrar" 
                      onclick="abrirModalVisita(<?= (int)$m['miembro_id'] ?>, '<?= addslashes(htmlspecialchars($m['miembro_nombre'], ENT_QUOTES)) ?>')">
                  <i class="fa-solid fa-plus"></i>
              </button>

              <?php if (!empty($m['ultima_visita_id'])): ?>
                  <?php 
                        // Usamos 'fecha_real', que es la llave correcta enviada desde el Controlador
                        $fechaCruda = $m['fecha_real'] ?? '';
                        // Formateamos estrictamente a YYYY-MM-DD para el input type="date"
                        $fechaParaInput = !empty($fechaCruda) ? date('Y-m-d', strtotime($fechaCruda)) : '';
                    ?>

                    <button class="btn-accion" style="background:#f59e0b; color:white;" title="Editar"
                            onclick="abrirModalEditar(
                                <?= (int)$m['ultima_visita_id'] ?>, 
                                <?= (int)$m['miembro_id'] ?>, 
                                '<?= addslashes(htmlspecialchars($m['miembro_nombre'], ENT_QUOTES)) ?>', 
                                '<?= $fechaParaInput ?>', 
                                '<?= addslashes(htmlspecialchars($m['ultimo_motivo'] ?? '', ENT_QUOTES)) ?>'
                            )">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>

                  <button class="btn-accion btn-eliminar-visita" title="Eliminar"
                          onclick="abrirModalEliminar(<?= (int)$m['ultima_visita_id'] ?>, '<?= addslashes(htmlspecialchars($m['miembro_nombre'], ENT_QUOTES)) ?>')">
                      <i class="fa-solid fa-trash-can"></i>
                  </button>
              <?php endif; ?>


          </td>
        </tr>
    <?php endforeach; ?>
  </tbody>
</table>







<div id="modalVisita" class="modal" style="display: none;">
    <div class="modal-contenido">
        <div class="modal-header" id="modalHeaderTitulo">Registrar Visita</div>
        <p style="margin-top:-5px; margin-bottom:20px; color:#64748b; font-size:0.9rem;">
            Miembro: <strong id="modalNombreMiembro" style="color:#1e293b;">-</strong>
        </p>
        <form id="formRegistrarVisita" action="<?= URL ?>index.php?vista=admin/guardarVisita" onsubmit="procesarGuardarVisita(event)">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
            <input type="hidden" name="miembro_id" id="modalMiembroId">
            <input type="hidden" name="visita_id" id="modalVisitaId">
            <div class="campo">
                <label for="txtFechaVisita">Fecha de la Visita</label>
                <input type="date" id="txtFechaVisita" name="fecha_visita" required>
            </div>
            <div class="campo">
                <label for="selectMotivo">Motivo de la Visita</label>
                <select id="selectMotivo" name="motivo_predefinido" onchange="evaluarSeleccionMotivo(this)" required>
                    <option value="Visita Regular">Visita Regular</option>
                    <option value="Por Enfermedad">Por Enfermedad</option>
                    <option value="Evangelística">Evangelística</option>
                    <option value="Otros">Otros (Escribir motivo)</option>
                </select>
                <div id="contenedorOtros" class="area-otros" style="display: none;">
                    <label for="txtMotivoLibre">Especificar Motivo</label>
                    <textarea id="txtMotivoLibre" name="motivo_libre" rows="3" placeholder="Escribe detalladamente la razón..."></textarea>
                </div>
            </div>
            <div class="modal-acciones">
                <button type="button" class="btn-accion" onclick="cerrarModalVisita()">Cancelar</button>
                <button type="submit" class="btn-accion btn-visitar" id="btnSubmitVisita">
                    <i class="fa-solid fa-floppy-disk"></i> <span id="btnTextVisita">Guardar Registro</span>
                </button>
            </div>
        </form>
    </div>
</div>























<div id="modalAjustes" class="modal" style="display: none;">
    <div class="modal-contenido">
        <div class="modal-header"><i class="fa-solid fa-gear"></i> Ajustes de Tiempo</div>
        <p style="color:#64748b; font-size:0.85rem; margin-bottom:20px;">
            Modifica la frecuencia máxima tolerada (en meses) para recalcular los estados dinámicos.
        </p>
        <form id="formAjustesVisita" action="<?= URL ?>index.php?vista=admin/guardarAjustesVisita" onsubmit="procesarGuardarAjustes(event)">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
            <div class="campo">
                <label for="numMeses">Frecuencia Máxima (Meses)</label>
                <input type="number" id="numMeses" name="meses_limite" value="<?= $mesesLimiteActual ?>" min="1" max="24" required>
            </div>
            <div class="modal-acciones">
                <button type="button" class="btn-accion" onclick="cerrarModalAjustes()">Cancelar</button>
                <button type="submit" class="btn-accion btn-visitar" style="background:#64748b;"><i class="fa-solid fa-rotate"></i> Actualizar Rangos</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEliminarVisita" class="modal" style="display: none;">
    <div class="modal-contenido" style="max-width: 420px; text-align: center;">
        <div style="color: #ef4444; font-size: 3rem; margin-bottom: 10px;">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div class="modal-header" style="margin-bottom: 10px; justify-content: center;">¿Eliminar esta visita?</div>
        <p style="color:#64748b; font-size:0.95rem; margin-bottom: 25px; line-height: 1.5;">
            ¿Estás seguro de que deseas revertir el último registro de visita para 
            <strong id="eliminarNombreMiembro" style="color:#0f172a;">-</strong>?
        </p>
        <form id="formEliminarVisita">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
            <input type="hidden" name="visita_id" id="modalEliminarVisitaId">
            <div class="modal-acciones" style="display:flex; justify-content:center; gap:15px;">
                <button type="button" class="btn-accion" onclick="cerrarModalEliminar()">Cancelar</button>
                <button type="button" class="btn-accion" onclick="procesarEliminacionLogica()" style="background:#ef4444; color:white; padding:10px 20px; font-weight:600;">
                    <i class="fa-solid fa-trash"></i> Suprimir
                </button>
            </div>
        </form>
    </div>
</div>
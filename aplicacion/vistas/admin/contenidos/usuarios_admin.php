<?php
use aplicacion\controladores\UsuarioController;
use Illuminate\Database\Capsule\Manager as DB;

$controller = new UsuarioController();

// ── ENDPOINT PARA CONSULTAR BITÁCORA VÍA AJAX ──
if (isset($_GET['obtener_bitacora']) && !empty($_GET['usuario_id'])) {
    header('Content-Type: application/json');
    $logs = DB::table('bitacora')
              ->where('usuario_id', $_GET['usuario_id'])
              ->orderBy('fecha', 'DESC')
              ->limit(10) // Limitamos a las últimas 10 actividades
              ->get();
    echo json_encode($logs);
    exit;
}

// ── PROCESAMIENTO DE FORMULARIOS POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Caso: Cambio de Contraseña
    if (isset($_POST['action']) && $_POST['action'] === 'cambiar_password' && isset($_POST['id'])) {
        $resultado = $controller->cambiarPassword($_POST['id'], $_POST['nueva_password']);
        
        if ($resultado) {
            DB::table('bitacora')->insert([
                'usuario_id' => $_POST['id'],
                'accion' => 'El administrador restableció la contraseña de este usuario.',
                'fecha' => date('Y-m-d H:i:s')
            ]);
        }
        
        $status = $resultado ? 'exito=1' : 'error=1';
        echo "<script>window.location.href = '/IglesiaDelNazarenoBagua/dashboard?seccion=usuarios_admin&$status';</script>";
        exit;
    }

    // 2. Caso: Edición/Actualización de Usuario
    if (isset($_POST['action']) && $_POST['action'] === 'editar' && isset($_POST['id'])) {
        $resultado = $controller->actualizar(
            $_POST['id'],
            $_POST['username'],
            $_POST['rol'],
            $_POST['estado']
        );
        
        if ($resultado) {
            DB::table('bitacora')->insert([
                'usuario_id' => $_POST['id'],
                'accion' => "Datos actualizados. Rol: {$_POST['rol']}, Estado: {$_POST['estado']}.",
                'fecha' => date('Y-m-d H:i:s')
            ]);
        }
        
        $status = $resultado ? 'exito=1' : 'error=1';
        echo "<script>window.location.href = '/IglesiaDelNazarenoBagua/dashboard?seccion=usuarios_admin&$status';</script>";
        exit;
    }

    // 3. Caso: Desactivar / Eliminar Lógico (NUEVO)
    if (isset($_POST['action']) && $_POST['action'] === 'desactivar' && isset($_POST['id'])) {
        $resultado = $controller->desactivar($_POST['id']);
        
        if ($resultado) {
            DB::table('bitacora')->insert([
                'usuario_id' => $_POST['id'],
                'accion' => 'El usuario fue marcado como inactivo por el administrador.',
                'fecha' => date('Y-m-d H:i:s')
            ]);
        }
        
        $status = $resultado ? 'exito=1' : 'error=1';
        echo "<script>window.location.href = '/IglesiaDelNazarenoBagua/dashboard?seccion=usuarios_admin&$status';</script>";
        exit;
    }
    
    // 4. Caso: Registro de Nuevo Usuario
    if (isset($_POST['username']) && !isset($_POST['action'])) {
        $resultado  = $controller->registrar(
            $_POST['username'],
            $_POST['password'],
            $_POST['rol'],
            $_POST['estado']
        );
        $status = $resultado ? 'exito=1' : 'error=1';
        echo "<script>window.location.href = '/IglesiaDelNazarenoBagua/dashboard?seccion=usuarios_admin&$status';</script>";
        exit;
    }
}

// ... El resto de consultas a $usuarios y mapas de diseño siguen igual ...
// Ordena primero por estado ('activo' antes que 'inactivo' alfabéticamente) 
// y de forma secundaria por nombre de usuario de la A a la Z
$usuarios = DB::table('usuarios')
              ->orderBy('estado', 'asc')
              ->orderBy('username', 'asc')
              ->get();
$total_usuarios       = $usuarios->count();
$total_activos         = $usuarios->where('estado', 'activo')->count();
$total_inactivos       = $usuarios->where('estado', 'inactivo')->count();

$etiqueta_rol = ['1' => 'Admin', '2' => 'Pastor', '9' => 'Discipulador', '11' => 'Secretaria', '12' => 'Grupo de Visitas'];
$clase_rol    = ['1' => 'rol-admin', '2' => 'rol-editor', '9' => 'rol-lector', '11' => 'rol-lector', '12' => 'rol-lector'];
$color_avatar = ['1' => '#38d9a9', '2' => '#4f6ef7', '9' => '#f59f00', '11' => '#e64980', '12' => '#15aabf'];
$etiqueta_estado = ['activo' => 'Activo', 'inactivo' => 'Inactivo'];
$clase_estado    = ['activo' => 'estado-activo', 'inactivo' => 'estado-inactivo'];
?>

<!-- ── BARRA SUPERIOR ── -->
<header class="barra-superior">
<div class="barra-info">
    <h1>
        <i class="fa-solid fa-newspaper"></i>
        Gestión de Usuarios
    </h1>
    <p>
        Creación y administración de miembros de la Iglesia Del Nazareno
    </p>
</div>
<div class="barra-acciones">
    <div class="badge-info">
        <i class="fa-solid fa-user-check"></i>
        <span class="badge-total-real" ><?= $total_activos ?></span>
        Usuario Activos
    </div>
    <div class="badge-info">
        <i class="fa-solid fa-user-xmark"></i>
        <span class="badge-total-real"><?= $total_inactivos  ?></span>
        Usuarios Inactivos
    </div>
    <button class="boton boton-primario" onclick="abrirModalCrear()">
        <i class="fa-solid fa-user-plus"></i> Nuevo usuario
    </button>
</div>
</header>

<!-- ── CONTENIDO PRINCIPAL ── -->
<main class="area-contenido">

    <!-- TABLA -->
    <div class="contenedor-tabla">
        <div class="cabecera-tabla">
            <h2>Lista de Usuarios</h2>
            <span class="contador-tabla" id="contadorTabla">
                <?= $total_usuarios ?> usuarios
            </span>
        </div>

        <div style="padding: 16px 22px; border-bottom: 1px solid var(--borde);">
            <div class="barra-herramientas">
                <div class="contenedor-busqueda">
                    <i class="fa-solid fa-magnifying-glass icono-busqueda"></i>
                    <input type="text" class="campo-busqueda"
                           placeholder="Buscar por usuario..."
                           oninput="filtrarUsuarios(this.value)"/>
                </div>
                <select class="selector-filtro" onchange="filtrarPorEstado(this.value)">
                    <option value="todos">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
                <select class="selector-filtro" onchange="filtrarPorRol(this.value)">
                    <option value="todos">Todos los roles</option>
                    <option value="1">Admin</option>
                    <option value="2">Pastor</option>
                    <option value="9">Discipulador</option>
                    <option value="11">Secretaria</option>
                    <option value="12">Grupo de Visitas</option>
                </select>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th style="text-align:center">Acciones</th>
                    </tr>
                </thead>

                <tbody id="cuerpoTabla">
                <?php foreach ($usuarios as $usuario): ?>
                    <?php
                        $idUsuario = $usuario->id;
                        $rolId    = $usuario->id_rol;
                        $estado   = $usuario->estado;
                        $username = $usuario->username;

                        $avatar   = strtoupper(substr($username, 0, 2));
                        $colorAv  = $color_avatar[$rolId] ?? '#868e96';
                        $claseRol = $clase_rol[$rolId]    ?? 'rol-lector';
                        $etqRol   = $etiqueta_rol[$rolId] ?? 'Sin rol';
                        $claseEst = $clase_estado[$estado] ?? '';
                        $etqEst   = $etiqueta_estado[$estado] ?? $estado;
                    ?>
                    <tr data-nombre="<?= strtolower($username) ?>"
                        data-estado="<?= $estado ?>"
                        data-rol="<?= $rolId ?>">

                        <td>
                            <div class="celda-usuario">
                                <div class="avatar-usuario" style="background: <?= $colorAv ?>">
                                    <?= $avatar ?>
                                </div>
                                <div>
                                    <div class="nombre-usuario"><?= htmlspecialchars($username) ?></div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="badge-rol <?= $claseRol ?>">
                                <?= $etqRol ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge-estado <?= $claseEst ?>">
                                <?= $etqEst ?>
                            </span>
                        </td>

                        <td>
                            <div class="celda-acciones" style="justify-content: center; gap: 6px;">
                                <button class="boton-icono" title="Editar"
                                        onclick="abrirModalEditar(<?= $idUsuario ?>, '<?= addslashes($username) ?>', '<?= $rolId ?>', '<?= $estado ?>')">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                
                                <button class="boton-icono" style="color: #4f6ef7;" title="Cambiar Contraseña"
                                        onclick="abrirModalPassword(<?= $idUsuario ?>, '<?= addslashes($username) ?>')">
                                    <i class="fa-solid fa-key"></i>
                                </button>

                                <button class="boton-icono" style="color: #1098ad;" title="Ver Actividad"
                                        onclick="abrirModalBitacora(<?= $idUsuario ?>, '<?= addslashes($username) ?>')">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <!-- BOTÓN NUEVO: Llamado directo a abrirModalEliminar -->
                                <button class="boton-icono" style="color: #e64980;" title="Desactivar Usuario"
                                        onclick="abrirModalEliminar(<?= $idUsuario ?>, '<?= addslashes($username) ?>')">
                                    <i class="fa-solid fa-user-xmark"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="padding: 14px 22px; border-top: 1px solid var(--borde); display:flex; justify-content: space-between; align-items:center;">
            <span style="font-size:0.8rem; color:var(--texto-suave);">
                Mostrando <span id="filasMostradas"><?= $total_usuarios ?></span> de <?= $total_usuarios ?> usuarios
            </span>
        </div>
    </div>
</main>

<!-- MODAL CREAR -->
<div class="superposicion-modal" id="modalCrear">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalCrear()">✕</button>
        <h3>👤 Nuevo Usuario</h3>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
            <div class="grupo-formulario">
                <label>Username</label>
                <input type="text" id="crearUsername" name="username" placeholder="Ej: carlos123" required/>
            </div>
            <div class="grupo-formulario">
                <label>Contraseña</label>
                <input type="password" id="crearPassword" name="password" placeholder="Mínimo 8 caracteres" required/>
            </div>
            <div class="grupo-formulario">
                <label>Rol</label>
                <select id="crearRol" name="rol" required>
                    <option value="">Selecciona un rol</option>
                    <option value="1">Admin</option>
                    <option value="2">Pastor</option>
                    <option value="9">Discipulador</option>
                    <option value="11">Secretaria</option>
                    <option value="12">Grupo de Visitas</option>
                </select>
            </div>
            <div class="grupo-formulario">
                <label>Estado</label>
                <select id="crearEstado" name="estado">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="fila-botones-modal">
                <button type="button" class="boton boton-contorno" onclick="cerrarModalCrear()">Cancelar</button>
                <button type="submit" class="boton boton-primario">
                    <i class="fa-solid fa-user-plus"></i> Crear usuario
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="superposicion-modal" id="modalEditar">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalEditar()">✕</button>
        <h3>✏️ Editar Usuario</h3>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
            <input type="hidden" id="editarId" name="id"/>
            <input type="hidden" name="action" value="editar" />
            <div class="grupo-formulario">
                <label>Username</label>
                <input type="text" id="editarUsername" name="username" placeholder="Username" required/>
            </div>
            <div class="grupo-formulario">
                <label>Rol</label>
                <select id="editarRol" name="rol" required>
                    <option value="1">Admin</option>
                    <option value="2">Pastor</option>
                    <option value="9">Discipulador</option>
                    <option value="11">Secretaria</option>
                    <option value="12">Grupo de Visitas</option>
                </select>
            </div>
            <div class="grupo-formulario">
                <label>Estado</label>
                <select id="editarEstado" name="estado" required>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="fila-botones-modal">
                <button type="button" class="boton boton-contorno" onclick="cerrarModalEditar()">Cancelar</button>
                <button type="submit" class="boton boton-primario">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL ELIMINAR/DESACTIVAR (NUEVO HTML COMPLETO) -->
<div class="superposicion-modal" id="modalEliminar">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalEliminar()">✕</button>
        <h3>⚠️ Desactivar Usuario</h3>
        <p class="subtexto-confirmacion" id="textoEliminar" style="margin-bottom:20px; color: var(--texto-suave);"></p>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
            <input type="hidden" id="desactivarId" name="id" value="" />
            <input type="hidden" name="action" value="desactivar" /> 
            
            <div class="fila-botones-modal">
                <button type="button" class="boton boton-contorno" onclick="cerrarModalEliminar()">Cancelar</button>
                <button type="submit" class="boton" style="background-color: #e64980; color: white;">
                    <i class="fa-solid fa-user-xmark"></i> Confirmar Desactivación
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PASSWORD -->
<div class="superposicion-modal" id="modalPassword">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalPassword()">✕</button>
        <h3>🔑 Restablecer Contraseña</h3>
        <p class="subtexto-confirmacion" id="textoPassword" style="margin-bottom:15px; color: var(--texto-suave);"></p>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
            <input type="hidden" id="passId" name="id" value="" />
            <input type="hidden" name="action" value="cambiar_password" /> 
            <div class="grupo-formulario">
                <label>Nueva Contraseña</label>
                <input type="password" name="nueva_password" placeholder="Mínimo 8 caracteres" required minlength="8" />
            </div>
            <div class="fila-botones-modal">
                <button type="button" class="boton boton-contorno" onclick="cerrarModalPassword()">Cancelar</button>
                <button type="submit" class="boton" style="background-color: #4f6ef7; color: white;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Nueva Clave
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL BITACORA -->
<div class="superposicion-modal" id="modalBitacora">
    <div class="caja-modal" style="max-width: 550px;">
        <button class="cerrar-modal" onclick="cerrarModalBitacora()">✕</button>
        <h3>👁️ Registro de Actividad</h3>
        <p style="margin-bottom: 15px; color: var(--texto-suave);">Historial reciente del usuario: <strong id="nombreUsuarioBitacora"></strong></p>
        
        <div id="listaBitacora" style="max-height: 300px; overflow-y: auto; border: 1px solid var(--borde); border-radius: 8px; padding: 10px; background: #fafafa;">
            <p style="text-align:center; color:#888;">Cargando historial...</p>
        </div>
        
        <div class="fila-botones-modal" style="margin-top: 15px;">
            <button type="button" class="boton boton-contorno" onclick="cerrarModalBitacora()">Cerrar Ventana</button>
        </div>
    </div>
</div>

<!-- AVISO FLOTANTE -->
<div class="aviso" id="aviso">
    <i class="fa-solid fa-circle-check"></i>
    <span id="mensajeAviso">Acción completada</span>
</div>
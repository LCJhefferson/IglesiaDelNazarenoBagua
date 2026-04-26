<?php
use aplicacion\controladores\RegistroController;
use aplicacion\dao\userDAO;

// ── PROCESAR REGISTRO ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $controller = new RegistroController();
    $resultado  = $controller->registrar(
        $_POST['username'],
        $_POST['password'],
        $_POST['rol'],
        $_POST['estado']
    );
    if ($resultado) {
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=usuarios_admin&exito=1");
    } else {
        header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=usuarios_admin&error=1");
    }
    exit;
}

// ── OBTENER USUARIOS DE LA BD ──
$dao      = new userDAO();
$usuarios = $dao->listar();

// ── CONTADORES ──
$total_usuarios       = count($usuarios);
$total_activos        = count(array_filter($usuarios, fn($u) => $u['estado'] === 'activo'));
$total_inactivos      = count(array_filter($usuarios, fn($u) => $u['estado'] === 'inactivo'));
$total_deshabilitados = 0; // tu BD solo tiene activo/inactivo

// ── MAPAS ──
$etiqueta_rol = [
    '1' => 'Admin',
    '2' => 'Pastor',
];
$clase_rol = [
    '1' => 'rol-admin',
    '2' => 'rol-editor',
];
$etiqueta_estado = [
    'activo'   => 'Activo',
    'inactivo' => 'Inactivo',
];
$clase_estado = [
    'activo'   => 'estado-activo',
    'inactivo' => 'estado-inactivo',
];
$color_avatar = [
    '1' => '#38d9a9',
    '2' => '#4f6ef7',
];
?>

<!-- ── BARRA SUPERIOR ── -->
<header class="barra-superior">
    <h1>Gestión de Usuarios</h1>
    <button class="boton boton-primario" onclick="abrirModalCrear()">
        <i class="fa-solid fa-user-plus"></i> Nuevo usuario
    </button>
</header>

<!-- ── CONTENIDO PRINCIPAL ── -->
<main class="area-contenido">

    <!-- ESTADÍSTICAS -->
    <div class="cuadricula-estadisticas">
        <div class="tarjeta-estadistica">
            <div class="icono-estadistica azul"><i class="fa-solid fa-users"></i></div>
            <div class="datos-estadistica">
                <div class="valor"><?= $total_usuarios ?></div>
                <div class="etiqueta">Total usuarios</div>
            </div>
        </div>
        <div class="tarjeta-estadistica">
            <div class="icono-estadistica verde"><i class="fa-solid fa-circle-check"></i></div>
            <div class="datos-estadistica">
                <div class="valor"><?= $total_activos ?></div>
                <div class="etiqueta">Activos</div>
            </div>
        </div>
        <div class="tarjeta-estadistica">
            <div class="icono-estadistica naranja"><i class="fa-solid fa-clock"></i></div>
            <div class="datos-estadistica">
                <div class="valor"><?= $total_inactivos ?></div>
                <div class="etiqueta">Inactivos</div>
            </div>
        </div>
        <div class="tarjeta-estadistica">
            <div class="icono-estadistica rojo"><i class="fa-solid fa-ban"></i></div>
            <div class="datos-estadistica">
                <div class="valor"><?= $total_deshabilitados ?></div>
                <div class="etiqueta">Deshabilitados</div>
            </div>
        </div>
    </div>

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
                        $rolId    = $usuario['id_rol'];
                        $estado   = $usuario['estado'];
                        $username = $usuario['username'];
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
                            <div class="celda-acciones" style="justify-content: center;">
                                <button class="boton-icono" title="Editar usuario"
                                        onclick="abrirModalEditar(
                                            <?= $usuario['id'] ?>,
                                            '<?= addslashes($username) ?>',
                                            '<?= $rolId ?>',
                                            '<?= $estado ?>'
                                        )">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="boton-icono peligro" title="Eliminar usuario"
                                        onclick="abrirModalEliminar(<?= $usuario['id'] ?>, '<?= addslashes($username) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="padding: 14px 22px; border-top: 1px solid var(--borde);
                    display:flex; justify-content: space-between; align-items:center;">
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
        <form method="POST" action="/IglesiaDelNazarenoBagua/actualizar_usuario.php">
            <input type="hidden" id="editarId" name="id"/>
            <div class="grupo-formulario">
                <label>Username</label>
                <input type="text" id="editarUsername" name="username" placeholder="Username"/>
            </div>
            <div class="grupo-formulario">
                <label>Rol</label>
                <select id="editarRol" name="rol">
                    <option value="1">Admin</option>
                    <option value="2">Pastor</option>
                </select>
            </div>
            <div class="grupo-formulario">
                <label>Estado</label>
                <select id="editarEstado" name="estado">
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

<!-- MODAL ELIMINAR -->
<div class="superposicion-modal" id="modalEliminar">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalEliminar()">✕</button>
        <div class="icono-confirmacion"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="texto-confirmacion" style="font-weight:700; font-size:1.05rem;">¿Eliminar este usuario?</p>
        <p class="subtexto-confirmacion" id="textoEliminar">Esta acción no se puede deshacer.</p>
        <div class="fila-botones-modal">
            <button class="boton boton-contorno" onclick="cerrarModalEliminar()">Cancelar</button>
            <button class="boton boton-peligro" onclick="confirmarEliminar()">
                <i class="fa-solid fa-trash"></i> Sí, eliminar
            </button>
        </div>
    </div>
</div>

<!-- AVISO FLOTANTE -->
<div class="aviso" id="aviso">
    <i class="fa-solid fa-circle-check"></i>
    <span id="mensajeAviso">Acción completada</span>
</div>
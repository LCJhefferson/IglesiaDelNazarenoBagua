<?php
// ══════════════════════════════════════════════════
//  usuarios.php — Panel de Administración de Usuarios
//  Variables, comentarios y etiquetas en español
// ══════════════════════════════════════════════════

// ── DATOS DE USUARIOS (aquí irá la consulta MySQL en el futuro) ──
$usuarios = [
    [
        'id'        => 1,
        'nombre'    => 'Carlos Mendoza',
        'correo'    => 'carlos@iglesia.com',
        'rol'       => 'super_admin',
        'estado'    => 'activo',
        'fecha'     => '01 Ene 2026',
        'avatar'    => 'CM',
    ],
    [
        'id'        => 2,
        'nombre'    => 'María López',
        'correo'    => 'maria@iglesia.com',
        'rol'       => 'admin',
        'estado'    => 'activo',
        'fecha'     => '15 Feb 2026',
        'avatar'    => 'ML',
    ],
    [
        'id'        => 3,
        'nombre'    => 'Juan Pérez',
        'correo'    => 'juan@iglesia.com',
        'rol'       => 'editor',
        'estado'    => 'inactivo',
        'fecha'     => '20 Feb 2026',
        'avatar'    => 'JP',
    ],
    [
        'id'        => 4,
        'nombre'    => 'Ana Torres',
        'correo'    => 'ana@iglesia.com',
        'rol'       => 'editor',
        'estado'    => 'activo',
        'fecha'     => '05 Mar 2026',
        'avatar'    => 'AT',
    ],
    [
        'id'        => 5,
        'nombre'    => 'Pedro Ramírez',
        'correo'    => 'pedro@iglesia.com',
        'rol'       => 'lector',
        'estado'    => 'deshabilitado',
        'fecha'     => '10 Mar 2026',
        'avatar'    => 'PR',
    ],
    [
        'id'        => 6,
        'nombre'    => 'Sofía Vargas',
        'correo'    => 'sofia@iglesia.com',
        'rol'       => 'lector',
        'estado'    => 'activo',
        'fecha'     => '12 Abr 2026',
        'avatar'    => 'SV',
    ],
    [
        'id'        => 7,
        'nombre'    => 'Luis Castro',
        'correo'    => 'luis@iglesia.com',
        'rol'       => 'editor',
        'estado'    => 'inactivo',
        'fecha'     => '08 Abr 2026',
        'avatar'    => 'LC',
    ],
    [
        'id'        => 8,
        'nombre'    => 'Rosa Jiménez',
        'correo'    => 'rosa@iglesia.com',
        'rol'       => 'admin',
        'estado'    => 'deshabilitado',
        'fecha'     => '03 Abr 2026',
        'avatar'    => 'RJ',
    ],
];

// ── CONTADORES POR ESTADO ──
$total_usuarios      = count($usuarios);
$total_activos       = count(array_filter($usuarios, fn($u) => $u['estado'] === 'activo'));
$total_inactivos     = count(array_filter($usuarios, fn($u) => $u['estado'] === 'inactivo'));
$total_deshabilitados = count(array_filter($usuarios, fn($u) => $u['estado'] === 'deshabilitado'));

// ── MAPAS DE ROL: etiqueta visible y clase CSS ──
$etiqueta_rol = [
    'super_admin' => 'Super Admin',
    'admin'       => 'Admin',
    'editor'      => 'Editor',
    'lector'      => 'Lector',
];
$clase_rol = [
    'super_admin' => 'rol-super',
    'admin'       => 'rol-admin',
    'editor'      => 'rol-editor',
    'lector'      => 'rol-lector',
];

// ── MAPAS DE ESTADO: etiqueta visible y clase CSS ──
$etiqueta_estado = [
    'activo'        => 'Activo',
    'inactivo'      => 'Inactivo',
    'deshabilitado' => 'Deshabilitado',
];
$clase_estado = [
    'activo'        => 'estado-activo',
    'inactivo'      => 'estado-inactivo',
    'deshabilitado' => 'estado-deshabilitado',
];

// ── COLOR DE AVATAR SEGÚN ROL ──
$color_avatar = [
    'super_admin' => '#4f6ef7',
    'admin'       => '#38d9a9',
    'editor'      => '#f59f00',
    'lector'      => '#868e96',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Panel de Usuarios</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    
</head>
<body>


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

    <!-- TABLA DE USUARIOS -->
    <div class="contenedor-tabla">
        <div class="cabecera-tabla">
            <h2>Lista de Usuarios</h2>
            <span class="contador-tabla" id="contadorTabla">
                <?= $total_usuarios ?> usuarios
            </span>
        </div>

        <!-- Barra de herramientas -->
        <div style="padding: 16px 22px; border-bottom: 1px solid var(--borde);">
            <div class="barra-herramientas">
                <div class="contenedor-busqueda">
                    <i class="fa-solid fa-magnifying-glass icono-busqueda"></i>
                    <input type="text" class="campo-busqueda"
                           placeholder="Buscar por nombre o correo..."
                           oninput="filtrarUsuarios(this.value)"/>
                </div>
                <select class="selector-filtro" onchange="filtrarPorEstado(this.value)">
                    <option value="todos">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                    <option value="deshabilitado">Deshabilitados</option>
                </select>
                <select class="selector-filtro" onchange="filtrarPorRol(this.value)">
                    <option value="todos">Todos los roles</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="editor">Editor</option>
                    <option value="lector">Lector</option>
                </select>
            </div>
        </div>

        <!-- Tabla -->
        <div style="overflow-x: auto;">
            <table id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha registro</th>
                        <th style="text-align:center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla">
                <?php foreach ($usuarios as $usuario): ?>
                    <tr data-nombre="<?= strtolower($usuario['nombre']) ?>"
                        data-correo="<?= strtolower($usuario['correo']) ?>"
                        data-estado="<?= $usuario['estado'] ?>"
                        data-rol="<?= $usuario['rol'] ?>">

                        <!-- Usuario -->
                        <td>
                            <div class="celda-usuario">
                                <div class="avatar-usuario"
                                     style="background: <?= $color_avatar[$usuario['rol']] ?>">
                                    <?= $usuario['avatar'] ?>
                                </div>
                                <div>
                                    <div class="nombre-usuario"><?= htmlspecialchars($usuario['nombre']) ?></div>
                                    <div class="correo-usuario"><?= htmlspecialchars($usuario['correo']) ?></div>
                                </div>
                            </div>
                        </td>

                        <!-- Rol -->
                        <td>
                            <span class="badge-rol <?= $clase_rol[$usuario['rol']] ?>">
                                <?= $etiqueta_rol[$usuario['rol']] ?>
                            </span>
                        </td>

                        <!-- Estado -->
                        <td>
                            <span class="badge-estado <?= $clase_estado[$usuario['estado']] ?>">
                                <?= $etiqueta_estado[$usuario['estado']] ?>
                            </span>
                        </td>

                        <!-- Fecha -->
                        <td style="color: var(--texto-suave); font-size: 0.82rem;">
                            <?= $usuario['fecha'] ?>
                        </td>

                        <!-- Acciones -->
                        <td>
                            <div class="celda-acciones" style="justify-content: center;">
                                <button class="boton-icono"
                                        title="Editar usuario"
                                        onclick="abrirModalEditar(
                                            <?= $usuario['id'] ?>,
                                            '<?= addslashes($usuario['nombre']) ?>',
                                            '<?= addslashes($usuario['correo']) ?>',
                                            '<?= $usuario['rol'] ?>',
                                            '<?= $usuario['estado'] ?>'
                                        )">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="boton-icono peligro"
                                        title="Eliminar usuario"
                                        onclick="abrirModalEliminar(<?= $usuario['id'] ?>, '<?= addslashes($usuario['nombre']) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pie de tabla -->
        <div style="padding: 14px 22px; border-top: 1px solid var(--borde);
                    display:flex; justify-content: space-between; align-items:center;">
            <span style="font-size:0.8rem; color:var(--texto-suave);">
                Mostrando <span id="filasMostradas"><?= $total_usuarios ?></span> de <?= $total_usuarios ?> usuarios
            </span>
        </div>
    </div>

</main>

<!-- ══════════════════════════════
     MODAL: CREAR USUARIO
══════════════════════════════ -->
<div class="superposicion-modal" id="modalCrear">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalCrear()">✕</button>
        <h3>👤 Nuevo Usuario</h3>

        <div class="grupo-formulario">
            <label for="crearNombre">Nombre completo</label>
            <input type="text" id="crearNombre" placeholder="Ej: María López"/>
        </div>
        <div class="grupo-formulario">
            <label for="crearCorreo">Correo electrónico</label>
            <input type="email" id="crearCorreo" placeholder="correo@ejemplo.com"/>
        </div>
        <div class="grupo-formulario">
            <label for="crearContrasena">Contraseña</label>
            <input type="password" id="crearContrasena" placeholder="Mínimo 8 caracteres"/>
        </div>
        <div class="grupo-formulario">
            <label for="crearRol">Rol</label>
            <select id="crearRol">
                <option value="lector">Lector</option>
                <option value="editor">Editor</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
            </select>
        </div>
        <div class="grupo-formulario">
            <label for="crearEstado">Estado</label>
            <select id="crearEstado">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
                <option value="deshabilitado">Deshabilitado</option>
            </select>
        </div>

        <div class="fila-botones-modal">
            <button class="boton boton-contorno" onclick="cerrarModalCrear()">Cancelar</button>
            <button class="boton boton-primario" onclick="guardarNuevoUsuario()">
                <i class="fa-solid fa-user-plus"></i> Crear usuario
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════
     MODAL: EDITAR USUARIO
══════════════════════════════ -->
<div class="superposicion-modal" id="modalEditar">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalEditar()">✕</button>
        <h3>✏️ Editar Usuario</h3>

        <input type="hidden" id="editarId"/>
        <div class="grupo-formulario">
            <label for="editarNombre">Nombre completo</label>
            <input type="text" id="editarNombre" placeholder="Nombre del usuario"/>
        </div>
        <div class="grupo-formulario">
            <label for="editarCorreo">Correo electrónico</label>
            <input type="email" id="editarCorreo" placeholder="correo@ejemplo.com"/>
        </div>
        <div class="grupo-formulario">
            <label for="editarRol">Rol</label>
            <select id="editarRol">
                <option value="lector">Lector</option>
                <option value="editor">Editor</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
            </select>
        </div>
        <div class="grupo-formulario">
            <label for="editarEstado">Estado</label>
            <select id="editarEstado">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
                <option value="deshabilitado">Deshabilitado</option>
            </select>
        </div>

        <div class="fila-botones-modal">
            <button class="boton boton-contorno" onclick="cerrarModalEditar()">Cancelar</button>
            <button class="boton boton-primario" onclick="guardarCambiosUsuario()">
                <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════
     MODAL: CONFIRMAR ELIMINACIÓN
══════════════════════════════ -->
<div class="superposicion-modal" id="modalEliminar">
    <div class="caja-modal">
        <button class="cerrar-modal" onclick="cerrarModalEliminar()">✕</button>
        <div class="icono-confirmacion"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <p class="texto-confirmacion" style="font-weight:700; font-size:1.05rem;">
            ¿Eliminar este usuario?
        </p>
        <p class="subtexto-confirmacion" id="textoEliminar">
            Esta acción no se puede deshacer.
        </p>
        <div class="fila-botones-modal">
            <button class="boton boton-contorno" onclick="cerrarModalEliminar()">Cancelar</button>
            <button class="boton boton-peligro" onclick="confirmarEliminar()">
                <i class="fa-solid fa-trash"></i> Sí, eliminar
            </button>
        </div>
    </div>
</div>

<!-- ── AVISO FLOTANTE ── -->
<div class="aviso" id="aviso">
    <i class="fa-solid fa-circle-check"></i>
    <span id="mensajeAviso">Acción completada</span>
</div>

</body>
</html>
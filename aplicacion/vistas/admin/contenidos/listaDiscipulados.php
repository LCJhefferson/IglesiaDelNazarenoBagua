<?php
// discipulados.php — Módulo de gestión de discipulados
// Solo diseño visual, sin conexión a BD aún
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discipulados — Iglesia del Nazareno Bagua</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body>

    <div class="layout">

        <!-- ══════════ BARRA LATERAL ══════════ -->
        <?php include 'components/sidebar.php'; ?>

        <!-- ══════════ CONTENIDO ══════════ -->
        <main class="contenido">

            <!-- Encabezado -->
            <div class="page-header">
                <div>
                    <h1>Discipulados</h1>
                    <p>Gestión de miembros y grupos de discipulado</p>
                </div>
                <button class="btn btn-primary" onclick="abrirModal('modal-nuevo-discipulo')">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo Discipulado
                </button>
            </div>

            <!-- Tabla de discipulados -->
            <div class="card">
                <div class="card-header">
                    <h3>Lista de Discipulados</h3>
                    <span style="font-size:0.82rem; color:var(--gris);">4 registros</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Nivel</th>
                            <th>Nivel Disciplinado</th>
                            <th>Plataforma / Grupo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de ejemplo (datos ficticios) -->
                        <tr>
                            <td>01</td>
                            <td>
                                <strong>Ana Torres Ríos</strong><br>
                                <span style="font-size:0.78rem; color:var(--gris);">DNI: 45678901</span>
                            </td>
                            <td><span class="badge badge-nivel1">Nivel 1</span></td>
                            <td>Básico</td>
                            <td>Grupo Jóvenes A</td>
                            <td>
                                <button class="btn btn-sm btn-secondary"
                                    onclick="abrirModal('modal-editar-discipulo')">Editar</button>
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </td>
                        </tr>
                        <tr>
                            <td>02</td>
                            <td>
                                <strong>Carlos Mendoza</strong><br>
                                <span style="font-size:0.78rem; color:var(--gris);">DNI: 72345610</span>
                            </td>
                            <td><span class="badge badge-nivel2">Nivel 2</span></td>
                            <td>Intermedio</td>
                            <td>Grupo Adultos B</td>
                            <td>
                                <button class="btn btn-sm btn-secondary">Editar</button>
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </td>
                        </tr>
                        <tr>
                            <td>03</td>
                            <td>
                                <strong>Lucía Vargas Soto</strong><br>
                                <span style="font-size:0.78rem; color:var(--gris);">DNI: 61234509</span>
                            </td>
                            <td><span class="badge badge-nivel3">Nivel 3</span></td>
                            <td>Avanzado</td>
                            <td>Grupo Líderes</td>
                            <td>
                                <button class="btn btn-sm btn-secondary">Editar</button>
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </td>
                        </tr>
                        <tr>
                            <td>04</td>
                            <td>
                                <strong>Pedro Huanca</strong><br>
                                <span style="font-size:0.78rem; color:var(--gris);">DNI: 80123456</span>
                            </td>
                            <td><span class="badge badge-nivel1">Nivel 1</span></td>
                            <td>Básico</td>
                            <td>Grupo Jóvenes A</td>
                            <td>
                                <button class="btn btn-sm btn-secondary">Editar</button>
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <!-- ══════════ MODAL: NUEVO DISCIPULADO ══════════ -->
    <div class="form-overlay" id="modal-nuevo-discipulo">
        <div class="form-modal">
            <h2>Nuevo Discipulado</h2>
            <p>Completa los datos del nuevo discípulo</p>

            <div class="form-row">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" placeholder="Ej: Ana">
                </div>
                <div class="form-group">
                    <label>Apellidos</label>
                    <input type="text" placeholder="Ej: Torres Ríos">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" placeholder="Ej: 987654321">
                </div>
                <div class="form-group">
                    <label>DNI</label>
                    <input type="text" placeholder="Ej: 45678901" maxlength="8">
                </div>
            </div>

            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" placeholder="Ej: ana@correo.com">
            </div>

            <div class="form-actions">
                <button class="btn btn-secondary" onclick="cerrarModal('modal-nuevo-discipulo')">Cancelar</button>
                <button class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>

    <!-- ══════════ MODAL: EDITAR DISCIPULADO ══════════ -->
    <div class="form-overlay" id="modal-editar-discipulo">
        <div class="form-modal">
            <h2>Editar Discipulado</h2>
            <p>Modifica los datos del discípulo</p>

            <div class="form-row">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" value="Ana">
                </div>
                <div class="form-group">
                    <label>Apellidos</label>
                    <input type="text" value="Torres Ríos">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" value="987654321">
                </div>
                <div class="form-group">
                    <label>DNI</label>
                    <input type="text" value="45678901" maxlength="8">
                </div>
            </div>

            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" value="ana@correo.com">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nivel</label>
                    <select>
                        <option selected>Nivel 1 — Básico</option>
                        <option>Nivel 2 — Intermedio</option>
                        <option>Nivel 3 — Avanzado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Grupo asignado</label>
                    <select>
                        <option selected>Grupo Jóvenes A</option>
                        <option>Grupo Adultos B</option>
                        <option>Grupo Líderes</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-secondary" onclick="cerrarModal('modal-editar-discipulo')">Cancelar</button>
                <button class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </div>

    <!-- ══════════ SCRIPT ══════════ -->
    <script>
        function abrirModal(id) {
            document.getElementById(id).classList.add('visible');
        }
        function cerrarModal(id) {
            document.getElementById(id).classList.remove('visible');
        }
        // Cerrar al hacer clic fuera del modal
        document.querySelectorAll('.form-overlay').forEach(overlay => {
            overlay.addEventListener('click', function (e) {
                if (e.target === this) cerrarModal(this.id);
            });
        });
    </script>

</body>

</html>
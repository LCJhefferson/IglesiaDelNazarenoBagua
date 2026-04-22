
    // ── Variable para guardar el id del usuario a eliminar ──
    let idUsuarioEliminar = null;

    // ══════════════════════════════
    //  MENÚ HAMBURGUESA
    // ══════════════════════════════
    function alternarMenu() {
        const panel       = document.getElementById('panelMenu');
        const boton       = document.getElementById('btnHamburguesa');
        const fondoOscuro = document.getElementById('fondoOscuro');
        if (panel.classList.contains('visible')) {
            cerrarMenu();
        } else {
            panel.classList.add('visible');
            boton.classList.add('abierto');
            fondoOscuro.classList.add('visible');
        }
    }

    function cerrarMenu() {
        document.getElementById('panelMenu').classList.remove('visible');
        document.getElementById('btnHamburguesa').classList.remove('abierto');
        document.getElementById('fondoOscuro').classList.remove('visible');
    }

    // ══════════════════════════════
    //  FILTROS DE LA TABLA
    // ══════════════════════════════

    // Actualiza el contador de filas visibles
    function actualizarContador() {
        const filas   = document.querySelectorAll('#cuerpoTabla tr');
        let visibles  = 0;
        filas.forEach(fila => { if (fila.style.display !== 'none') visibles++; });
        document.getElementById('filasMostradas').textContent = visibles;
    }

    // Aplica todos los filtros activos a la vez
    function aplicarFiltros() {
        const textoBusqueda   = document.querySelector('.campo-busqueda').value.toLowerCase();
        const estadoSeleccion = document.querySelectorAll('.selector-filtro')[0].value;
        const rolSeleccion    = document.querySelectorAll('.selector-filtro')[1].value;

        document.querySelectorAll('#cuerpoTabla tr').forEach(fila => {
            const nombre = fila.dataset.nombre || '';
            const correo = fila.dataset.correo || '';
            const estado = fila.dataset.estado || '';
            const rol    = fila.dataset.rol    || '';

            const coincideTexto  = nombre.includes(textoBusqueda) || correo.includes(textoBusqueda);
            const coincideEstado = estadoSeleccion === 'todos' || estado === estadoSeleccion;
            const coincideRol    = rolSeleccion    === 'todos' || rol    === rolSeleccion;

            fila.style.display = (coincideTexto && coincideEstado && coincideRol) ? '' : 'none';
        });
        actualizarContador();
    }

    function filtrarUsuarios(valor) { aplicarFiltros(); }
    function filtrarPorEstado(valor) { aplicarFiltros(); }
    function filtrarPorRol(valor)    { aplicarFiltros(); }

    // ══════════════════════════════
    //  MODAL: CREAR USUARIO
    // ══════════════════════════════
    function abrirModalCrear() {
        document.getElementById('crearNombre').value    = '';
        document.getElementById('crearCorreo').value    = '';
        document.getElementById('crearContrasena').value = '';
        document.getElementById('crearRol').value       = 'lector';
        document.getElementById('crearEstado').value    = 'activo';
        document.getElementById('modalCrear').classList.add('abierto');
    }

    function cerrarModalCrear() {
        document.getElementById('modalCrear').classList.remove('abierto');
    }

    function guardarNuevoUsuario() {
        const nombre = document.getElementById('crearNombre').value.trim();
        if (!nombre) { mostrarAviso('El nombre es obligatorio', 'error'); return; }
        mostrarAviso('Usuario "' + nombre + '" creado correctamente', 'exito');
        cerrarModalCrear();
    }

    // ══════════════════════════════
    //  MODAL: EDITAR USUARIO
    // ══════════════════════════════
    function abrirModalEditar(id, nombre, correo, rol, estado) {
        document.getElementById('editarId').value     = id;
        document.getElementById('editarNombre').value = nombre;
        document.getElementById('editarCorreo').value = correo;
        document.getElementById('editarRol').value    = rol;
        document.getElementById('editarEstado').value = estado;
        document.getElementById('modalEditar').classList.add('abierto');
    }

    function cerrarModalEditar() {
        document.getElementById('modalEditar').classList.remove('abierto');
    }

    function guardarCambiosUsuario() {
        const nombre = document.getElementById('editarNombre').value.trim();
        if (!nombre) { mostrarAviso('El nombre es obligatorio', 'error'); return; }
        mostrarAviso('Usuario "' + nombre + '" actualizado', 'exito');
        cerrarModalEditar();
    }

    // ══════════════════════════════
    //  MODAL: ELIMINAR USUARIO
    // ══════════════════════════════
    function abrirModalEliminar(id, nombre) {
        idUsuarioEliminar = id;
        document.getElementById('textoEliminar').textContent =
            'Se eliminará permanentemente a "' + nombre + '". Esta acción no se puede deshacer.';
        document.getElementById('modalEliminar').classList.add('abierto');
    }

    function cerrarModalEliminar() {
        document.getElementById('modalEliminar').classList.remove('abierto');
        idUsuarioEliminar = null;
    }

    function confirmarEliminar() {
        mostrarAviso('Usuario eliminado correctamente', 'exito');
        cerrarModalEliminar();
    }

    // ══════════════════════════════
    //  AVISO FLOTANTE (TOAST)
    // ══════════════════════════════
    function mostrarAviso(mensaje, tipo = 'exito') {
        const cajaAviso  = document.getElementById('aviso');
        const iconoAviso = cajaAviso.querySelector('i');
        document.getElementById('mensajeAviso').textContent = mensaje;
        cajaAviso.className = 'aviso ' + tipo;
        iconoAviso.className = tipo === 'exito'
            ? 'fa-solid fa-circle-check'
            : 'fa-solid fa-circle-xmark';
        cajaAviso.classList.add('visible');
        setTimeout(() => cajaAviso.classList.remove('visible'), 2800);
    }

    // ── Cerrar modales al hacer clic fuera ──
    ['modalCrear', 'modalEditar', 'modalEliminar'].forEach(idModal => {
        document.getElementById(idModal).addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('abierto');
        });
    });
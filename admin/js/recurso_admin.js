const titulos = { publico:'Vista Pública', subir:'Subir Archivo', archivos:'Mis Archivos', papelera:'Papelera' };

    // ── MENÚ ──
    function alternarMenu() {
        const panel = document.getElementById('panelMenu');
        const boton = document.getElementById('btnHamburguesa');
        const fondo = document.getElementById('fondoOscuro');
        panel.classList.contains('visible') ? cerrarMenu() : (panel.classList.add('visible'), boton.classList.add('abierto'), fondo.classList.add('visible'));
    }
    function cerrarMenu() {
        document.getElementById('panelMenu').classList.remove('visible');
        document.getElementById('btnHamburguesa').classList.remove('abierto');
        document.getElementById('fondoOscuro').classList.remove('visible');
    }

    // ── PÁGINAS ──
    function mostrarPagina(id) {
        document.querySelectorAll('.pagina').forEach(p => p.classList.remove('activa'));
        document.querySelectorAll('.opcion-menu').forEach(op => op.classList.remove('activo'));
        document.getElementById('pagina-' + id).classList.add('activa');
        document.getElementById('tituloPagina').textContent = titulos[id] || '';
        const op = document.getElementById('op-' + id);
        if (op) op.classList.add('activo');
        cerrarMenu();
    }

    // ── FILTROS ──
    function filtrarArchivos(consulta) {
        document.querySelectorAll('#todosArchivos .tarjeta-archivo').forEach(t => {
            t.style.display = t.querySelector('.nombre-archivo').textContent.toLowerCase().includes(consulta.toLowerCase()) ? '' : 'none';
        });
    }
    function filtrarPorTipo(tipo) {
        document.querySelectorAll('#todosArchivos .tarjeta-archivo').forEach(t => {
            if (tipo === 'todos') { t.style.display = ''; return; }
            const etiqueta = t.querySelector('.etiqueta-archivo');
            t.style.display = etiqueta && etiqueta.textContent.toLowerCase().includes(tipo) ? '' : 'none';
        });
    }

    // ── VISTA PREVIA FORMULARIO ──
    function actualizarPrevista() {
        const titulo = document.getElementById('campoTitulo').value;
        const desc   = document.getElementById('campoDescripcion').value;
        document.getElementById('tituloPrevio').textContent      = titulo || 'Título del archivo';
        document.getElementById('descripcionPrevia').textContent = desc   || 'La descripción aparecerá aquí...';
    }
    function manejarSeleccion(campo) { if (campo.files[0]) mostrarArchivoSeleccionado(campo.files[0]); }
    function manejarSoltado(e) {
        e.preventDefault();
        document.getElementById('zonaArrastre').classList.remove('arrastrando');
        if (e.dataTransfer.files[0]) mostrarArchivoSeleccionado(e.dataTransfer.files[0]);
    }
    function mostrarArchivoSeleccionado(archivo) {
        const zona = document.getElementById('zonaArrastre');
        const ext  = archivo.name.split('.').pop().toUpperCase();
        const mb   = (archivo.size / 1024 / 1024).toFixed(2);
        zona.innerHTML = `
            <div style="display:flex;align-items:center;gap:12px;text-align:left;">
                <div style="width:44px;height:44px;border-radius:10px;background:#eaf3de;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.4rem;">📄</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${archivo.name}</div>
                    <div style="font-size:.75rem;color:var(--texto-suave);margin-top:2px;">${mb} MB · ${ext}</div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <button type="button" class="boton boton-contorno" style="padding:6px 14px;font-size:.78rem;" onclick="cambiarArchivo()"><i class="fa-solid fa-pen"></i> Cambiar</button>
                    <button type="button" class="boton" style="padding:6px 10px;font-size:.78rem;background:transparent;border:1.5px solid var(--borde);color:var(--peligro);" onclick="quitarArchivo()"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>`;
        zona.style.border = '2px solid var(--exito)';
        zona.style.background = 'var(--blanco)';
        zona.style.padding = '16px 18px';
        zona.style.cursor = 'default';
        zona.onclick = null;
    }
    function cambiarArchivo() { document.getElementById('campoPrincipal').click(); }
    function quitarArchivo() {
        const zona = document.getElementById('zonaArrastre');
        zona.style.border = zona.style.background = zona.style.padding = zona.style.cursor = '';
        zona.onclick = () => document.getElementById('campoPrincipal').click();
        zona.innerHTML = `<i class="fa-solid fa-cloud-arrow-up"></i><p>Arrastra un archivo aquí o <span>selecciona uno</span></p><p style="margin-top:6px;font-size:.75rem">PDF, imágenes, videos — Máx. 50MB</p>`;
        document.getElementById('campoPrincipal').value = '';
    }
    function limpiarFormulario() {
        document.getElementById('campoId').value = '';
        document.getElementById('campoTitulo').value = '';
        document.getElementById('campoDescripcion').value = '';
        document.getElementById('campoCategoria').value = '';
        document.getElementById('campoYoutube').value = '';
        document.getElementById('campoRutaActual').value = '';
        document.getElementById('campoTipoActual').value = '';
        document.getElementById('tituloFormulario').textContent = '📤 Subir nuevo archivo';
        document.getElementById('textoBotonGuardar').textContent = 'Publicar archivo';
        quitarArchivo();
        actualizarPrevista();
    }

    // ── MODAL EDITAR ──
    // Recibe los datos del archivo directo desde PHP (ya saneados)
    function abrirModalEditar(id, titulo, descripcion, categoria, tipo, ruta, youtube) {
        document.getElementById('editarId').value          = id;
        document.getElementById('editarTitulo').value      = titulo;
        document.getElementById('editarDescripcion').value = descripcion;
        document.getElementById('editarCategoria').value   = categoria;
        document.getElementById('editarTipoActual').value  = tipo;
        document.getElementById('editarRuta').value        = ruta;
        document.getElementById('editarYoutube').value     = youtube;
        document.getElementById('modalEditar').classList.add('abierto');
    }
    function cerrarModalEditar() {
        document.getElementById('modalEditar').classList.remove('abierto');
    }

    // ── MODAL CONFIRMAR ELIMINAR (a papelera) ──
 function confirmarEliminar(id, nombre) {
    document.getElementById('textoConfirmarEliminar').innerHTML =
        `"<strong>${nombre}</strong>" se moverá a la papelera. Podrás restaurarlo después.`;
    document.getElementById('enlaceConfirmarEliminar').href =
        '/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=recurso_admin&eliminar=' + id;
    document.getElementById('modalConfirmarEliminar').classList.add('abierto');
}
    function cerrarModalConfirmar() {
        document.getElementById('modalConfirmarEliminar').classList.remove('abierto');
    }

    // ── MODAL CONFIRMAR ELIMINAR DEFINITIVO ──
  function confirmarEliminarDefinitivo(id, nombre) {
    document.getElementById('textoConfirmarDefinitivo').innerHTML =
        `"<strong>${nombre}</strong>" será eliminado <strong>permanentemente</strong>. Esta acción no se puede deshacer.`;
    document.getElementById('enlaceConfirmarDefinitivo').href =
        '/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php?vista=recurso_admin&eliminar_definitivo=' + id;
    document.getElementById('modalConfirmarDefinitivo').classList.add('abierto');
}
    function cerrarModalDefinitivo() {
        document.getElementById('modalConfirmarDefinitivo').classList.remove('abierto');
    }

    // ── TOAST ──
    function mostrarAviso(mensaje, tipo = 'exito') {
        const caja = document.getElementById('aviso');
        document.getElementById('mensajeAviso').textContent = mensaje;
        caja.className = `aviso ${tipo}`;
        caja.querySelector('i').className = tipo === 'exito' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark';
        caja.classList.add('visible');
        setTimeout(() => caja.classList.remove('visible'), 3000);
    }

    // ── CERRAR MODALES AL HACER CLIC FUERA ──
    ['modalEditar','modalConfirmarEliminar','modalConfirmarDefinitivo'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('abierto');
            }
        });
    });


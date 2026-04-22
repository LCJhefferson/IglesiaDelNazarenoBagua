

    const titulos = {
        publico:  'Vista Pública',
        subir:    'Subir Archivo',
        archivos: 'Mis Archivos',
        papelera: 'Papelera'
    };

    let idArchivoEditando = null;

    function alternarMenu() {
        const panel  = document.getElementById('panelMenu');
        const boton  = document.getElementById('btnHamburguesa');
        const fondo  = document.getElementById('fondoOscuro');
        if (panel.classList.contains('visible')) {
            cerrarMenu();
        } else {
            panel.classList.add('visible');
            boton.classList.add('abierto');
            fondo.classList.add('visible');
        }
    }

    function cerrarMenu() {
        document.getElementById('panelMenu').classList.remove('visible');
        document.getElementById('btnHamburguesa').classList.remove('abierto');
        document.getElementById('fondoOscuro').classList.remove('visible');
    }

    function mostrarPagina(id) {
        document.querySelectorAll('.pagina').forEach(p => p.classList.remove('activa'));
        document.querySelectorAll('.opcion-menu').forEach(op => op.classList.remove('activo'));
        document.getElementById('pagina-' + id).classList.add('activa');
        document.getElementById('tituloPagina').textContent = titulos[id] || '';
        const op = document.getElementById('op-' + id);
        if (op) op.classList.add('activo');
        cerrarMenu();
    }

    function filtrarArchivos(consulta) {
        document.querySelectorAll('#todosArchivos .tarjeta-archivo').forEach(t => {
            t.style.display = t.querySelector('.nombre-archivo').textContent.toLowerCase().includes(consulta.toLowerCase()) ? '' : 'none';
        });
    }

    function filtrarPorTipo(tipo) {
        document.querySelectorAll('#todosArchivos .tarjeta-archivo').forEach(t => {
            if (tipo === 'todos') { t.style.display = ''; return; }
            t.style.display = t.querySelector('.etiqueta-archivo').textContent.toLowerCase().includes(tipo) ? '' : 'none';
        });
    }

    function actualizarPrevista() {
        const titulo = document.getElementById('campoTitulo').value;
        const desc   = document.getElementById('campoDescripcion').value;
        document.getElementById('tituloPrevio').textContent      = titulo || 'Título del archivo';
        document.getElementById('descripcionPrevia').textContent = desc   || 'La descripción aparecerá aquí...';
    }

    function manejarSeleccion(campo) {
        if (campo.files[0]) mostrarArchivoSeleccionado(campo.files[0]);
    }

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
                    <button type="button" class="boton boton-contorno" style="padding:6px 14px;font-size:.78rem;border-radius:6px;" onclick="cambiarArchivo()"><i class="fa-solid fa-pen"></i> Cambiar</button>
                    <button type="button" class="boton" style="padding:6px 10px;font-size:.78rem;border-radius:6px;background:transparent;border:1.5px solid var(--borde);color:var(--peligro);" onclick="quitarArchivo()"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>`;
        zona.style.border     = '2px solid var(--exito)';
        zona.style.background = 'var(--blanco)';
        zona.style.padding    = '16px 18px';
        zona.style.cursor     = 'default';
        zona.onclick          = null;
    }

    function cambiarArchivo() { document.getElementById('campoPrincipal').click(); }

    function quitarArchivo() {
        const zona = document.getElementById('zonaArrastre');
        zona.style.border = zona.style.background = zona.style.padding = zona.style.cursor = '';
        zona.onclick = () => document.getElementById('campoPrincipal').click();
        zona.innerHTML = `<i class="fa-solid fa-cloud-arrow-up"></i><p>Arrastra un archivo aquí o <span>selecciona uno</span></p><p style="margin-top:6px;font-size:.75rem">PDF, imágenes, videos — Máx. 50MB</p>`;
        document.getElementById('campoPrincipal').value = '';
    }

    function abrirModalEditar(id) {
        const datos = {
            1:{nombre:'Guía 40 días de oración',descripcion:'Material de oración para la temporada de pentecostés.',categoria:'documentos',fecha:'10 Abr 2026'},
            2:{nombre:'Gráficos Cuaresma Redes',descripcion:'Gráficos para redes sociales, domingo, jueves y viernes.',categoria:'imagenes',fecha:'05 Abr 2026'},
            3:{nombre:'Semana Mundial de Oración',descripcion:'Recursos y video para la semana mundial de oración 2026.',categoria:'videos',fecha:'01 Mar 2026'},
            4:{nombre:'Manual de Liderazgo',descripcion:'Guía completa para líderes de célula y ministerios.',categoria:'documentos',fecha:'20 Feb 2026'},
            5:{nombre:'Fotografías Evento Anual',descripcion:'Galería fotográfica del encuentro regional anual.',categoria:'imagenes',fecha:'15 Ene 2026'},
            6:{nombre:'Sermón Domingo de Ramos',descripcion:'Video del sermón especial para domingo de ramos.',categoria:'videos',fecha:'12 Abr 2026'},
        };
        idArchivoEditando = id;
        const d = datos[id];
        if (d) {
            document.getElementById('editarNombre').value      = d.nombre;
            document.getElementById('editarDescripcion').value = d.descripcion;
            document.getElementById('editarCategoria').value   = d.categoria;
            document.getElementById('editarFecha').value       = d.fecha;
        }
        document.getElementById('modalEditar').classList.add('abierto');
    }

    function cerrarModalEditar() {
        document.getElementById('modalEditar').classList.remove('abierto');
        idArchivoEditando = null;
    }

    function guardarCambios() {
        mostrarAviso('Archivo "' + document.getElementById('editarNombre').value + '" actualizado', 'exito');
        cerrarModalEditar();
    }

    function mostrarAviso(mensaje, tipo = 'exito') {
        const caja = document.getElementById('aviso');
        document.getElementById('mensajeAviso').textContent = mensaje;
        caja.className = `aviso ${tipo}`;
        caja.querySelector('i').className = tipo === 'exito' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark';
        caja.classList.add('visible');
        setTimeout(() => caja.classList.remove('visible'), 2800);
    }

    // Chips de filtro
    document.querySelectorAll('.chip-filtro').forEach(chip => {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.chip-filtro').forEach(c => c.classList.remove('activo'));
            this.classList.add('activo');
        });
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('modalEditar').addEventListener('click', function (e) {
        if (e.target === this) cerrarModalEditar();
    });

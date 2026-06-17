<link rel="stylesheet" href="reportes.css">

<header class="barra-superior">
    <div class="barra-info">
        <h1><i class="fas fa-users-cog"></i>Reportes</h1>
        <p>Reportes por filtro personalizados</p>
    </div>
</header>


<div class="reportes-tabs">
    <button class="tab-btn " onclick="cambiarPestaña('miembros', this)">👤 Reporte de Miembros</button>
    <button class="tab-btn" onclick="cambiarPestaña('visitas', this)">🚗 Reporte de Visitas</button>
    <button class="tab-btn" onclick="cambiarPestaña('discipulado', this)">📖 Discipulado Avanzado</button>
    <button class="tab-btn" onclick="cambiarPestaña('cumpleanos', this)">🎂 Cumpleaños</button>
</div>



<div class="card-reporte " id="pane-miembros">
    <h2>Reporte de Miembros</h2>
    <form id="form-miembros" class="grid-filtros" onsubmit="event.preventDefault();">
    
    <div>
        <label>Buscar Miembro Específico:</label>
        <div class="autocomplete-container" style="position: relative;">
            <input type="text" id="input-miembro" placeholder="Escribe el nombre del miembro..." oninput="window.ejecutarAutocomplete('miembros', this)" autocomplete="off">
            <input type="hidden" name="miembro_id" id="hidden-miembro">
            <div id="res-miembros" class="autocomplete-resultados" style="display: none; position: absolute; width: 100%; background: white; border: 1px solid #ccc; z-index: 100; max-height: 200px; overflow-y: auto;"></div>
        </div>
    </div>
        <div>
            <label>Condición:</label>
            <select name="condicion" id="select-condiciones" onchange="cargarVistaPrevia('miembros')">
                <option value="">Todas</option>
            </select>
        </div>
        <div>
            <label>Estado:</label>
            <select name="estado" onchange="cargarVistaPrevia('miembros')">
                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
        <div class="campo-corto">
            <label>Edad Mín:</label>
            <input type="number" name="edad_min" oninput="cargarVistaPrevia('miembros')" placeholder="0">
        </div>
        <div class="campo-corto">
            <label>Edad Máx:</label>
            <input type="number" name="edad_max" oninput="cargarVistaPrevia('miembros')" placeholder="100">
        </div>
    </form>
    
    <div class="contenedor-botones">
        <button type="button" onclick="limpiarFiltros('miembros')" style="background-color: #ffc117; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🗑️ Limpiar Filtros
        </button>
        <button class="btn-exportar btn-pdf" onclick="descargar('miembros','pdf')">📄 PDF</button>
        <button class="btn-exportar btn-excel" onclick="descargar('miembros','excel')">📊 Excel</button>
        <button class="btn-exportar btn-csv" onclick="descargar('miembros','csv')">📝 CSV</button>
    </div>

    <table class="tabla-preview" id="tabla-miembros">
        <thead>
            <tr>
                <th>Nombre y Apellidos</th>
                <th>Teléfono</th>
                <th>Edad</th>
                <th>Dirección</th>
                <th>Origen</th>
                <th>Condición</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="card-reporte" id="pane-visitas">
    <h2>Reporte de Control de Visitas</h2>
    <form id="form-visitas" class="grid-filtros filtros-visitas">
        <div>
            <label>Buscar por Nombre:</label>
            <input type="text" name="buscar_nombre" placeholder="Escribe el nombre del miembro..." oninput="cargarVistaPrevia('visitas')" autocomplete="off">
        </div>

        <div>
            <label>Estado:</label>
            <select name="estado" onchange="cargarVistaPrevia('visitas')">
                <option value="">Todos</option>
                <option value="Visitado reciente">Visitado reciente</option>
                <option value="Visitado intermedio">Visitado intermedio</option>
                <option value="Pendiente próximo">Pendiente próximo</option>
                <option value="Pendiente crítico">Pendiente crítico</option>
            </select>
        </div>
        <div>
            <label>Motivo:</label>
            <select name="motivo" onchange="cargarVistaPrevia('visitas')">
                <option value="">Todos</option>
                <option value="Visita Regular">Visita Regular</option>
                <option value="Por Enfermedad">Por Enfermedad</option>
                <option value="Evangelística">Evangelística</option>
                <option value="Otros">Otros (Filtro Excluyente)</option>
            </select>
        </div>
        <div>
            <label>Desde:</label>
            <input type="date" name="fecha_inicio"  onchange="cargarVistaPrevia('visitas')">
        </div>
        <div>
            <label>Hasta:</label>
            <input type="date" name="fecha_fin" onchange="cargarVistaPrevia('visitas')">
        </div>
    </form>

    <div class="contenedor-botones">
        <button type="button" onclick="limpiarFiltros('visitas')" style="background-color: #ffc117; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🗑️ Limpiar Filtros
        </button>
        <button class="btn-exportar btn-pdf" onclick="descargar('visitas','pdf')">📄 PDF</button>
        <button class="btn-exportar btn-excel" onclick="descargar('visitas','excel')">📊 Excel</button>
        <button class="btn-exportar btn-csv" onclick="descargar('visitas','csv')">📝 CSV</button>
    </div>

    <table class="tabla-preview" id="tabla-visitas">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Dirección</th>
                <th>Última Visita</th>
                <th>Motivo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="card-reporte" id="pane-discipulado">
    <h2>Reporte de Discipulado Avanzado</h2>
    <form id="form-discipulado" class="grid-filtros filtros-discipulado-avanzado" onsubmit="event.preventDefault();">
        
        <div class="autocomplete-container" style="position:relative;">
            <label>Miembro Integrante:</label>
            <input type="text" id="input-discipulado-alumno" placeholder="Escribe para buscar miembro..." oninput="window.ejecutarAutocomplete('discipulado_alumno', this)" autocomplete="off">
            <input type="hidden" name="miembro_id" id="hidden-discipulado-alumno">
            <div class="autocomplete-resultados" id="res-discipulado_alumno" style="display: none; position: absolute; width: 100%; background: white; border: 1px solid #ccc; z-index: 100; max-height: 200px; overflow-y: auto;"></div>
        </div>
        
        <div class="autocomplete-container" style="position:relative;">
            <label>Grupo / Clase:</label>
            <input type="text" id="input-grupo" placeholder="Escribe para buscar grupo..." oninput="window.ejecutarAutocomplete('grupos', this)" autocomplete="off">
            <input type="hidden" name="grupo_id" id="hidden-grupo">
            <div class="autocomplete-resultados" id="res-grupos"></div>
        </div>
        
        <div class="autocomplete-container" style="position:relative;">
            <label>Discipulador / Líder:</label>
            <input type="text" id="input-discipulador" placeholder="Escribe para buscar líder..." oninput="window.ejecutarAutocomplete('discipuladores', this)" autocomplete="off">
            <input type="hidden" name="discipulador_id" id="hidden-discipulador">
            <div class="autocomplete-resultados" id="res-discipuladores"></div>
        </div>
        
        <div>
            <label>Estado del Integrante:</label>
            <select name="estado_discipulo_id" id="select-estados-discipulo" onchange="cargarVistaPrevia('discipulado')">
                <option value="">Cargando estados...</option>
            </select>
        </div>
    </form>

    <div class="contenedor-botones">
        <button type="button" onclick="limpiarFiltros('discipulado')" style="background-color: #ffc117; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🗑️ Limpiar Filtros
        </button>
        <button class="btn-exportar btn-pdf" onclick="descargar('discipulado','pdf')">📄 PDF</button>
        <button class="btn-exportar btn-excel" onclick="descargar('discipulado','excel')">📊 Excel</button>
        <button class="btn-exportar btn-csv" onclick="descargar('discipulado','csv')">📝 CSV</button>
    </div>

    <table class="tabla-preview" id="tabla-discipulado">
        <thead>
            <tr>
                <th>Grupo de Crecimiento</th>
                <th>Miembro / Alumno</th>
                <th>Estado del Alumno</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="card-reporte" id="pane-cumpleanos">
    <h2>Reporte Mensual de Cumpleaños</h2>
    <form id="form-cumpleanos" class="grid-filtros" onsubmit="event.preventDefault();">
        
        <div>
            <label>Seleccionar Mes:</label>
            <select name="mes_cumpleanos" id="select-mes-cumpleanos" onchange="cargarVistaPrevia('cumpleanos')">
                <option value="">-- Seleccione un Mes --</option>
                <option value="todos">📅 Todos los meses</option> <!-- NUEVA OPCIÓN -->
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>
        </div>

        <div>
            <label>Buscar Cumpleañero:</label>
            <div class="autocomplete-container" style="position: relative;">
                <input type="text" id="input-cumpleanero" placeholder="Escribe el nombre..." onkeyup="window.ejecutarAutocomplete('cumpleanos', this)" autocomplete="off">
                <input type="hidden" name="miembro_id" id="hidden-cumpleanero">
                <div id="res-cumpleanos" class="autocomplete-resultados" style="display: none; position: absolute; width: 100%; background: white; border: 1px solid #ccc; z-index: 100; max-height: 200px; overflow-y: auto;"></div>
            </div>
        </div>

        <div>
            <label>Edad Mínima:</label>
            <input type="number" name="edad_min" placeholder="Ej. 18" min="0" oninput="cargarVistaPrevia('cumpleanos')">
        </div>

        <div>
            <label>Edad Máxima:</label>
            <input type="number" name="edad_max" placeholder="Ej. 60" min="0" oninput="cargarVistaPrevia('cumpleanos')">
        </div>
    </form>

    <div class="contenedor-botones">
        <button type="button" onclick="limpiarFiltros('cumpleanos')" style="background-color: #ffc117; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🗑️ Limpiar Filtros
        </button>
        <button class="btn-exportar btn-pdf" onclick="descargar('cumpleanos','pdf')">📄 PDF</button>
        <button class="btn-exportar btn-excel" onclick="descargar('cumpleanos','excel')">📊 Excel</button>
    </div>

    <table class="tabla-preview" id="tabla-cumpleanos">
        <thead>
            <tr>
                <th>Miembro / Cumpleañero</th>
                <th>Teléfono</th>
                <th>Fecha de Nacimiento</th>
                <th>Edad Actual</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" style="text-align:center; padding:15px; color:#aaa;">Seleccione un filtro para iniciar la búsqueda.</td>
            </tr>
        </tbody>
    </table>
</div>

<script src="reportes.js"></script>
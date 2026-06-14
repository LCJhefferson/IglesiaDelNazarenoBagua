<link rel="stylesheet" href="reportes.css">

<header class="barra-superior">
    <div class="barra-info">
        <h1><i class="fas fa-users-cog"></i>Reportes</h1>
        <p>Reportes por filtro personalizados</p>
    </div>
</header>

<div class="card-reporte">
    <h2>Reporte de Miembros</h2>
    <form id="form-miembros" class="grid-filtros filtros-miembros">
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
        <button type="button" onclick="limpiarFiltrosMiembros()" style="background-color: #ffc117; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
    🗑️ Limpiar Filtros</button>
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

<div class="card-reporte">
    <h2>Reporte de Visitas</h2>
    <form id="form-visitas" class="grid-filtros filtros-visitas">
        <div>
            <label>Estado:</label>
            <select name="estado" onchange="cargarVistaPrevia('visitas')">
                <option value="">Todos</option>
                <option value="Realizada">Realizada</option>
                <option value="Pendiente">Pendiente</option>
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
            <input type="date" name="fecha_inicio" onchange="cargarVistaPrevia('visitas')">
        </div>
        <div>
            <label>Hasta:</label>
            <input type="date" name="fecha_fin" onchange="cargarVistaPrevia('visitas')">
        </div>
    </form>
    <div class="contenedor-botones">
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

<div class="card-reporte">
    <h2>Reporte de Discipulado</h2>
    <form id="form-discipulado" class="grid-filtros filtros-discipulado-avanzado">
        <div class="autocomplete-container" style="position:relative;">
            <label>Miembro Integrante:</label>
            <input type="text" id="input-miembro" placeholder="Escribe para buscar miembro..." oninput="ejecutarAutocomplete('miembros', this)">
            <input type="hidden" name="miembro_id" id="hidden-miembro">
            <div class="autocomplete-resultados" id="res-miembros"></div>
        </div>
        <div class="autocomplete-container" style="position:relative;">
            <label>Grupo:</label>
            <input type="text" id="input-grupo" placeholder="Escribe para buscar grupo..." oninput="ejecutarAutocomplete('grupos', this)">
            <input type="hidden" name="grupo_id" id="hidden-grupo">
            <div class="autocomplete-resultados" id="res-grupos"></div>
        </div>
        <div class="autocomplete-container" style="position:relative;">
            <label>Discipulador:</label>
            <input type="text" id="input-discipulador" placeholder="Escribe para buscar líder..." oninput="ejecutarAutocomplete('discipuladores', this)">
            <input type="hidden" name="discipulador_id" id="hidden-discipulador">
            <div class="autocomplete-resultados" id="res-discipuladores"></div>
        </div>
        <div>
            <label>Estado del Integrante:</label>
            <select name="estado_alumno" onchange="cargarVistaPrevia('discipulado')">
                <option value="">Todos</option>
                <option value="proceso">Activo / En proceso</option>
                <option value="1">Aprobado</option>
                <option value="0">Desaprobado / Retirado</option>
            </select>
        </div>
    </form>
    <div class="contenedor-botones">
        <button class="btn-exportar btn-pdf" onclick="descargar('discipulado','pdf')">📄 PDF</button>
        <button class="btn-exportar btn-excel" onclick="descargar('discipulado','excel')">📊 Excel</button>
        <button class="btn-exportar btn-csv" onclick="descargar('discipulado','csv')">📝 CSV</button>
    </div>
    <div class="info-bloque" id="bloque-meta-discipulado" style="display:none; background:#f3f4f6; padding:10px; border-radius:5px; margin: 10px 0;">
        <div>Nombre del grupo: <span id="meta-grupo" style="text-decoration: underline; font-weight:bold;"></span></div>
    </div>
    <table class="tabla-preview" id="tabla-discipulado">
        <thead>
            <tr>
                <th>Integrante(s)</th>
                <th>Estado Integrante</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="card-reporte">
    <h2>Reporte de Cumpleaños</h2>
    <form id="form-cumpleanos" class="grid-filtros filtros-cumpleanos">
        <div>
            <label>Mes de Onomástico:</label>
            <select name="mes_cumple" onchange="cargarVistaPrevia('cumpleanos')">
                <option value="">Todos los meses</option>
                <option value="01">Enero</option>
                <option value="02">Febrero</option>
                <option value="03">Marzo</option>
                <option value="04">Abril</option>
                <option value="05">Mayo</option>
                <option value="06">Junio</option>
                <option value="07">Julio</option>
                <option value="08">Agosto</option>
                <option value="09">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>
        </div>
        <div>
            <label>Fecha Inicio:</label>
            <input type="date" name="fecha_inicio" onchange="cargarVistaPrevia('cumpleanos')">
        </div>
        <div>
            <label>Fecha Fin:</label>
            <input type="date" name="fecha_fin" onchange="cargarVistaPrevia('cumpleanos')">
        </div>
    </form>
    <div class="contenedor-botones">
        <button class="btn-exportar btn-pdf" onclick="descargar('cumpleanos','pdf')">📄 PDF</button>
        <button class="btn-exportar btn-excel" onclick="descargar('cumpleanos','excel')">📊 Excel</button>
        <button class="btn-exportar btn-csv" onclick="descargar('cumpleanos','csv')">📝 CSV</button>
    </div>
    <table class="tabla-preview" id="tabla-cumpleanos">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Fecha de Cumpleaños</th>
                <th>Edad Actual</th>
                <th>Teléfono</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script src="reportes.js"></script>
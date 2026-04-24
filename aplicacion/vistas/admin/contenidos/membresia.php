<?php
$archivo = "miembros.json";

/* ───────── FUNCIONES ───────── */
function leerDatos($archivo){
    if (!file_exists($archivo)) {
        file_put_contents($archivo, json_encode([]));
    }
    return json_decode(file_get_contents($archivo), true);
}

function guardarDatos($archivo, $datos){
    file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT));
}

$miembros = leerDatos($archivo);

/* ───────── REGISTRAR ───────── */
if (isset($_POST['registrar'])) {

    $id = !empty($miembros) ? max(array_column($miembros,'id')) + 1 : 1;

    $miembros[] = [
        "id"=>$id,
        "nombres"=>$_POST['nombres'],
        "apellidos"=>$_POST['apellidos'],
        "telefono"=>$_POST['telefono'],
        "direccion"=>$_POST['direccion'],
        "fecha_nacimiento"=>$_POST['fecha_nacimiento'],
        "cargo_id"=>$_POST['cargo_id'],
        "condicion_id"=>$_POST['condicion_id'],
        "latitud"=>$_POST['latitud'],
        "longitud"=>$_POST['longitud']
    ];

    guardarDatos($archivo, $miembros);
    header("Location: ".$_SERVER['PHP_SELF']);
}

/* ───────── EDITAR ───────── */
if (isset($_POST['editar'])) {
    foreach ($miembros as &$m) {
        if ($m['id'] == $_POST['id']) {
            $m['nombres'] = $_POST['nombres'];
            $m['apellidos'] = $_POST['apellidos'];
            $m['telefono'] = $_POST['telefono'];
            $m['direccion'] = $_POST['direccion'];
            $m['fecha_nacimiento'] = $_POST['fecha_nacimiento'];
            $m['cargo_id'] = $_POST['cargo_id'];
            $m['condicion_id'] = $_POST['condicion_id'];
            $m['latitud'] = $_POST['latitud'];
            $m['longitud'] = $_POST['longitud'];
        }
    }

    guardarDatos($archivo, $miembros);
    header("Location: ".$_SERVER['PHP_SELF']);
}

/* ───────── ELIMINAR ───────── */
if (isset($_GET['eliminar'])){
    $miembros = array_values(array_filter($miembros, fn($m)=>$m['id'] != $_GET['eliminar']));
    guardarDatos($archivo,$miembros);
    header("Location: ".$_SERVER['PHP_SELF']);
}

/* ───────── ESTADÍSTICAS ───────── */
$pastores = count(array_filter($miembros, fn($m)=>($m['cargo_id'] ?? 2)==1));
$discipulos = count($miembros) - $pastores;
$total = count($miembros);

/* ───────── MAPA CONDICIONES ───────── */
$condiciones = [
1=>"Saludable",
2=>"Enfermo",
3=>"Hospitalizado",
4=>"Reposo",
5=>"Tratamiento"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Miembros</title>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" href="css/membresia.css">
</head>

<body>

<!-- HEADER -->
<div class="header">
    <h2><i class="fa-solid fa-users"></i> Gestión de Miembros</h2>
    <button class="nuevo" onclick="abrirModal()">
        <i class="fa-solid fa-user-plus"></i> Nuevo Miembro
    </button>
</div>

<!-- CARDS -->
<div class="cards">
    <div class="card">
        <i class="fa-solid fa-users icono"></i>
        <div>
            <h3><?= $total ?></h3>
            <p>Total</p>
        </div>
    </div>

    <div class="card">
        <i class="fa-solid fa-user-tie icono"></i>
        <div>
            <h3><?= $pastores ?></h3>
            <p>Pastores</p>
        </div>
    </div>

    <div class="card">
        <i class="fa-solid fa-user icono"></i>
        <div>
            <h3><?= $discipulos ?></h3>
            <p>Discípulos</p>
        </div>
    </div>
</div>

<!-- BUSCADOR -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
    <h2>Lista de Miembros</h2>
    <input class="buscador" type="text" id="buscar" onkeyup="buscar()" placeholder="Buscar miembro...">
</div>

<!-- TABLA -->
<table>
<thead>
<tr>
<th>Nombre</th>
<th>Teléfono</th>
<th>Dirección</th>
<th>Fecha</th>
<th>Rol</th>
<th>Condición</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>
<?php foreach($miembros as $m){ ?>
<tr>

<td><?= htmlspecialchars(($m['nombres'] ?? '')." ".($m['apellidos'] ?? '')) ?></td>
<td><?= htmlspecialchars($m['telefono'] ?? '') ?></td>
<td><?= htmlspecialchars($m['direccion'] ?? '') ?></td>
<td><?= htmlspecialchars($m['fecha_nacimiento'] ?? '') ?></td>

<td>
<?= (($m['cargo_id'] ?? 2)==1)
    ? '<span class="badge pastor">Pastor</span>'
    : '<span class="badge discipulo">Discípulo</span>' ?>
</td>

<td>
<?= $condiciones[$m['condicion_id'] ?? 1] ?>
</td>

<td>
<button class="btn editar"
onclick="editar(
'<?= $m['id'] ?>',
'<?= addslashes($m['nombres'] ?? '') ?>',
'<?= addslashes($m['apellidos'] ?? '') ?>',
'<?= addslashes($m['telefono'] ?? '') ?>',
'<?= addslashes($m['direccion'] ?? '') ?>',
'<?= $m['fecha_nacimiento'] ?? '' ?>',
'<?= $m['cargo_id'] ?? 2 ?>',
'<?= $m['condicion_id'] ?? 1 ?>',
'<?= $m['latitud'] ?? '' ?>',
'<?= $m['longitud'] ?? '' ?>'
)">
<i class="fa-solid fa-pen"></i>
</button>

<a class="btn eliminar"
href="?eliminar=<?= $m['id'] ?>"
onclick="return confirm('¿Eliminar este miembro?')">
<i class="fa-solid fa-trash"></i>
</a>
</td>

</tr>
<?php } ?>
</tbody>
</table>

<!-- MODAL -->
<div class="modal" id="modal">
<div class="modal-box">

<h3 id="tituloModal">
<i class="fa-solid fa-user-plus"></i> Nuevo Miembro
</h3>

<form method="POST">
<input type="hidden" name="id">

<div class="grid">

<input type="text" name="nombres" placeholder="Nombres" required>
<input type="text" name="apellidos" placeholder="Apellidos" required>

<input type="text" name="telefono" placeholder="Teléfono">



<input type="date" name="fecha_nacimiento">

<select name="cargo_id">
<option value="1">Pastor</option>
<option value="2">Discípulo</option>
</select>

<select name="condicion_id">
<option value="1">Saludable</option>
<option value="2">Enfermo</option>
<option value="3">Hospitalizado</option>
<option value="4">Reposo</option>
<option value="5">Tratamiento</option>
</select>


<div class="campo-mapa">

<input type="text" name="direccion" placeholder="Dirección">

<button type="button" class="btn-mapa" onclick="abrirMapa()">
    <i class="fa-solid fa-map-location-dot"></i>
</button>

</div>

<div class="coordenadas">
    <input type="text" name="latitud" placeholder="Latitud">
    <input type="text" name="longitud" placeholder="Longitud">
</div>

</div>

<br>

<button name="registrar" id="btnAgregar" class="nuevo">
<i class="fa-solid fa-user-plus"></i> Agregar miembro
</button>

<button name="editar" id="btnActualizar" class="nuevo" style="display:none;">
<i class="fa-solid fa-pen"></i> Actualizar
</button>

<button type="button" onclick="cerrarModal()">
Cancelar
</button>

</form>

</div>
</div>

<script src="js/membresia.js"></script>

</body>
</html>
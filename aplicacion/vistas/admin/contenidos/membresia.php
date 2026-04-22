<?php

$archivo = "membresias.json";

// LEER JSON
function leerDatos($archivo){
    if (!file_exists($archivo)) {
        file_put_contents($archivo, json_encode([]));
    }

    $contenido = file_get_contents($archivo);
    return json_decode($contenido, true);
}

// GUARDAR JSON
function guardarDatos($archivo, $datos){
    file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT));
}

$membresias = leerDatos($archivo);

// REGISTRAR
if (isset($_POST['registrar'])) {

    $id = 1;
    if (!empty($membresias)) {
        $ids = array_column($membresias, 'id');
        $id = max($ids) + 1;
    }

    $nuevo = [
        "id" => $id,
        "nombre" => trim($_POST['nombre']),
        "apellido" => trim($_POST['apellido']),
        "documento" => trim($_POST['documento']),
        "telefono" => trim($_POST['telefono']),
        "direccion" => trim($_POST['direccion']),
        "tipo" => $_POST['tipo']
    ];

    $membresias[] = $nuevo;
    guardarDatos($archivo, $membresias);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// EDITAR
if (isset($_POST['editar'])) {
    $id = $_POST['id'];

    foreach ($membresias as &$m) {
        if ($m['id'] == $id) {
            $m['nombre'] = trim($_POST['nombre']);
            $m['apellido'] = trim($_POST['apellido']);
            $m['documento'] = trim($_POST['documento']);
            $m['telefono'] = trim($_POST['telefono']);
            $m['direccion'] = trim($_POST['direccion']);
            $m['tipo'] = $_POST['tipo'];
        }
    }

    guardarDatos($archivo, $membresias);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    foreach ($membresias as $key => $m) {
        if ($m['id'] == $id) {
            unset($membresias[$key]);
        }
    }

    $membresias = array_values($membresias);
    guardarDatos($archivo, $membresias);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Membresía</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


</head>
<body>

<div class="contenedor">

    <h2><i class="fa-solid fa-users"></i> Gestión de Membresía</h2>

    <form method="POST" class="formulario">

        <input type="hidden" name="id" id="id">

        <div class="grid">

            <div class="campo">
                <label><i class="fa-solid fa-user"></i> Nombre</label>
                <input type="text" name="nombre" required>
            </div>

            <div class="campo">
                <label><i class="fa-solid fa-user"></i> Apellido</label>
                <input type="text" name="apellido" required>
            </div>

            <div class="campo">
                <label><i class="fa-solid fa-id-card"></i> Documento</label>
                <input type="text" name="documento" required>
            </div>

            <div class="campo">
                <label><i class="fa-solid fa-phone"></i> Teléfono</label>
                <input type="text" name="telefono">
            </div>

            <div class="campo full">
                <label><i class="fa-solid fa-location-dot"></i> Ubicación</label>

                <div class="ubicacion">
                    <input type="text" name="direccion" id="direccion" placeholder="Ingrese dirección">
                    <button type="button" onclick="abrirMapa()">
                        <i class="fa-solid fa-map"></i>
                    </button>
                </div>
            </div>

            

        </div>

        <div class="botones">
            <button type="submit" name="registrar" class="btn guardar">
                <i class="fa-solid fa-floppy-disk"></i> Registrar
            </button>

            <button type="submit" name="editar" class="btn editar">
                <i class="fa-solid fa-pen"></i> Modificar
            </button>
        </div>

    </form>

    <hr>

    <table class="tabla">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Documento</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Acciones</th>
        </tr>

        <?php foreach($membresias as $row){ ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['apellido']) ?></td>
            <td><?= htmlspecialchars($row['documento']) ?></td>
            <td><?= htmlspecialchars($row['telefono']) ?></td>
            <td><?= htmlspecialchars($row['direccion'] ?? '') ?></td>
            

            <td>
                <button class="btn-small editar"
                    onclick="editar('<?= $row['id'] ?>','<?= $row['nombre'] ?>','<?= $row['apellido'] ?>','<?= $row['documento'] ?>','<?= $row['telefono'] ?>','<?= $row['direccion'] ?>','<?= $row['tipo'] ?>')">
                    <i class="fa-solid fa-pen"></i>
                </button>

                <a class="btn-small eliminar"
                   href="?eliminar=<?= $row['id'] ?>"
                   onclick="return confirm('¿Eliminar?')">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>
</body>
</html>
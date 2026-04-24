<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Transmisión</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<div class="contenedor">

    <!-- HEADER -->
    <div class="header">
        <div class="titulo">
            <i class="fa-solid fa-video"></i> Gestión de Transmisión
        </div>

        <div class="badge activo">
            <i class="fa-solid fa-circle"></i> En vivo
        </div>
    </div>

    <!-- GRID -->
    <div class="grid">

        <!-- IZQUIERDA: VIDEO -->
        <div class="card">

            <iframe class="video"
                src="https://www.youtube.com/embed/5qap5aO4i9A"
                allowfullscreen>
            </iframe>

            <div class="info">
                <h3>Servicio Dominical</h3>
                <p>Transmisión en vivo desde la iglesia</p>
            </div>

        </div>

        <!-- DERECHA: CONFIG -->
        <div class="card">

            <h3><i class="fa-solid fa-gear"></i> Configuración</h3>

            <form>

                <div class="form-group">
                    <label>Título</label>
                    <input type="text" placeholder="Ej: Servicio Dominical">
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea rows="3" placeholder="Descripción de la transmisión"></textarea>
                </div>

                <div class="form-group">
                    <label>URL YouTube (embed)</label>
                    <input type="text" placeholder="https://www.youtube.com/embed/...">
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select>
                        <option>Programada</option>
                        <option>Activo (En vivo)</option>
                        <option>Inactivo</option>
                        <option>Cancelar</option>
                    </select>
                </div>

                <button class="btn">
                    <i class="fa-solid fa-save"></i> Guardar
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>
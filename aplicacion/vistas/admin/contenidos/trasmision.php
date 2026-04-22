<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Transmisión</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* 🎨 PALETA */
:root{
    --primario:#4f6bed;
    --fondo:#f5f7fb;
    --card:#ffffff;
    --borde:#e5e7eb;
    --texto:#1f2937;
    --texto-sec:#6b7280;

    --activo:#22c55e;
    --activo-bg:#dcfce7;

    --inactivo:#ef4444;
    --inactivo-bg:#fee2e2;
}

/* 🌐 BASE */
body{
    margin:0;
    font-family:Arial, sans-serif;
    background:var(--fondo);
}

/* CONTENEDOR */
.contenedor{
    padding:20px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.titulo{
    font-size:22px;
    font-weight:bold;
    color:var(--texto);
}

/* BOTÓN */
.btn{
    background:var(--primario);
    color:white;
    border:none;
    padding:10px 15px;
    border-radius:8px;
    cursor:pointer;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}

/* CARD */
.card{
    background:var(--card);
    border:1px solid var(--borde);
    border-radius:12px;
    padding:20px;
}

/* VIDEO */
.video{
    width:100%;
    height:700px;
    border:none;
    border-radius:10px;
}

/* BADGE */
.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.activo{
    background:var(--activo-bg);
    color:var(--activo);
}

.inactivo{
    background:var(--inactivo-bg);
    color:var(--inactivo);
}

/* FORM */
.form-group{
    margin-bottom:15px;
}

.form-group label{
    font-size:14px;
    color:var(--texto-sec);
}

.form-group input,
.form-group textarea,
.form-group select{
    width:100%;
    padding:10px;
    border:1px solid var(--borde);
    border-radius:8px;
    margin-top:5px;
}

/* TEXTO INFO */
.info{
    margin-top:15px;
}

.info h3{
    margin:0;
}

.info p{
    color:var(--texto-sec);
}

/* RESPONSIVE */
@media(max-width:900px){
    .grid{
        grid-template-columns:1fr;
    }
}

</style>

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
                        <option>Activo (En vivo)</option>
                        <option>Inactivo</option>
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
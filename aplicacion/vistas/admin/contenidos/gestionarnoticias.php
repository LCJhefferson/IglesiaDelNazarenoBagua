<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

</head>
<body>
    
<div class="contenedor">

    

    
    <main class="contenido">

        <h2>Gestión de Noticias</h2>

        <div class="tabla-noticias">
            <h3>Relación de Noticias</h3>

            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Resumen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Evento Juvenil</td>
                        <td>Reunión este sábado</td>
                        <td>
                            <button class="btn editar">Editar</button>
                            <button class="btn eliminar">Eliminar</button>
                        </td>
                    </tr>

                    <tr>
                        <td>Campaña Evangelística</td>
                        <td>Invitación abierta</td>
                        <td>
                            <button class="btn editar">Editar</button>
                            <button class="btn eliminar">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- FORMULARIO EDITAR -->
        <div class="form-editar">
            <h3>Editar Noticia</h3>

            <form>
                <label>Título</label>
                <input type="text" value="Evento Juvenil">

                <label>Resumen</label>
                <textarea rows="3">Reunión este sábado</textarea>

                <label>Imagen</label>
                <input type="file">

                <label>Contenido</label>
                <textarea rows="5">Detalle de la noticia...</textarea>

                <div class="botones">
                    <button class="btn guardar">Actualizar</button>
                    <button class="btn cancelar">Cancelar</button>
                </div>
            </form>
        </div>

    </main>

</div>


</body>
</html>



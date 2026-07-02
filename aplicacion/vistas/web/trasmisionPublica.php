<?php
use aplicacion\modelos\TransmisionModelo;

// Buscamos si hay alguna transmisión activa (Estado 1) usando Eloquent
$live = TransmisionModelo::where('estado_id', 1)->first();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Transmisión en Vivo - Iglesia del Nazareno</title>
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; color: #2d3748; margin: 0; padding: 0; }
        
        /* MODO CINE: Contenedor negro de extremo a extremo de la pantalla */
       .cinema-mode-container {
            width: 100%;
            background: #0f0f0f; 
            padding: 20px 0;
            margin-top: 76px; /* <-- ESTO EMPUJA EL CONTENIDO DEBAJO DE TU NAV FIXED */
            box-shadow: inset 0 -10px 20px rgba(0,0,0,0.5);
        }

        /* Envoltura del reproductor que mantiene la proporción y limita el ancho máximo en pantallas gigantes */
        .cinema-wrapper {
            max-width: 1500px; 
            width: 95%;        
            margin: 0 auto;
            padding: 0;
        }
        
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* Relación de aspecto 16:9 */
            height: 0; overflow: hidden;
            background: #000;
            border-radius: 8px; /* Bordes ligeramente suavizados */
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
        }
        .video-container iframe {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;
        }

        /* Sección inferior para los detalles de la transmisión */
        .details-container {
            max-width: 1500px;
            width: 95%;
            margin: 0 auto;
            padding: 25px 0;
            box-sizing: border-box;
        }

        .status-card { text-align: center; padding: 80px 20px; color: white; }
        .status-card i { opacity: 0.3; margin-bottom: 20px; }
        .status-card h3 { font-size: 1.8rem; margin-bottom: 10px; }

        .finished-bg { background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%); }
        .empty-bg { background: #141414; }

        #live-header { margin-bottom: 15px; }
        #live-header h2 {
            font-size: 0.9rem;
            color: #ef4444; 
            letter-spacing: 2px; 
            display: flex; 
            align-items: center; 
            margin: 0 0 8px 0;
        }
        #live-title { font-size: 1.6rem; margin: 0; color: #ffffff; font-weight: 700; }
        
        .dot-live {
            height: 10px; width: 10px; background-color: #ef4444;
            border-radius: 50%; display: inline-block;
            animation: blink 1s infinite; margin-right: 8px;
            box-shadow: 0 0 8px #ef4444;
        }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
        
        /* Caja de información (Descripción) abajo del video */
        .info-card { 
            margin-top: 20px; 
            padding: 20px; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
        }
        .info-card h4 { margin: 0 0 8px 0; color: #4a5568; font-size: 1.1rem; }
        .info-card p { margin: 0; color: #4a5568; line-height: 1.6; }
    </style>
</head>
<body>
    <?php 
    include __DIR__ . '/componentes/nav.php'; 
    ?>

    <div class="cinema-mode-container">
        <div class="cinema-wrapper">
            
            <div id="live-header" style="<?= $live ? 'display:block;' : 'display:none;' ?>">
                <h2><span class="dot-live"></span> EN VIVO AHORA</h2>
                <h1 id="live-title"><?= $live ? htmlspecialchars($live->titulo) : '' ?></h1>
            </div>

            <div id="player-wrapper">
                <?php if ($live): ?>
                    <div class="video-container">
                        <iframe id="main-iframe" src="<?= htmlspecialchars($live->link_video) ?>?autoplay=1" allow="autoplay; fullscreen" allowfullscreen></iframe>
                    </div>
                <?php else: ?>
                    <div class="video-container empty-bg">
                        <div class="status-card">
                            <i class="fa-solid fa-church fa-4x"></i>
                            <h3>Sin transmisión activa</h3>
                            <p>Te invitamos a estar atento a nuestros próximos servicios.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <div class="details-container">
        <div id="info-wrapper">
            <?php if ($live): ?>
                <div class="info-card">
                    <h4>Descripción del servicio</h4>
                    <p><?= $live->descripcion ? nl2br(htmlspecialchars($live->descripcion)) : 'Sin descripción disponible.' ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/componentes/footer.php'; ?>
    

    <script>
        const pusher = new Pusher('TU_KEY', { cluster: 'TU_CLUSTER' });
        const channel = pusher.subscribe('iglesia-canal');

        channel.bind('evento-vivo', function(data) {
            console.log("Señal de Pusher:", data);
            
            if (data.estado_id == 1 || data.message === 'live_started' || data.message === 'live_updated') {
                actualizarVistaVivo(data);
            } 
            else if (data.estado_id == 2 || data.message === 'live_finished') {
                mostrarVistaFin();
            }
        });

        // Whitelist estricta: solo permitimos embebidos de YouTube.
        // Defensa en profundidad, aunque el backend ya normaliza en formatearUrlYoutube().
        function esUrlYoutubeEmbedValida(url) {
            if (typeof url !== 'string') return false;
            try {
                const u = new URL(url, window.location.origin);
                return (u.protocol === 'https:') &&
                       (u.hostname === 'www.youtube.com' || u.hostname === 'youtube.com') &&
                       u.pathname.startsWith('/embed/');
            } catch (e) {
                return false;
            }
        }

        function limpiarNodo(nodo) {
            while (nodo.firstChild) nodo.removeChild(nodo.firstChild);
        }

        function actualizarVistaVivo(data) {
            document.getElementById('live-header').style.display = 'block';
            document.getElementById('live-title').textContent = data.titulo || '';

            const playerWrapper = document.getElementById('player-wrapper');
            limpiarNodo(playerWrapper);

            const contenedor = document.createElement('div');
            contenedor.className = 'video-container';

            if (esUrlYoutubeEmbedValida(data.link_video)) {
                const iframe = document.createElement('iframe');
                iframe.id = 'main-iframe';
                iframe.setAttribute('src', data.link_video + '?autoplay=1');
                iframe.setAttribute('allow', 'autoplay; fullscreen');
                iframe.setAttribute('allowfullscreen', '');
                contenedor.appendChild(iframe);
            } else {
                const aviso = document.createElement('p');
                aviso.style.color = '#fff';
                aviso.style.padding = '20px';
                aviso.textContent = 'La transmisión no tiene un enlace de video válido.';
                contenedor.appendChild(aviso);
            }
            playerWrapper.appendChild(contenedor);

            const infoWrapper = document.getElementById('info-wrapper');
            limpiarNodo(infoWrapper);

            const card = document.createElement('div');
            card.className = 'info-card';

            const titulo = document.createElement('h4');
            titulo.textContent = 'Descripción del servicio';

            const parrafo = document.createElement('p');
            parrafo.style.whiteSpace = 'pre-line'; // equivalente visual a nl2br sin usar HTML
            parrafo.textContent = data.descripcion || 'Sin descripción disponible.';

            card.appendChild(titulo);
            card.appendChild(parrafo);
            infoWrapper.appendChild(card);
        }

        function mostrarVistaFin() {
            document.getElementById('live-header').style.display = 'none';

            const playerWrapper = document.getElementById('player-wrapper');
            limpiarNodo(playerWrapper);

            playerWrapper.innerHTML = `
                <div class="video-container finished-bg">
                    <div class="status-card">
                        <i class="fa-solid fa-circle-check fa-4x"></i>
                        <h3>Fin de la transmisión</h3>
                        <p>Gracias por acompañarnos. ¡Dios te bendiga!</p>
                        <hr style="width:30%; opacity:0.1; margin:20px auto;">
                        <small>La transmisión ha terminado. Esperamos verte pronto.</small>
                    </div>
                </div>
            `;

            limpiarNodo(document.getElementById('info-wrapper'));
        }
    </script>
</body>
</html>

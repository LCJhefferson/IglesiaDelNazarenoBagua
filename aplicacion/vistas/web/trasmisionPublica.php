<?php
use aplicacion\modelos\TransmisionModelo;

// Buscamos si hay alguna transmisión activa (Estado 1)
$live = TransmisionModelo::where('estado_id', 1)->first();

// Definimos los parámetros de reproducción limpia de YouTube
$youtubeParams = "?autoplay=1&modestbranding=1&rel=0&controls=1";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transmisión - Iglesia del Nazareno</title>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>

    <link rel="stylesheet" href="<?= URL ?>public/web/css/globalPublico.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/transmisionPublica.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>
<body class="pagina-transmision">

    <?php include __DIR__ . '/componentes/nav.php'; ?>

    <section class="cinema-section-full">
        <div class="cinema-container-full">
            
            <header id="live-header" class="live-header-box" style="<?= $live ? 'display:flex;' : 'display:none;' ?>">
                <div class="badge-live">
                    <span class="dot-live"></span>
                    <span class="badge-text">TRANSMITIENDO</span>
                   
                </div>
                 <!-- <h1 id="live-title" class="live-title">  <?= $live ? htmlspecialchars($live->titulo) : '' ?>  </h1> -->
            </header>

            <div id="player-wrapper" class="player-wrapper-full">
                <?php if ($live): ?>
                    <div class="video-container-full">
                        <iframe id="main-iframe" src="<?= htmlspecialchars($live->link_video) . $youtubeParams ?>" allow="autoplay; fullscreen" allowfullscreen></iframe>
                    </div>
                <?php else: ?>
                    <div class="video-container-full empty-bg">
                        <div class="status-card">
                            <div class="status-icon-circle">
                                <i class="fa-solid fa-church"></i>
                            </div>
                            <h3>Sin transmisión activa en este momento</h3>
                            <p>Te invitamos a conectarte durante nuestros horarios habituales de culto y reuniones especiales.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <main class="transmision-details-container">
        <section class="details-section">
            <div id="info-wrapper" class="info-wrapper">
                <?php if ($live): ?>
                    <article class="info-card">
                        <div class="info-card-header">
                            <i class="fa-solid fa-circle-info"></i>
                            <h4>Descripción del servicio</h4>
                        </div>
                        <p><?= $live->descripcion ? nl2br(htmlspecialchars($live->descripcion)) : 'Sin descripción disponible para esta transmisión.' ?></p>
                    </article>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/componentes/footer.php'; ?>

    <script>
        const pusher = new Pusher('TU_KEY', { cluster: 'TU_CLUSTER' });
        const channel = pusher.subscribe('iglesia-canal');

        channel.bind('evento-vivo', function(data) {
            if (data.estado_id == 1 || data.message === 'live_started' || data.message === 'live_updated') {
                actualizarVistaVivo(data);
            } 
            else if (data.estado_id == 2 || data.message === 'live_finished') {
                mostrarVistaFin();
            }
        });

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
            const liveHeader = document.getElementById('live-header');
            liveHeader.style.display = 'flex';
            document.getElementById('live-title').textContent = data.titulo || '';

            const playerWrapper = document.getElementById('player-wrapper');
            limpiarNodo(playerWrapper);

            const contenedor = document.createElement('div');
            contenedor.className = 'video-container-full';

            if (esUrlYoutubeEmbedValida(data.link_video)) {
                const iframe = document.createElement('iframe');
                iframe.id = 'main-iframe';
                
                // Concatena los parámetros para la vista dinámica de Pusher
                const separator = data.link_video.includes('?') ? '&' : '?';
                iframe.setAttribute('src', data.link_video + separator + 'autoplay=1&modestbranding=1&rel=0&controls=1');
                
                iframe.setAttribute('allow', 'autoplay; fullscreen');
                iframe.setAttribute('allowfullscreen', '');
                contenedor.appendChild(iframe);
            } else {
                const aviso = document.createElement('p');
                aviso.style.color = '#334155';
                aviso.style.padding = '30px';
                aviso.style.textAlign = 'center';
                aviso.textContent = 'La transmisión no tiene un enlace de video válido.';
                contenedor.appendChild(aviso);
            }
            playerWrapper.appendChild(contenedor);

            const infoWrapper = document.getElementById('info-wrapper');
            limpiarNodo(infoWrapper);

            const card = document.createElement('article');
            card.className = 'info-card';

            const headerDiv = document.createElement('div');
            headerDiv.className = 'info-card-header';
            
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-circle-info';
            
            const titulo = document.createElement('h4');
            titulo.textContent = 'Descripción del servicio';

            headerDiv.appendChild(icon);
            headerDiv.appendChild(titulo);

            const parrafo = document.createElement('p');
            parrafo.style.whiteSpace = 'pre-line';
            parrafo.textContent = data.descripcion || 'Sin descripción disponible para esta transmisión.';

            card.appendChild(headerDiv);
            card.appendChild(parrafo);
            infoWrapper.appendChild(card);
        }

        function mostrarVistaFin() {
            document.getElementById('live-header').style.display = 'none';

            const playerWrapper = document.getElementById('player-wrapper');
            limpiarNodo(playerWrapper);

            playerWrapper.innerHTML = `
                <div class="video-container-full finished-bg">
                    <div class="status-card">
                        <div class="status-icon-circle finished-icon">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <h3>Fin de la transmisión</h3>
                        <p>Gracias por acompañarnos. ¡Que Dios te bendiga grandemente!</p>
                        <div class="status-divider"></div>
                        <small>La transmisión ha finalizado. Te esperamos en nuestro próximo servicio.</small>
                    </div>
                </div>
            `;

            limpiarNodo(document.getElementById('info-wrapper'));
        }
    </script>

</body>
</html>
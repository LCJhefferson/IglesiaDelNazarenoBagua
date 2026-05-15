/**
 * Control de notificaciones en tiempo real para la página de inicio
 */
document.addEventListener('DOMContentLoaded', () => {
    // Reemplaza con tus credenciales reales de Pusher de tu TransmisionController
    const PUSHER_KEY = 'tu_key_aqui';
    const PUSHER_CLUSTER = 'tu_cluster_aqui';

    try {
        const pusherInicio = new Pusher(PUSHER_KEY, { 
            cluster: PUSHER_CLUSTER,
            forceTLS: true 
        });
        
        const channelInicio = pusherInicio.subscribe('iglesia-canal');

        channelInicio.bind('evento-vivo', function(data) {
            console.log("Señal recibida en Inicio:", data);
            
            const banner = document.getElementById('bannerTransmision');
            const texto = document.getElementById('textoBanner');

            if (!banner || !texto) return;

            // Regla 1: Si el estado cambia a 1 (En Vivo) o llega señal de inicio/actualización
            if (data.estado_id == 1 || data.message === 'live_started' || data.message === 'live_updated') {
                
                // Actualizamos el contenido dinámico del texto
                texto.innerText = "¡ESTAMOS EN VIVO! 🔴 " + data.titulo;
                
                // Mostramos el banner aplicando flexbox
                banner.style.display = 'flex';
                
            } 
            // Regla 2: Si el estado cambia a 2 (Finalizado) o llega señal de fin
            else if (data.estado_id == 2 || data.message === 'live_finished') {
                
                // Ocultamos el banner por completo
                banner.style.display = 'none';
                texto.innerText = "";
            }
        });
        
    } catch (error) {
        error_log("Error al conectar con el servicio de alertas en vivo: ", error);
    }
});
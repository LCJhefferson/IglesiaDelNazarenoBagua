

    /**
     * mostrarOcultar(idCampo, boton)
     * Alterna el tipo del input entre 'password' y 'text'
     * y cambia el ícono del ojo en consecuencia.
     *
     * @param {string} idCampo - El id del input de contraseña
     * @param {HTMLElement} boton - El botón que fue presionado
     */
    function mostrarOcultar(idCampo, boton) {
        const campoContrasena = document.getElementById(idCampo);
        const iconoOjo        = document.getElementById('ojo-' + idCampo);

        if (campoContrasena.type === 'password') {
            // Mostrar la contraseña
            campoContrasena.type = 'text';
            iconoOjo.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            // Ocultar la contraseña
            campoContrasena.type = 'password';
            iconoOjo.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    }

    /**
     * evaluarFuerza(valor)
     * Evalúa la fortaleza de la contraseña y colorea
     * los segmentos de la barra de fuerza.
     *
     * Criterios (1 punto cada uno):
     *  - Al menos 8 caracteres
     *  - Al menos una mayúscula
     *  - Al menos un número
     *  - Al menos un carácter especial
     *
     * @param {string} valor - El valor actual del input de contraseña
     */
    function evaluarFuerza(valor) {
        const segmentos = [
            document.getElementById('seg1'),
            document.getElementById('seg2'),
            document.getElementById('seg3'),
            document.getElementById('seg4'),
        ];

        // Limpiar clases previas
        segmentos.forEach(seg => { seg.className = ''; });

        if (!valor) return; // Si está vacío, no hacer nada

        let puntuacion = 0;
        if (valor.length >= 8)           puntuacion++; // Longitud mínima
        if (/[A-Z]/.test(valor))         puntuacion++; // Tiene mayúscula
        if (/[0-9]/.test(valor))         puntuacion++; // Tiene número
        if (/[^A-Za-z0-9]/.test(valor))  puntuacion++; // Tiene carácter especial

        // Determinar clase de color según la puntuación
        const nivelFuerza = puntuacion <= 1 ? 'debil' : puntuacion <= 2 ? 'media' : 'fuerte';

        // Colorear los segmentos según la puntuación obtenida
        for (let i = 0; i < puntuacion; i++) {
            segmentos[i].className = nivelFuerza;
        }
    }

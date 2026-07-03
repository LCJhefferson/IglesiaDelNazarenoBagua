
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

    // Limpiar contenido previo de forma segura
    while (iconoOjo.firstChild) {
        iconoOjo.removeChild(iconoOjo.firstChild);
    }

    if (campoContrasena.type === 'password') {
        // Mostrar la contraseña
        campoContrasena.type = 'text';

        // CORRECCIÓN XSS: crear SVG de forma segura
        const path1 = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path1.setAttribute("d", "M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24");
        const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
        line.setAttribute("x1", "1");
        line.setAttribute("y1", "1");
        line.setAttribute("x2", "23");
        line.setAttribute("y2", "23");

        iconoOjo.appendChild(path1);
        iconoOjo.appendChild(line);

    } else {
        // Ocultar la contraseña
        campoContrasena.type = 'password';

        // CORRECCIÓN XSS: crear SVG de forma segura
        const path2 = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path2.setAttribute("d", "M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z");
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", "12");
        circle.setAttribute("cy", "12");
        circle.setAttribute("r", "3");

        iconoOjo.appendChild(path2);
        iconoOjo.appendChild(circle);
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

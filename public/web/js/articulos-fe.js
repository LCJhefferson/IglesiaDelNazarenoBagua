document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.accordion-header').forEach(button => {
        button.addEventListener('click', () => {
            const accordionItem = button.parentElement;
            const accordionContent = accordionItem.querySelector('.accordion-content');
            
            // 1. Cierra los otros acordeones
            document.querySelectorAll('.accordion-item').forEach(item => {
                if (item !== accordionItem && item.classList.contains('active')) {
                    item.classList.remove('active');
                    item.querySelector('.accordion-content').style.height = '0px';
                }
            });

            // 2. Alterna el estado del actual
            accordionItem.classList.toggle('active');

            if (accordionItem.classList.contains('active')) {
                // Calcula la altura midiendo la caja interna
                const innerContent = accordionContent.querySelector('.accordion-content-inner');
                accordionContent.style.height = innerContent.offsetHeight + 'px';
            } else {
                // Lo cierra devolviéndolo a 0
                accordionContent.style.height = '0px';
            }
        });
    });

    // 3. Recalcula la altura si el usuario voltea el celular o cambia el tamaño de la ventana
    window.addEventListener('resize', () => {
        document.querySelectorAll('.accordion-item.active').forEach(item => {
            const content = item.querySelector('.accordion-content');
            const inner = item.querySelector('.accordion-content-inner');
            content.style.height = inner.offsetHeight + 'px';
        });
    });
});
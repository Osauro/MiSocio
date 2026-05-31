import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Auto-seleccionar contenido al hacer clic en inputs numéricos y de texto
document.addEventListener('click', function (e) {
    const el = e.target;
    if (
        el.tagName === 'INPUT' &&
        (el.type === 'text' || el.type === 'number' || el.type === 'email' || el.type === 'tel' || el.type === 'search' || el.type === '')
    ) {
        el.select();
    }
}, true);

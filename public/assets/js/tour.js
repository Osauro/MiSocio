/**
 * MiSocio — Tour de bienvenida (Driver.js v1.x)
 * Se inicializa desde el layout del tenant.
 */
(function () {
    'use strict';

    // ── Helpers ──────────────────────────────────────────────────────────────

    function el(selector) {
        return document.querySelector(selector);
    }

    // ── Pasos del tour ───────────────────────────────────────────────────────

    function buildSteps() {
        const steps = [];

        // 1. Bienvenida
        steps.push({
            popover: {
                title: '👋 ¡Bienvenido a MiSocio!',
                description: 'Te mostraremos las principales funcionalidades del sistema. Puedes avanzar con <strong>Siguiente</strong> o cerrar con <strong>✕</strong> en cualquier momento.',
                side: 'over',
                align: 'center',
            },
        });

        // 2. Sidebar
        if (el('.main-sidebar')) {
            steps.push({
                element: '.main-sidebar',
                popover: {
                    title: '📋 Menú de navegación',
                    description: 'Desde aquí accedes a todos los módulos del sistema. El menú se adapta a los permisos de tu cuenta.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 3. Ventas
        if (el('a[href*="ventas"]')) {
            steps.push({
                element: 'a[href*="/ventas"]',
                popover: {
                    title: '🛒 Ventas',
                    description: 'Registra ventas rápidamente: busca productos por nombre o código, selecciona cliente, elige método de pago e imprime el ticket.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 4. Productos
        if (el('a[href*="/productos"]')) {
            steps.push({
                element: 'a[href*="/productos"]',
                popover: {
                    title: '📦 Productos',
                    description: 'Administra tu catálogo: crea, edita y organiza productos con precios, stock, imágenes y categorías.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 5. Compras
        if (el('a[href*="/compras"]')) {
            steps.push({
                element: 'a[href*="/compras"]',
                popover: {
                    title: '🧺 Compras',
                    description: 'Registra compras a proveedores para actualizar automáticamente el stock y el costo de tus productos.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 6. Clientes
        if (el('a[href*="/clientes"]')) {
            steps.push({
                element: 'a[href*="/clientes"]',
                popover: {
                    title: '👥 Clientes',
                    description: 'Gestiona tu cartera de clientes. Puedes asignarles crédito y ver su historial de compras.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 7. Préstamos
        if (el('a[href*="/prestamos"]')) {
            steps.push({
                element: 'a[href*="/prestamos"]',
                popover: {
                    title: '🤝 Préstamos',
                    description: 'Módulo de créditos y préstamos. Registra entregas, cuotas y saldos pendientes de tus clientes.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 8. Kardex
        if (el('a[href*="/kardex"]')) {
            steps.push({
                element: 'a[href*="/kardex"]',
                popover: {
                    title: '📋 Kardex',
                    description: 'Consulta el historial de movimientos de stock de cada producto: entradas, salidas y ajustes.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 9. Inventarios
        if (el('a[href*="/inventarios"]')) {
            steps.push({
                element: 'a[href*="/inventarios"]',
                popover: {
                    title: '📊 Inventarios',
                    description: 'Realiza conteos físicos de inventario y genera reportes de diferencias entre el stock real y el registrado.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 10. Movimientos
        if (el('a[href*="/movimientos"]')) {
            steps.push({
                element: 'a[href*="/movimientos"]',
                popover: {
                    title: '💵 Movimientos',
                    description: 'Registro de ingresos y egresos de caja. Lleva el control del flujo de dinero de tu negocio.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 11. Hospedajes (si visible)
        if (el('a[href*="/habitaciones"]')) {
            steps.push({
                element: 'a[href*="/habitaciones"]',
                popover: {
                    title: '🏨 Hospedajes',
                    description: 'Módulo de gestión hotelera: habitaciones, check-in/out, tarifas y modalidades.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 12. Usuarios
        if (el('a[href*="/usuarios"]')) {
            steps.push({
                element: 'a[href*="/usuarios"]',
                popover: {
                    title: '👤 Usuarios',
                    description: 'Agrega cajeros, operadores y administradores. Define qué puede hacer cada uno en el sistema.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 13. Suscripción
        if (el('a[href*="/suscripcion"]')) {
            steps.push({
                element: 'a[href*="/suscripcion"]',
                popover: {
                    title: '💳 Suscripción',
                    description: 'Consulta tu plan actual, fecha de vencimiento y renueva tu suscripción desde aquí.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 14. Configuración
        if (el('a[href*="/config"]')) {
            steps.push({
                element: 'a[href*="/config"]',
                popover: {
                    title: '⚙️ Configuración',
                    description: 'Personaliza el nombre de tu tienda, impresora, tema visual, WhatsApp y más.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 15. Tutoriales
        if (el('a[href*="/tutoriales"]')) {
            steps.push({
                element: 'a[href*="/tutoriales"]',
                popover: {
                    title: '🎬 Videotutoriales',
                    description: 'Accede a videos paso a paso para aprender a usar cada módulo del sistema en poco tiempo.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 16. Botón reiniciar tour
        if (el('#btn-iniciar-tour')) {
            steps.push({
                element: '#btn-iniciar-tour',
                popover: {
                    title: '🔁 ¿Quieres volver a ver la guía?',
                    description: 'Puedes reiniciar este tour en cualquier momento haciendo clic aquí.',
                    side: 'right',
                    align: 'start',
                },
            });
        }

        // 17. Fin
        steps.push({
            popover: {
                title: '🎉 ¡Listo para empezar!',
                description: 'Ya conoces las principales secciones de MiSocio. Si tienes dudas, visita los <strong>Videotutoriales</strong>. ¡Mucho éxito con tu negocio!',
                side: 'over',
                align: 'center',
            },
        });

        return steps;
    }

    // ── Inicializar Driver.js ─────────────────────────────────────────────────

    function crearDriver() {
        return window.driver.js.driver({
            showProgress: true,
            progressText: '{{current}} de {{total}}',
            nextBtnText: 'Siguiente →',
            prevBtnText: '← Anterior',
            doneBtnText: '¡Finalizar!',
            animate: true,
            overlayOpacity: 0.75,
            smoothScroll: true,
            allowClose: true,
            onDestroyStarted: function () {
                marcarCompletado();
                this.destroy();
            },
            steps: buildSteps(),
        });
    }

    function marcarCompletado() {
        fetch('/onboarding/completado', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }).catch(() => {});
    }

    // ── API pública ───────────────────────────────────────────────────────────

    window.MiSocioTour = {
        iniciar: function () {
            if (typeof window.driver === 'undefined' || typeof window.driver.js === 'undefined') {
                console.warn('Driver.js no está cargado todavía.');
                return;
            }
            crearDriver().drive();
        },
    };

    // ── Auto-inicio si es el primer acceso ───────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        if (window.__misocioOnboardingPendiente && typeof window.driver !== 'undefined') {
            setTimeout(function () {
                window.MiSocioTour.iniciar();
            }, 800);
        }
    });

})();

/**
 * MiSocio — Tour de bienvenida y tours por módulo (Driver.js v1.x)
 *
 * Arquitectura:
 *  - Tour global de onboarding: se dispara en el primer login (window.__misocioOnboardingPendiente)
 *  - Tours por módulo: se disparan automáticamente la primera vez que el usuario visita cada módulo
 *  - Tours multi-fase: se usan MutationObserver para detectar apertura de formularios/modales
 *  - Estado: almacenado en localStorage bajo la clave STORAGE_KEY
 */
(function () {
    'use strict';

    // ─────────────────────────────────────────────────────────────────────────
    // Storage
    // ─────────────────────────────────────────────────────────────────────────

    var STORAGE_KEY = 'misocio_tours_v1';

    function isCompleted(key) {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}')[key] === true;
        } catch (e) {
            return false;
        }
    }

    function markCompleted(key) {
        try {
            var data = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            data[key] = true;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch (e) {}
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    function el(selector) {
        try { return document.querySelector(selector); } catch (e) { return null; }
    }

    function csrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : '';
    }

    function currentSlug() {
        var parts = window.location.pathname.replace(/^\//, '').split('/');
        return parts[0] || 'home';
    }

    function abrirSidebar() {
        var sidebar = document.querySelector('.main-sidebar, #sidebar');
        if (!sidebar) return;
        var visible = sidebar.offsetWidth > 0 && sidebar.offsetParent !== null;
        if (!visible) {
            var toggle = document.querySelector('[data-bs-toggle="sidebar"], .sidebar-toggle, #header-toggle, .open-sidebar');
            if (toggle) toggle.click();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Motor del tour
    // ─────────────────────────────────────────────────────────────────────────

    function iniciarTour(steps, onDone) {
        if (typeof window.driver === 'undefined' || typeof window.driver.js === 'undefined') {
            console.warn('[MiSocioTour] Driver.js no esta cargado.');
            return;
        }
        if (!steps || steps.length === 0) return;

        var driverInstance;
        driverInstance = window.driver.js.driver({
            showProgress: true,
            progressText: '{{current}} de {{total}}',
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Listo!',
            animate: true,
            overlayOpacity: 0.72,
            smoothScroll: true,
            allowClose: true,
            onDestroyStarted: function () {
                if (onDone) onDone();
                if (driverInstance) driverInstance.destroy();
            },
            steps: steps,
        });

        setTimeout(function () {
            driverInstance.drive();
        }, 350);

        return driverInstance;
    }

    function observarModal(triggerSelector, phaseKey, stepsFactory, delay) {
        if (isCompleted(phaseKey)) return;
        delay = delay || 400;

        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var added = mutations[i].addedNodes;
                for (var j = 0; j < added.length; j++) {
                    var node = added[j];
                    if (node.nodeType !== 1) continue;
                    var found = null;
                    try {
                        if (node.matches && node.matches(triggerSelector)) {
                            found = node;
                        } else if (node.querySelector) {
                            found = node.querySelector(triggerSelector);
                        }
                    } catch (e) {}
                    if (found) {
                        observer.disconnect();
                        setTimeout(function () {
                            var steps = stepsFactory();
                            if (steps && steps.length > 0) {
                                iniciarTour(steps, function () { markCompleted(phaseKey); });
                            }
                        }, delay);
                        return;
                    }
                }
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
        setTimeout(function () { observer.disconnect(); }, 1200000);
    }

    function autoIniciar(key, stepsFactory, delayMs) {
        delayMs = delayMs || 1000;
        setTimeout(function () {
            var steps = stepsFactory();
            if (steps && steps.length > 0) {
                iniciarTour(steps, function () { markCompleted(key); });
            }
        }, delayMs);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PASOS DEL TOUR GLOBAL (onboarding)
    // ─────────────────────────────────────────────────────────────────────────

    function buildSteps() {
        var steps = [];
        steps.push({ popover: { title: 'Bienvenido a MiSocio!', description: 'Te mostraremos las principales funcionalidades. Puedes avanzar con <strong>Siguiente</strong> o cerrar con X en cualquier momento.', side: 'over', align: 'center' } });
        if (el('.main-sidebar')) { steps.push({ element: '.main-sidebar', popover: { title: 'Menu de navegacion', description: 'Desde aqui accedes a todos los modulos del sistema.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/ventas"]')) { steps.push({ element: 'a[href*="/ventas"]', popover: { title: 'Ventas', description: 'Registra ventas: busca productos, selecciona cliente, elige metodo de pago e imprime el ticket.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/productos"]')) { steps.push({ element: 'a[href*="/productos"]', popover: { title: 'Productos', description: 'Administra tu catalogo con precios, stock, imagenes y categorias.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/compras"]')) { steps.push({ element: 'a[href*="/compras"]', popover: { title: 'Compras', description: 'Registra compras a proveedores para actualizar automaticamente el stock.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/clientes"]')) { steps.push({ element: 'a[href*="/clientes"]', popover: { title: 'Clientes', description: 'Gestiona tu cartera de clientes y asignales credito.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/prestamos"]')) { steps.push({ element: 'a[href*="/prestamos"]', popover: { title: 'Prestamos', description: 'Creditos y prestamos: cuotas, saldos pendientes e historial.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/kardex"]')) { steps.push({ element: 'a[href*="/kardex"]', popover: { title: 'Kardex', description: 'Historial de movimientos de stock de cada producto.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/inventarios"]')) { steps.push({ element: 'a[href*="/inventarios"]', popover: { title: 'Inventarios', description: 'Conteos fisicos y reportes de diferencias de stock.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/movimientos"]')) { steps.push({ element: 'a[href*="/movimientos"]', popover: { title: 'Movimientos', description: 'Ingresos y egresos de caja que no son ventas.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/habitaciones"]')) { steps.push({ element: 'a[href*="/habitaciones"]', popover: { title: 'Hospedajes', description: 'Gestion hotelera: habitaciones, check-in/out y tarifas.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/usuarios"]')) { steps.push({ element: 'a[href*="/usuarios"]', popover: { title: 'Usuarios', description: 'Cajeros, operadores y administradores con permisos diferenciados.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/suscripcion"]')) { steps.push({ element: 'a[href*="/suscripcion"]', popover: { title: 'Suscripcion', description: 'Plan actual, fecha de vencimiento y renovacion.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/config"]')) { steps.push({ element: 'a[href*="/config"]', popover: { title: 'Configuracion', description: 'Nombre de tu tienda, impresora, tema visual, WhatsApp y mas.', side: 'right', align: 'start' } }); }
        if (el('a[href*="/tutoriales"]')) { steps.push({ element: 'a[href*="/tutoriales"]', popover: { title: 'Videotutoriales', description: 'Videos paso a paso para aprender cada modulo en minutos.', side: 'right', align: 'start' } }); }
        if (el('#btn-iniciar-tour')) { steps.push({ element: '#btn-iniciar-tour', popover: { title: 'Reiniciar guia', description: 'Puedes volver a ver este tour en cualquier momento desde aqui.', side: 'bottom', align: 'start' } }); }
        steps.push({ popover: { title: 'Listo para empezar!', description: 'Ya conoces las principales secciones de MiSocio. Visita los <strong>Videotutoriales</strong> si tienes dudas.', side: 'over', align: 'center' } });
        return steps;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PASOS POR MODULO
    // ─────────────────────────────────────────────────────────────────────────

    var STEPS = {

        venta: function () {
            var steps = [];
            steps.push({ popover: { title: 'Punto de Venta', description: 'Aqui registras las ventas de tu negocio. Te mostramos los elementos principales.', side: 'over', align: 'center' } });
            if (el('#buscadorVenta')) { steps.push({ element: '#buscadorVenta', popover: { title: 'Buscar producto', description: 'Escribe el nombre o codigo del producto para agregarlo al carrito. Compatible con lectoras de codigo de barras.', side: 'bottom', align: 'start' } }); }
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Carrito', description: 'Los productos seleccionados aparecen aqui. Puedes cambiar la cantidad o eliminar items antes de confirmar.', side: 'top', align: 'center' } }); }
            if (el('#buscarCliente')) { steps.push({ element: '#buscarCliente', popover: { title: 'Cliente', description: 'Selecciona el cliente. Si es nuevo, puedes crearlo directamente desde aqui.', side: 'top', align: 'start' } }); }
            if (el('#fechaVenta')) { steps.push({ element: '#fechaVenta', popover: { title: 'Fecha de venta', description: 'Por defecto es hoy. Puedes cambiarla si necesitas registrar una venta de otra fecha.', side: 'top', align: 'start' } }); }
            if (el('#montoPagoEfectivo')) { steps.push({ element: '#montoPagoEfectivo', popover: { title: 'Pago en efectivo', description: 'Ingresa el monto recibido. El sistema calcula el cambio a devolver automaticamente.', side: 'top', align: 'start' } }); }
            if (el('#montoPagoOnline')) { steps.push({ element: '#montoPagoOnline', popover: { title: 'Pago QR / Transferencia', description: 'Registra pagos digitales. Puedes combinar efectivo y pago online en una misma venta.', side: 'top', align: 'start' } }); }
            steps.push({ popover: { title: 'A vender!', description: 'Con el carrito lleno y el pago ingresado, haz clic en <strong>Confirmar venta</strong> para cerrarla e imprimir el ticket.', side: 'over', align: 'center' } });
            return steps;
        },

        ventas: function () {
            var steps = [];
            steps.push({ popover: { title: 'Historial de Ventas', description: 'Consulta todas las ventas registradas. Puedes filtrar, ver detalles e imprimir tickets anteriores.', side: 'over', align: 'center' } });
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Lista de ventas', description: 'Cada fila es una venta. Haz clic en una para ver el detalle: productos, totales y forma de pago.', side: 'top', align: 'center' } }); }
            return steps;
        },

        productos_lista: function () {
            var steps = [];
            steps.push({ popover: { title: 'Catalogo de Productos', description: 'Administras todos tus productos desde aqui: crea, edita y organiza con precios, stock e imagenes.', side: 'over', align: 'center' } });
            if (el('#search-mobile')) { steps.push({ element: '#search-mobile', popover: { title: 'Buscador', description: 'Filtra por nombre, codigo de barras o categoria en tiempo real.', side: 'bottom', align: 'start' } }); }
            if (el('.card')) { steps.push({ element: '.card', popover: { title: 'Tarjeta de producto', description: 'Cada tarjeta muestra imagen, nombre, precio y stock. Usa los iconos para editar o eliminar.', side: 'bottom', align: 'start' } }); }
            steps.push({ popover: { title: 'Proximo paso', description: 'Haz clic en <strong>Nuevo Producto</strong> para agregar tu primer articulo. El formulario se abrira con todos los campos necesarios.', side: 'over', align: 'center' } });
            return steps;
        },

        productos_form: function () {
            var steps = [];
            steps.push({ popover: { title: 'Formulario de Producto', description: 'Completa estos campos para registrar un nuevo producto en tu catalogo.', side: 'over', align: 'center' } });
            if (el('#nombre')) { steps.push({ element: '#nombre', popover: { title: 'Nombre', description: 'Nombre descriptivo del producto. Aparecera en ventas, compras, tickets y reportes.', side: 'bottom', align: 'start' } }); }
            if (el('#codigo')) { steps.push({ element: '#codigo', popover: { title: 'Codigo de barras', description: 'Opcional. Si el producto tiene codigo de barras, ingrésalo aqui para escanearlo en ventas.', side: 'bottom', align: 'start' } }); }
            if (el('#cantidad')) { steps.push({ element: '#cantidad', popover: { title: 'Stock inicial', description: 'Cantidad disponible en inventario. Se actualiza con cada venta y compra.', side: 'bottom', align: 'start' } }); }
            if (el('#precio_de_costo')) { steps.push({ element: '#precio_de_costo', popover: { title: 'Precio de costo', description: 'Lo que te costo el producto. Se usa para calcular la utilidad en reportes.', side: 'bottom', align: 'start' } }); }
            steps.push({ popover: { title: 'Imagen del producto', description: 'Usa el boton <strong>Abrir Galeria</strong> para asignar o subir una foto al producto.', side: 'over', align: 'center' } });
            return steps;
        },

        productos_galeria: function () {
            var steps = [];
            steps.push({ popover: { title: 'Galeria de Imagenes', description: 'Aqui puedes subir y seleccionar imagenes para tus productos.', side: 'over', align: 'center' } });
            if (el('.fa-cloud-arrow-up')) { steps.push({ element: '.fa-cloud-arrow-up', popover: { title: 'Subir imagen', description: 'Haz clic aqui para seleccionar una imagen de tu dispositivo y subirla a la galeria.', side: 'bottom', align: 'center' } }); }
            steps.push({ popover: { title: 'Seleccionar imagen', description: 'Haz clic en cualquier imagen para asignarla al producto. Las imagenes quedan disponibles para reutilizar.', side: 'over', align: 'center' } });
            return steps;
        },

        clientes_lista: function () {
            var steps = [];
            steps.push({ popover: { title: 'Cartera de Clientes', description: 'Administra tus clientes, asignales credito y consulta su historial de compras.', side: 'over', align: 'center' } });
            if (el('#search-mobile')) { steps.push({ element: '#search-mobile', popover: { title: 'Buscar cliente', description: 'Filtra clientes por nombre, celular o NIT.', side: 'bottom', align: 'start' } }); }
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Lista de clientes', description: 'Haz clic en editar para modificar los datos o ver el historial de compras.', side: 'top', align: 'center' } }); }
            steps.push({ popover: { title: 'Proximo paso', description: 'Haz clic en <strong>Nuevo Cliente</strong>. Solo necesitas nombre y celular para empezar.', side: 'over', align: 'center' } });
            return steps;
        },

        clientes_form: function () {
            var steps = [];
            steps.push({ popover: { title: 'Nuevo Cliente', description: 'Completa los datos basicos. Solo el nombre es obligatorio.', side: 'over', align: 'center' } });
            if (el('#nombre')) { steps.push({ element: '#nombre', popover: { title: 'Nombre', description: 'Nombre completo del cliente, como aparecera en facturas y reportes.', side: 'bottom', align: 'start' } }); }
            if (el('#celular')) { steps.push({ element: '#celular', popover: { title: 'Celular', description: 'Numero de contacto. Util para enviar notificaciones y ubicar al cliente en ventas.', side: 'bottom', align: 'start' } }); }
            if (el('#nit')) { steps.push({ element: '#nit', popover: { title: 'NIT', description: 'NIT o cedula para facturacion. Solo necesario si el cliente solicita factura.', side: 'bottom', align: 'start' } }); }
            if (el('#correo')) { steps.push({ element: '#correo', popover: { title: 'Correo electronico', description: 'Opcional. Puedes usarlo para enviarle notificaciones o reportes.', side: 'bottom', align: 'start' } }); }
            steps.push({ popover: { title: 'Guardar', description: 'Haz clic en <strong>Guardar</strong>. El cliente estara disponible de inmediato en el punto de venta.', side: 'over', align: 'center' } });
            return steps;
        },

        compras_lista: function () {
            var steps = [];
            steps.push({ popover: { title: 'Registro de Compras', description: 'Registra las compras a tus proveedores. El stock se actualiza automaticamente.', side: 'over', align: 'center' } });
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Historial de compras', description: 'Lista de todas las compras registradas. Haz clic en una para ver el detalle.', side: 'top', align: 'center' } }); }
            steps.push({ popover: { title: 'Tip', description: 'Registrar compras correctamente mantiene tu inventario actualizado y calcula el costo real de cada producto.', side: 'over', align: 'center' } });
            return steps;
        },

        compra: function () {
            var steps = [];
            steps.push({ popover: { title: 'Nueva Compra', description: 'Registra los productos que compraste a tu proveedor para actualizar el stock automaticamente.', side: 'over', align: 'center' } });
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Detalle de la compra', description: 'Aqui aparecen los productos agregados. Puedes ajustar cantidad y precio de costo de cada uno.', side: 'top', align: 'center' } }); }
            steps.push({ popover: { title: 'Confirmar', description: 'Al confirmar, el stock de cada producto se incrementa automaticamente con las cantidades ingresadas.', side: 'over', align: 'center' } });
            return steps;
        },

        kardex: function () {
            var steps = [];
            steps.push({ popover: { title: 'Kardex de Productos', description: 'Historial completo de movimientos de stock: entradas por compra, salidas por venta y ajustes manuales.', side: 'over', align: 'center' } });
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Movimientos', description: 'Cada fila es un movimiento de inventario: fecha, tipo, cantidad, costo y saldo acumulado.', side: 'top', align: 'center' } }); }
            steps.push({ popover: { title: 'Uso recomendado', description: 'Si notas diferencias de stock, el Kardex te ayuda a identificar en que transaccion ocurrio el cambio.', side: 'over', align: 'center' } });
            return steps;
        },

        movimientos: function () {
            var steps = [];
            steps.push({ popover: { title: 'Movimientos de Caja', description: 'Registra ingresos y egresos de dinero que no son ventas: gastos, adelantos, depositos, etc.', side: 'over', align: 'center' } });
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Historial', description: 'Todos los movimientos registrados. Junto con las ventas, forman el flujo de caja del negocio.', side: 'top', align: 'center' } }); }
            return steps;
        },

        inventarios: function () {
            var steps = [];
            steps.push({ popover: { title: 'Inventarios Fisicos', description: 'Realiza conteos de inventario para verificar que el stock real coincide con el del sistema.', side: 'over', align: 'center' } });
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Lista de inventarios', description: 'Cada inventario representa un conteo realizado. Puedes ver las diferencias encontradas en cada uno.', side: 'top', align: 'center' } }); }
            steps.push({ popover: { title: 'Recomendacion', description: 'Realiza conteos periodicos (semanal o mensual) para detectar perdidas o errores de registro a tiempo.', side: 'over', align: 'center' } });
            return steps;
        },

        prestamos_lista: function () {
            var steps = [];
            steps.push({ popover: { title: 'Prestamos y Creditos', description: 'Gestiona los creditos otorgados a clientes: cuotas, fechas de pago y saldos pendientes.', side: 'over', align: 'center' } });
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Lista de prestamos', description: 'Cada fila muestra cliente, monto prestado, saldo pendiente y estado del credito.', side: 'top', align: 'center' } }); }
            steps.push({ popover: { title: 'Tip', description: 'Haz clic en un prestamo para ver el historial de pagos y registrar nuevas cuotas.', side: 'over', align: 'center' } });
            return steps;
        },

        usuarios: function () {
            var steps = [];
            steps.push({ popover: { title: 'Gestion de Usuarios', description: 'Crea y administra los usuarios que acceden al sistema con diferentes niveles de permiso.', side: 'over', align: 'center' } });
            if (el('.table-responsive')) { steps.push({ element: '.table-responsive', popover: { title: 'Lista de usuarios', description: 'Puedes editar datos, cambiar contrasena o desactivar cualquier usuario del sistema.', side: 'top', align: 'center' } }); }
            steps.push({ popover: { title: 'Roles', description: 'Asigna roles (Administrador, Cajero, etc.) para controlar que puede hacer cada usuario.', side: 'over', align: 'center' } });
            return steps;
        },

        config: function () {
            var steps = [];
            steps.push({ popover: { title: 'Configuracion del Sistema', description: 'Personaliza todos los aspectos de tu negocio en MiSocio.', side: 'over', align: 'center' } });
            var tabs = document.querySelectorAll('button[wire\\:click*="setTab"]');
            if (tabs.length > 0) { steps.push({ element: tabs[0], popover: { title: 'Pestanas de configuracion', description: 'La configuracion esta organizada en: General, Impresion, WhatsApp, Modulos e Importacion.', side: 'bottom', align: 'start' } }); }
            steps.push({ popover: { title: 'General', description: 'Configura el nombre de tu negocio, logotipo, moneda y direccion.', side: 'over', align: 'center' } });
            steps.push({ popover: { title: 'Impresion', description: 'Configura la impresora de tickets: IP del agente de impresion, formato y pie de ticket.', side: 'over', align: 'center' } });
            steps.push({ popover: { title: 'WhatsApp', description: 'Activa notificaciones automaticas al registrar ventas via Green API.', side: 'over', align: 'center' } });
            return steps;
        },

        habitaciones: function () {
            var steps = [];
            steps.push({ popover: { title: 'Modulo de Hospedaje', description: 'Gestiona habitaciones, tipos de habitacion, tarifas y modalidades de pago.', side: 'over', align: 'center' } });
            steps.push({ popover: { title: 'Check-in / Check-out', description: 'Haz clic en una habitacion para registrar el ingreso de un huesped. Al finalizar, registra el check-out y genera la factura.', side: 'over', align: 'center' } });
            return steps;
        },

        tutoriales: function () {
            var steps = [];
            steps.push({ popover: { title: 'Videotutoriales', description: 'Aqui encuentras videos paso a paso para aprender a usar cada modulo de MiSocio.', side: 'over', align: 'center' } });
            if (el('iframe')) { steps.push({ element: 'iframe', popover: { title: 'Reproductor de video', description: 'El video seleccionado se reproduce aqui. Haz clic en un video de la lista para cambiarlo.', side: 'right', align: 'center' } }); }
            if (el('#btn-iniciar-tour')) { steps.push({ element: '#btn-iniciar-tour', popover: { title: 'Guia de inicio', description: 'Haz clic aqui en cualquier momento para reiniciar el tour de bienvenida del sistema.', side: 'bottom', align: 'start' } }); }
            return steps;
        },

    };

    // ─────────────────────────────────────────────────────────────────────────
    // Deteccion de modulo y configuracion de tours automaticos
    // ─────────────────────────────────────────────────────────────────────────

    function setup() {
        var slug = currentSlug();

        switch (slug) {
            case 'venta':
                if (!isCompleted('tour_venta')) { autoIniciar('tour_venta', STEPS.venta); }
                break;
            case 'ventas':
                if (!isCompleted('tour_ventas')) { autoIniciar('tour_ventas', STEPS.ventas); }
                break;
            case 'productos':
                if (!isCompleted('tour_productos_lista')) { autoIniciar('tour_productos_lista', STEPS.productos_lista); }
                if (!isCompleted('tour_productos_form')) {
                    observarModal('#nombre', 'tour_productos_form', STEPS.productos_form);
                }
                if (!isCompleted('tour_productos_galeria')) {
                    observarModal('.fa-cloud-arrow-up', 'tour_productos_galeria', STEPS.productos_galeria);
                }
                break;
            case 'compra':
                if (!isCompleted('tour_compra')) { autoIniciar('tour_compra', STEPS.compra); }
                break;
            case 'compras':
                if (!isCompleted('tour_compras_lista')) { autoIniciar('tour_compras_lista', STEPS.compras_lista); }
                break;
            case 'clientes':
                if (!isCompleted('tour_clientes_lista')) { autoIniciar('tour_clientes_lista', STEPS.clientes_lista); }
                if (!isCompleted('tour_clientes_form')) {
                    observarModal('#nombre', 'tour_clientes_form', STEPS.clientes_form);
                }
                break;
            case 'kardex':
                if (!isCompleted('tour_kardex')) { autoIniciar('tour_kardex', STEPS.kardex); }
                break;
            case 'movimientos':
                if (!isCompleted('tour_movimientos')) { autoIniciar('tour_movimientos', STEPS.movimientos); }
                break;
            case 'inventarios':
            case 'inventario':
                if (!isCompleted('tour_inventarios')) { autoIniciar('tour_inventarios', STEPS.inventarios); }
                break;
            case 'prestamos':
            case 'prestamo':
                if (!isCompleted('tour_prestamos_lista')) { autoIniciar('tour_prestamos_lista', STEPS.prestamos_lista); }
                break;
            case 'usuarios':
                if (!isCompleted('tour_usuarios')) { autoIniciar('tour_usuarios', STEPS.usuarios); }
                break;
            case 'config':
                if (!isCompleted('tour_config')) { autoIniciar('tour_config', STEPS.config, 1200); }
                break;
            case 'habitaciones':
            case 'hospedajes':
                if (!isCompleted('tour_habitaciones')) { autoIniciar('tour_habitaciones', STEPS.habitaciones); }
                break;
            case 'tutoriales':
                if (!isCompleted('tour_tutoriales')) { autoIniciar('tour_tutoriales', STEPS.tutoriales); }
                break;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API publica
    // ─────────────────────────────────────────────────────────────────────────

    window.MiSocioTour = {

        iniciar: function () {
            if (typeof window.driver === 'undefined') return;
            abrirSidebar();
            setTimeout(function () {
                iniciarTour(buildSteps(), function () {
                    markCompleted('onboarding');
                    fetch('/onboarding/completado', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    }).catch(function () {});
                });
            }, 300);
        },

        reiniciar: function (key) {
            try {
                var data = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
                if (key) { delete data[key]; } else { data = {}; }
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                console.info('[MiSocioTour] Tour(s) reiniciado(s). Recarga la pagina para que vuelva a aparecer.');
            } catch (e) {}
        },

        iniciarModulo: function (key) {
            if (!STEPS[key]) {
                console.warn('[MiSocioTour] Modulo "' + key + '" no encontrado. Disponibles: ' + Object.keys(STEPS).join(', '));
                return;
            }
            iniciarTour(STEPS[key](), function () { markCompleted('tour_' + key); });
        },

        modulos: function () {
            return Object.keys(STEPS);
        },
    };

    // ─────────────────────────────────────────────────────────────────────────
    // Inicializacion
    // ─────────────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.driver === 'undefined') return;

        if (window.__misocioOnboardingPendiente) {
            setTimeout(function () {
                abrirSidebar();
                iniciarTour(buildSteps(), function () {
                    markCompleted('onboarding');
                    fetch('/onboarding/completado', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    }).catch(function () {});
                });
            }, 800);
            return;
        }

        setup();
    });

})();

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
            steps.push({ popover: { title: 'Punto de Venta', description: 'Aqui registras las ventas de tu negocio. La pantalla esta dividida en dos partes: el <strong>carrito</strong> a la izquierda y el <strong>buscador de productos</strong> a la derecha.', side: 'over', align: 'center' } });

            // Buscador
            if (el('#buscadorVenta')) {
                steps.push({ element: '#buscadorVenta', popover: { title: 'Buscador de productos', description: 'Escribe el nombre, codigo de barras o tag del producto para agregarlo al carrito. Compatible con lectoras de codigo de barras: escanea y el producto se agrega automaticamente.', side: 'bottom', align: 'start' } });
            }

            // Panel derecho de resultados
            if (el('.search-results')) {
                steps.push({ element: '.search-results', popover: { title: 'Resultados de busqueda', description: 'Los productos encontrados aparecen aqui con su <strong>stock disponible</strong> (azul = hay stock, rojo = sin stock) y su <strong>medida de venta</strong> (caja, paquete, etc.). Haz clic en uno para agregarlo al carrito. Si ya esta en el carrito, muestra la cantidad actual en verde.', side: 'left', align: 'start' } });
            }

            // Tarjeta de item en carrito
            if (el('.card-body .card .card-body')) {
                steps.push({ element: '.card-body .card .card-body', popover: { title: 'Item en el carrito', description: 'Cada producto agregado muestra su imagen, nombre y los campos de cantidad y precio editables.', side: 'bottom', align: 'start' } });
            }

            // Enteros / Unidades
            if (el('input[placeholder="0"]')) {
                steps.push({ element: 'input[placeholder="0"]', popover: { title: 'Enteros y Unidades', description: '<strong>Enteros</strong>: cajas/paquetes completos. <strong>Unidades</strong>: unidades sueltas dentro de la caja. Por ejemplo: 2 cajas + 3 unidades = 2 enteros, 3 unidades. El precio se calcula automaticamente segun el precio por mayor y menor configurado.', side: 'bottom', align: 'start' } });
            }

            // Campo precio
            if (el('input[placeholder="0"][step="0.01"]')) {
                steps.push({ element: 'input[placeholder="0"][step="0.01"]', popover: { title: 'Precio y Subtotal', description: '<strong>Precio</strong>: puedes modificarlo directamente si necesitas hacer un descuento. <strong>Subtotal</strong>: se calcula automaticamente (cantidad × precio). Tambien puedes editar el subtotal y el precio se ajustara.', side: 'bottom', align: 'start' } });
            }

            // Icono eliminar
            if (el('.fa-trash')) {
                steps.push({ element: '.fa-trash', popover: { title: 'Eliminar producto', description: 'Haz clic en el icono de papelera para quitar este producto del carrito. Te pedira confirmacion antes de eliminar.', side: 'left', align: 'start' } });
            }

            // Footer - contador de productos
            if (el('.fixed-footer')) {
                steps.push({ element: '.fixed-footer', popover: { title: 'Barra inferior', description: 'La barra inferior muestra el <strong>total de productos</strong> en el carrito. Desde aqui confirmas o cancelas la venta.', side: 'top', align: 'center' } });
            }

            // Boton cancelar venta
            if (el('button[wire\\:click="cancelarVenta"]')) {
                steps.push({ element: 'button[wire\\:click="cancelarVenta"]', popover: { title: 'Cancelar venta', description: 'Descarta todos los productos del carrito y vuelve al historial de ventas. Te pedira confirmacion antes de cancelar para no perder los items agregados.', side: 'top', align: 'start' } });
            }

            // Boton confirmar
            if (el('button[wire\\:click="iniciarCompletarVenta"]')) {
                steps.push({ element: 'button[wire\\:click="iniciarCompletarVenta"]', popover: { title: 'Confirmar venta', description: 'Muestra el <strong>total a cobrar</strong> en Bolivianos. Al hacer clic se abre el flujo de 3 pasos: 1) Fecha de venta, 2) Seleccion de cliente, 3) Metodo de pago (efectivo, QR/online o credito). Al finalizar imprime el ticket automaticamente.', side: 'top', align: 'end' } });
            }

            steps.push({ popover: { title: 'Flujo de confirmacion', description: 'Al confirmar la venta pasaras por 3 pasos rapidos:<br><strong>1.</strong> Fecha de venta (por defecto hoy)<br><strong>2.</strong> Cliente (opcional, puedes omitir)<br><strong>3.</strong> Pago (efectivo + QR, calcula el cambio y credito automaticamente)', side: 'over', align: 'center' } });
            return steps;
        },

        ventas: function () {
            var steps = [];
            steps.push({ popover: { title: 'Historial de Ventas', description: 'Aqui consultas todas las ventas registradas con sus detalles, totales y forma de pago.', side: 'over', align: 'center' } });

            // Buscador + boton filtro fecha + boton +
            if (el('#searchInput')) {
                steps.push({ element: '#searchInput', popover: { title: 'Buscador', description: 'Busca ventas por numero, nombre de producto, cliente o cajero. El resultado se filtra en tiempo real mientras escribes.', side: 'bottom', align: 'start' } });
            }

            // Boton filtro / limpiar fechas (icono calendario o X rojo)
            var btnFiltro = el('button[wire\\:click="abrirModalFiltro"], button[wire\\:click="limpiarFiltroFechas"]');
            if (btnFiltro) {
                steps.push({ element: btnFiltro, popover: { title: 'Filtro de fechas', description: 'Por defecto se muestran las ventas de <strong>hoy</strong>. Haz clic en el icono de calendario para filtrar por rango de fechas. Si hay un filtro activo, aparece el boton <strong>X rojo</strong> para quitarlo y ver todas las ventas.', side: 'bottom', align: 'start' } });
            }

            // Boton + nueva venta
            var btnNueva = el('button[wire\\:click="crearVenta"]');
            if (btnNueva) {
                steps.push({ element: btnNueva, popover: { title: 'Nueva venta', description: 'Haz clic en <strong>+</strong> para abrir el punto de venta y registrar una nueva transaccion.', side: 'bottom', align: 'start' } });
            }

            // Tarjeta de venta
            if (el('.card .compra-card-body, .card-body .card')) {
                steps.push({ element: '.card-body .card', popover: { title: 'Tarjeta de venta', description: 'Cada tarjeta muestra: numero de venta, imagenes de los productos, totales, cajero, fecha y cliente. Usa el icono <strong>ojo</strong> para ver el detalle completo o el de <strong>impresora</strong> para reimprimir el ticket.', side: 'bottom', align: 'start' } });
            }

            // Paginado
            if (el('.pagination, nav[aria-label*="pagina"], .paginate-bar')) {
                steps.push({ element: '.pagination', popover: { title: 'Paginado', description: 'Las ventas se muestran de a 12 por pagina. Navega entre paginas con los controles de paginacion o cambia la cantidad de resultados por pagina.', side: 'top', align: 'center' } });
            }

            steps.push({ popover: { title: 'Tip', description: 'Combina el <strong>buscador</strong> con el <strong>filtro de fechas</strong> para encontrar rapidamente cualquier venta historica.', side: 'over', align: 'center' } });
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
            steps.push({ popover: { title: 'Formulario de Producto', description: 'Este formulario tiene todos los campos necesarios para registrar un producto. Te explicamos cada uno.', side: 'over', align: 'center' } });

            // Imagen
            if (el('.modal-xl .fa-image, .modal-xl [wire\\:click*="abrir"], .modal-xl button[wire\\:click*="galeria"]')) {
                steps.push({ element: '.modal-xl .fa-image', popover: { title: 'Imagen del producto', description: 'Haz clic en <strong>Seleccionar imagen</strong> para asignar una foto al producto desde la galeria. Mejora la identificacion visual en ventas.', side: 'right', align: 'start' } });
            }

            // Control de stock
            if (el('#control')) {
                steps.push({ element: '#control', popover: { title: 'Control de Stock', description: 'Cuando esta <strong>Activado</strong>, el stock disminuye automaticamente con cada venta. Desactivalo para productos de servicio o sin inventario fisico.', side: 'right', align: 'start' } });
            }

            // Nombre
            if (el('#nombre')) {
                steps.push({ element: '#nombre', popover: { title: 'Nombre *', description: 'Nombre completo del producto tal como aparecera en ventas, tickets y reportes. Se puede buscar por este nombre en el punto de venta.', side: 'bottom', align: 'start' } });
            }

            // Categoria
            if (el('#categoria_id')) {
                steps.push({ element: '#categoria_id', popover: { title: 'Categoria *', description: 'Agrupa el producto en una categoria para facilitar su busqueda y los reportes por categoria. Usa el boton <strong>+</strong> para crear una nueva categoria al instante.', side: 'bottom', align: 'start' } });
            }

            // Codigo
            if (el('#codigo')) {
                steps.push({ element: '#codigo', popover: { title: 'Codigo de barras', description: 'Opcional. Ingresa el codigo de barras del producto (EAN, UPC, etc.) para poder escanearlo en el punto de venta con una lectora. Tambien puedes usarlo como referencia interna.', side: 'bottom', align: 'start' } });
            }

            // Medida
            if (el('#medida')) {
                steps.push({ element: '#medida', popover: { title: 'Medida *', description: 'Unidad de empaque del producto: caja, paquete, six pack, litro, etc. Define en que unidad se vende al por mayor. Usa <strong>+</strong> para agregar nuevas medidas.', side: 'bottom', align: 'start' } });
            }

            // Cantidad
            if (el('#cantidad')) {
                steps.push({ element: '#cantidad', popover: { title: 'Cantidad (unidades) *', description: 'Stock inicial del producto expresado en unidades. Cada vez que registres una venta o compra, este numero se actualiza automaticamente.', side: 'bottom', align: 'start' } });
            }

            // Precio compra
            if (el('#precio_de_compra')) {
                steps.push({ element: '#precio_de_compra', popover: { title: 'Precio Compra (Bs.)', description: 'Lo que te costo el producto al comprarlo a tu proveedor. Se usa para calcular la <strong>utilidad bruta</strong> en los reportes de ventas y compras.', side: 'bottom', align: 'start' } });
            }

            // Precio mayor
            if (el('#precio_por_mayor')) {
                steps.push({ element: '#precio_por_mayor', popover: { title: 'Precio Mayor (Bs.)', description: 'Precio de venta <strong>por mayor</strong> (caja, paquete o la medida definida). Se aplica cuando el cliente compra en esa unidad de empaque en el punto de venta.', side: 'bottom', align: 'start' } });
            }

            // Precio menor
            if (el('#precio_por_menor')) {
                steps.push({ element: '#precio_por_menor', popover: { title: 'Precio Menor (Bs.)', description: 'Precio de venta <strong>al detalle</strong> (por unidad individual). Es el precio mas comun en ventas al publico en general.', side: 'bottom', align: 'start' } });
            }

            // Tags
            if (el('.tags-container')) {
                steps.push({ element: '.tags-container', popover: { title: 'Tags / Nombres Alternativos', description: 'Agrega palabras clave o nombres alternativos del producto (ej: "Paceña", "cerveza", "pilsener"). Sirven para encontrarlo mas facil en el buscador del punto de venta. Presiona <kbd>,</kbd> o <kbd>Enter</kbd> para agregar cada uno.', side: 'top', align: 'start' } });
            }

            steps.push({ popover: { title: 'Listo para guardar', description: 'Con todos los campos completados, haz clic en <strong>Guardar</strong>. El producto quedara disponible de inmediato en el punto de venta para ser vendido.', side: 'over', align: 'center' } });
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
            steps.push({ popover: { title: 'Registro de Compras', description: 'Aqui registras las compras a tus proveedores. Cada compra aumenta el stock de los productos y lleva un historial de costos para calcular el precio de venta ideal.', side: 'over', align: 'center' } });
            if (el('#searchInput')) { steps.push({ element: '#searchInput', popover: { title: 'Buscar compra', description: 'Filtra por numero de compra, proveedor o fecha escribiendo aqui.', side: 'bottom', align: 'start' } }); }
            if (el('button[wire\\:click="abrirModalFiltro"]')) { steps.push({ element: 'button[wire\\:click="abrirModalFiltro"]', popover: { title: 'Filtrar por fechas', description: 'Acota las compras a un rango de fechas especifico para revisar periodos concretos (semana, mes, etc.).', side: 'bottom', align: 'start' } }); }
            if (el('button[title="Importar compra por QR"]')) { steps.push({ element: 'button[title="Importar compra por QR"]', popover: { title: 'Importar por QR', description: 'Escanea el codigo QR de una compra FADI para importar todos los productos automaticamente sin tener que ingresarlos uno a uno.', side: 'bottom', align: 'end' } }); }
            if (el('button[wire\\:click="crearCompra"]')) { steps.push({ element: 'button[wire\\:click="crearCompra"]', popover: { title: 'Nueva compra', description: 'Crea una nueva compra. Se abrira el formulario donde agregas los productos y cantidades recibidas.', side: 'bottom', align: 'end' } }); }
            if (el('.card.shadow-sm')) { steps.push({ element: '.card.shadow-sm', popover: { title: 'Tarjeta de compra', description: 'Cada tarjeta muestra los <strong>productos comprados</strong> (con sus imagenes y cantidades), el <strong>total pagado</strong>, el proveedor y la fecha. Las compras canceladas aparecen semitransparentes.', side: 'bottom', align: 'start' } }); }
            steps.push({ popover: { title: 'Acciones por compra', description: '<strong>Ojo</strong>: ver detalle completo con precios y totales.<br><strong>Flecha</strong>: continuar una compra incompleta.<br><strong>Moneda</strong>: registrar un pago de credito al proveedor.<br><strong>Papelera</strong>: cancelar una compra pendiente.', side: 'over', align: 'center' } });
            steps.push({ popover: { title: 'Consejo', description: 'Registrar las compras con el precio de costo correcto permite que el sistema calcule automaticamente los precios de venta al mayor y al detal de cada producto.', side: 'over', align: 'center' } });
            return steps;
        },

        compra: function () {
            var steps = [];
            steps.push({ popover: { title: 'Nueva Compra', description: 'Aqui registras los productos que recibiste de tu proveedor. La pantalla esta dividida: <strong>carrito</strong> a la izquierda y <strong>buscador</strong> a la derecha. Al completar, el stock de cada producto sube automaticamente.', side: 'over', align: 'center' } });

            // Buscador
            if (el('#buscadorCompra')) { steps.push({ element: '#buscadorCompra', popover: { title: 'Buscador de productos', description: 'Escribe el nombre o codigo del producto para agregarlo. Compatible con lectoras de codigo de barras: escanea y el producto se agrega al instante.', side: 'bottom', align: 'start' } }); }

            // Resultados
            if (el('.search-results')) { steps.push({ element: '.search-results', popover: { title: 'Resultados de busqueda', description: 'Los productos encontrados aparecen con su <strong>stock actual</strong> (azul) y la <strong>medida de venta</strong> (ej. Caja 12u). Si ya esta en el carrito aparece un tilde verde; haz clic para ajustar la cantidad.', side: 'left', align: 'start' } }); }

            // Tarjeta item
            if (el('.card-body .card .card-body')) { steps.push({ element: '.card-body .card .card-body', popover: { title: 'Producto en la compra', description: 'Cada producto muestra su imagen y nombre. Los campos de cantidad y precio de costo son editables directamente desde la tarjeta.', side: 'right', align: 'start' } }); }

            // Enteros / Unidades
            if (el('input[placeholder="0"]')) { steps.push({ element: 'input[placeholder="0"]', popover: { title: 'Enteros y Unidades', description: '<strong>Enteros</strong>: cajas o paquetes completos que recibiste.<br><strong>Unidades</strong>: unidades sueltas adicionales dentro de la ultima caja parcial.<br>Ejemplo: 3 cajas + 4 unidades sueltas. El stock sube por el total de unidades calculado.', side: 'bottom', align: 'start' } }); }

            // Precio / Subtotal
            if (el('input[placeholder="0"][step="0.01"]')) { steps.push({ element: 'input[placeholder="0"][step="0.01"]', popover: { title: 'Precio de costo y Subtotal', description: '<strong>Precio</strong>: costo unitario pagado al proveedor. Se usa para calcular los precios de venta al mayor y al detal.<br><strong>Subtotal</strong>: cantidad total × precio. Editar el subtotal ajusta el precio automaticamente.', side: 'bottom', align: 'start' } }); }

            // Papelera
            if (el('.fa-trash')) { steps.push({ element: '.fa-trash', popover: { title: 'Quitar producto', description: 'Elimina este producto de la compra. El stock no se modifica hasta que completes y confirmes la compra.', side: 'left', align: 'start' } }); }

            // Footer
            if (el('.fixed-footer')) { steps.push({ element: '.fixed-footer', popover: { title: 'Barra de totales', description: '<strong>Productos</strong>: cantidad de items distintos en el carrito.<br><strong>Total</strong>: suma de todos los subtotales. Es lo que debes pagar al proveedor.', side: 'top', align: 'center' } }); }

            // Cancelar
            if (el('button[wire\\:click="cancelarCompra"]')) { steps.push({ element: 'button[wire\\:click="cancelarCompra"]', popover: { title: 'Cancelar compra', description: 'Descarta el carrito y regresa al listado. El stock no se ve afectado. Te pedira confirmacion antes de cancelar.', side: 'bottom', align: 'start' } }); }

            // Completar
            if (el('button[wire\\:click="iniciarCompletarCompra"]')) { steps.push({ element: 'button[wire\\:click="iniciarCompletarCompra"]', popover: { title: 'Completar compra', description: 'Abre el flujo de 4 pasos para cerrar la compra:<br><strong>1.</strong> Fecha de la compra<br><strong>2.</strong> Proveedor (opcional, para llevar credito)<br><strong>3.</strong> Saldo a caja (cuanto dinero salia de caja)<br><strong>4.</strong> Pago: efectivo o credito al proveedor', side: 'bottom', align: 'end' } }); }

            steps.push({ popover: { title: 'Credito al proveedor', description: 'Si no pagas el total en efectivo, la diferencia queda registrada como <strong>credito al proveedor</strong>. Puedes pagarlo despues desde la lista de compras usando el boton de moneda en cada tarjeta.', side: 'over', align: 'center' } });
            return steps;
        },

        prestamo: function () {
            var steps = [];
            steps.push({ popover: { title: 'Nuevo Prestamo', description: 'Registra productos que entregas a credito o en consignacion. La pantalla funciona igual que una venta: busca productos, ajusta cantidades y confirmas el prestamo.', side: 'over', align: 'center' } });
            if (el('#buscadorPrestamo')) { steps.push({ element: '#buscadorPrestamo', popover: { title: 'Buscar envase / producto', description: 'Escribe el nombre o codigo del producto que entregas en prestamo. Los envases retornables son el caso mas comun.', side: 'bottom', align: 'start' } }); }
            if (el('.search-results')) { steps.push({ element: '.search-results', popover: { title: 'Resultados de busqueda', description: 'Los productos encontrados aparecen con su stock disponible. Haz clic para agregar al listado del prestamo.', side: 'left', align: 'start' } }); }
            if (el('.card-body .card .card-body')) { steps.push({ element: '.card-body .card .card-body', popover: { title: 'Item prestado', description: 'Cada producto muestra imagen, nombre y campos de cantidad (enteros/unidades) y precio de referencia para calcular el total del prestamo.', side: 'bottom', align: 'start' } }); }
            if (el('.fa-trash')) { steps.push({ element: '.fa-trash', popover: { title: 'Quitar producto', description: 'Elimina este producto de la lista del prestamo.', side: 'left', align: 'start' } }); }
            if (el('button[wire\\:click="cancelarVenta"]')) { steps.push({ element: 'button[wire\\:click="cancelarVenta"]', popover: { title: 'Cancelar prestamo', description: 'Descarta el prestamo y regresa al listado sin registrar ningun movimiento.', side: 'bottom', align: 'start' } }); }
            if (el('button[wire\\:click="iniciarCompletarVenta"]')) { steps.push({ element: 'button[wire\\:click="iniciarCompletarVenta"]', popover: { title: 'Completar prestamo', description: 'Abre el flujo de confirmacion en 3 pasos:<br><strong>1.</strong> Fecha del prestamo<br><strong>2.</strong> Cliente (obligatorio para registrar a quien se le presta)<br><strong>3.</strong> Deposito inicial (monto que deja como garantia, puede ser cero)', side: 'bottom', align: 'end' } }); }
            steps.push({ popover: { title: 'Devolucion', description: 'Una vez confirmado, el prestamo aparece en la lista con estado <strong>Prestado</strong>. Cuando el cliente devuelva los productos, marcas el prestamo como devuelto desde el listado.', side: 'over', align: 'center' } });
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
            steps.push({ popover: { title: 'Prestamos y Consignaciones', description: 'Aqui gestionas los productos prestados a clientes: envases retornables, consignaciones o cualquier entrega que esperas que te devuelvan. Cada prestamo lleva fecha de vencimiento y estado.', side: 'over', align: 'center' } });
            if (el('#searchInput')) { steps.push({ element: '#searchInput', popover: { title: 'Buscar prestamo', description: 'Filtra por numero de prestamo, cliente o fecha.', side: 'bottom', align: 'start' } }); }
            if (el('button[wire\\:click="abrirModalFiltro"]')) { steps.push({ element: 'button[wire\\:click="abrirModalFiltro"]', popover: { title: 'Filtrar por fechas', description: 'Acota los prestamos a un rango de fechas especifico para controlar vencimientos del periodo.', side: 'bottom', align: 'start' } }); }
            if (el('button[wire\\:click="crearPrestamo"]')) { steps.push({ element: 'button[wire\\:click="crearPrestamo"]', popover: { title: 'Nuevo prestamo', description: 'Crea un nuevo registro de prestamo. Se abrira el formulario para agregar los productos que entregas.', side: 'bottom', align: 'end' } }); }
            if (el('.card.shadow-sm')) { steps.push({ element: '.card.shadow-sm', popover: { title: 'Tarjeta de prestamo', description: 'Cada tarjeta muestra los <strong>productos prestados</strong> con sus cantidades, el <strong>monto total</strong>, la <strong>fecha de vencimiento</strong> y el <strong>estado</strong>:<br>• Azul <em>Prestado</em>: activo y vigente<br>• Amarillo <em>Pendiente</em>: aun sin confirmar<br>• Rojo <em>Vencido</em>: paso la fecha limite<br>• Gris <em>Devuelto</em>: ya fue retornado', side: 'bottom', align: 'start' } }); }
            steps.push({ popover: { title: 'Acciones por prestamo', description: '<strong>Flecha</strong>: continuar un prestamo pendiente de confirmacion.<br><strong>Ojo</strong>: ver detalle completo de los productos prestados.<br><strong>Impresora</strong>: reimprimir el ticket del prestamo.', side: 'over', align: 'center' } });
            steps.push({ popover: { title: 'Control de vencimientos', description: 'Las tarjetas con <strong>borde rojo</strong> estan vencidas: el cliente no devolvio a tiempo. Revisalos periodicamente para hacer seguimiento.', side: 'over', align: 'center' } });
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
                if (!isCompleted('tour_prestamos_lista')) { autoIniciar('tour_prestamos_lista', STEPS.prestamos_lista); }
                break;
            case 'prestamo':
                if (!isCompleted('tour_prestamo')) { autoIniciar('tour_prestamo', STEPS.prestamo); }
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

    // Mapa slug → clave de STEPS para el atajo F4
    var SLUG_TOUR_MAP = {
        'venta':        'venta',
        'ventas':       'ventas',
        'productos':    'productos_lista',
        'compra':       'compra',
        'compras':      'compras_lista',
        'clientes':     'clientes_lista',
        'kardex':       'kardex',
        'movimientos':  'movimientos',
        'inventarios':  'inventarios',
        'inventario':   'inventarios',
        'prestamos':    'prestamos_lista',
        'prestamo':     'prestamo',
        'usuarios':     'usuarios',
        'config':       'config',
        'habitaciones': 'habitaciones',
        'hospedajes':   'habitaciones',
        'tutoriales':   'tutoriales',
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.driver === 'undefined') return;

        // Atajo F4: iniciar/reiniciar el tour del modulo activo
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'F4') return;
            e.preventDefault();
            var slug = currentSlug();
            var key = SLUG_TOUR_MAP[slug];
            if (key && STEPS[key]) {
                iniciarTour(STEPS[key](), function () { markCompleted('tour_' + key); });
            } else {
                // Fuera de un modulo con tour: lanza el onboarding global
                abrirSidebar();
                iniciarTour(buildSteps(), function () {
                    markCompleted('onboarding');
                    fetch('/onboarding/completado', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    }).catch(function () {});
                });
            }
        });

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

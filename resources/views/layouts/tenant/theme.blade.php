<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description"
        content="Admiro admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities." />
    <meta name="keywords"
        content="admin template, Admiro admin template, best javascript admin, dashboard template, bootstrap admin template, responsive admin template, web app" />
    <meta name="author" content="pixelstrap" />
    <title>{{ config('app.name', 'LicoPOS') }}</title>
    <!-- Favicon icon-->
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,200;6..12,300;6..12,400;6..12,500;6..12,600;6..12,700;6..12,800;6..12,900;6..12,1000&amp;display=swap"
        rel="stylesheet" />
    <!-- Flag icon css -->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/flag-icon.css') }}" />
    <!-- iconly-icon-->
    <link rel="stylesheet" href="{{ asset('assets/css/iconly-icon.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bulk-style.css') }}" />
    <!-- iconly-icon-->
    <link rel="stylesheet" href="{{ asset('assets/css/themify.css') }}" />
    <!--fontawesome-->
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-min.css') }}" />
    <!-- Whether Icon css-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/vendors/weather-icons/weather-icons.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/scrollbar.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick-theme.css') }}" />
    <!-- App css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
    @php
        $currentTenant = currentTenant();
        $themeNumber = $currentTenant?->theme_number ?? 5;
        $themeColor = getThemeColor();
    @endphp
    <link id="color" rel="stylesheet" href="{{ asset('assets/css/color-' . $themeNumber . '.css') }}?v={{ time() }}" media="screen" />
    <style>
        :root {
            --theme-default: {{ $themeColor }};
            --primary-color: {{ $themeColor }};
        }

        /* Fix para backdrop y modales de Bootstrap */
        .modal-backdrop {
            z-index: 1055 !important;
        }
        .modal {
            z-index: 1056 !important;
        }

        /* Asegurar que los modales personalizados de Livewire también funcionen */
        .modal.fade.show.d-block {
            z-index: 1056 !important;
        }

        /* Asegurar que los modales abiertos con Bootstrap JS también funcionen */
        .modal.show {
            z-index: 1056 !important;
        }

        /* Asegurar que el backdrop esté siempre debajo del modal */
        body > .modal-backdrop {
            z-index: 1055 !important;
        }
    </style>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}" />
</head>

<body>
    <!-- page-wrapper Start-->
    <!-- tap on top starts-->
    <div class="tap-top"><i class="iconly-Arrow-Up icli"></i></div>
    <!-- tap on tap ends-->
    <!-- loader-->
    <div class="loader-wrapper">
        <div class="loader"><span></span><span></span><span></span><span></span><span></span></div>
    </div>

    <!-- Loading Overlay Global -->
    <x-loading-overlay />

    <div class="page-wrapper compact-wrapper" id="pageWrapper" x-data>

        @include('layouts.tenant.header')


        <!-- Page Body Start-->
        <div class="page-body-wrapper">

            <!-- Page sidebar start-->
            @include('layouts.tenant.sidebar')
            <!-- Page sidebar end-->


            <div class="page-body">
                {{ $slot }}

                <!-- Espaciador para vista móvil (barra inferior fija) -->
                <div class="d-md-none" style="min-height: 70px;"></div>
            </div>

        </div>

        <!-- Sidebar de perfil de usuario -->
        @livewire('perfil-usuario')

        <!-- Selector de tenant (overlay) -->
        @livewire('tenant-selector')
    </div>
    <!-- jquery-->
    <script src="{{ asset('assets/js/vendors/jquery/jquery.min.js') }}"></script>
    <!-- bootstrap js-->
    <script src="{{ asset('assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}" defer=""></script>
    <script src="{{ asset('assets/js/vendors/bootstrap/dist/js/popper.min.js') }}" defer=""></script>
    <!--fontawesome-->
    <script src="{{ asset('assets/js/vendors/font-awesome/fontawesome-min.js') }}"></script>
    <!-- feather-->
    <script src="{{ asset('assets/js/vendors/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/feather-icon/custom-script.js') }}"></script>
    <!-- sidebar -->
    <script src="{{ asset('assets/js/sidebar.js') }}"></script>
    <!-- height_equal-->
    <script src="{{ asset('assets/js/height-equal.js') }}"></script>
    <!-- config-->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <!-- apex-->
    <script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
    <script src="{{ asset('assets/js/chart/apex-chart/stock-prices.js') }}"></script>
    <!-- scrollbar-->
    <script src="{{ asset('assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('assets/js/scrollbar/custom.js') }}"></script>
    <!-- slick-->
    <script src="{{ asset('assets/js/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/slick/slick.js') }}"></script>

    <!-- data_table-->
    {{-- <script src="{{ asset('assets/js/js-datatables/datatables/jquery.dataTables.min.js') }}"></script>
    <!-- page_datatable-->
    <script src="{{ asset('assets/js/js-datatables/datatables/datatable.custom.js') }}"></script>
    <!-- page_datatable1-->
    <script src="{{ asset('assets/js/js-datatables/datatables/datatable.custom1.js') }}"></script>
    <!-- page_datatable-->
    <script src="{{ asset('assets/js/datatable/datatables/datatable.custom.js') }}"></script> --}}
    <!-- theme_customizer-->
    <script src="{{ asset('assets/js/theme-customizer/customizer.js') }}"></script>
    <!-- tilt-->
    <script src="{{ asset('assets/js/animation/tilt/tilt.jquery.js') }}"></script>
    <!-- page_tilt-->
    <script src="{{ asset('assets/js/animation/tilt/tilt-custom.js') }}"></script>
    <!-- dashboard_1-->
    {{-- <script src="{{ asset('assets/js/dashboard/dashboard_1.js') }}"></script> --}}
    <!-- custom script -->
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src="{{ asset('assets/js/toasts-custom.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>



    <script>
        function toast(mensaje, tipo = 'success') {
            // creamos el contenedor si no existe
            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '11';
                document.body.appendChild(toastContainer);
            }

            // creamos el elemento html toast
            const toastHTML = `
                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-${tipo} text-white">
                        <strong class="me-auto">Notificación</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${mensaje}
                    </div>
                </div>
            `;

            // agregamos el toast al contenedor
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);

            // inicializamos y mostramos el toast
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            // eliminamos el toast del DOM después de ocultarlo
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }


    </script>



    <script>
        // Alpine.js Store para el loading overlay global
        document.addEventListener('alpine:init', () => {
            Alpine.store('loading', {
                show: false,
                message: 'Cargando...',

                start(message = 'Cargando...') {
                    this.message = message;
                    this.show = true;
                },

                stop() {
                    this.show = false;
                    this.message = 'Cargando...';
                }
            });
        });

        $(document).ready(function () {
            localStorage.setItem("color", 'color-5');
            localStorage.setItem("primary", '#884A39');
            localStorage.setItem("secondary", '#C38154');
        });

        // Restringir fechas futuras en todos los inputs de tipo date - GLOBAL
        (function() {
            const getToday = () => new Date().toISOString().split('T')[0];

            // Función para aplicar max y validar inputs date
            function restrictFutureDates() {
                const today = getToday();
                document.querySelectorAll('input[type="date"]').forEach(input => {
                    // Siempre forzar el max
                    input.setAttribute('max', today);

                    // Validar el valor actual
                    if (input.value && input.value > today) {
                        input.value = today;
                    }

                    // Agregar listener de cambio si no existe
                    if (!input.dataset.maxDateRestricted) {
                        input.dataset.maxDateRestricted = 'true';

                        input.addEventListener('input', function(e) {
                            const currentToday = getToday();
                            this.setAttribute('max', currentToday);
                            if (this.value > currentToday) {
                                this.value = currentToday;
                            }
                        });

                        input.addEventListener('change', function(e) {
                            const currentToday = getToday();
                            this.setAttribute('max', currentToday);
                            if (this.value > currentToday) {
                                this.value = currentToday;
                            }
                        });
                    }
                });
            }

            // Aplicar inmediatamente
            restrictFutureDates();

            // Aplicar al cargar el DOM
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', restrictFutureDates);
            } else {
                setTimeout(restrictFutureDates, 100);
            }

            // Observar cambios en el DOM
            const observer = new MutationObserver(function(mutations) {
                let shouldRestrict = false;
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === 1) { // Element node
                                if (node.tagName === 'INPUT' && node.type === 'date') {
                                    shouldRestrict = true;
                                } else if (node.querySelectorAll) {
                                    if (node.querySelectorAll('input[type="date"]').length > 0) {
                                        shouldRestrict = true;
                                    }
                                }
                            }
                        });
                    }
                });
                if (shouldRestrict) {
                    restrictFutureDates();
                }
            });

            observer.observe(document.body || document.documentElement, {
                childList: true,
                subtree: true
            });

            // Hooks de Livewire
            if (typeof Livewire !== 'undefined') {
                Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                    succeed(() => {
                        setTimeout(restrictFutureDates, 50);
                    });
                });

                Livewire.hook('morph.updated', ({ el, component }) => {
                    setTimeout(restrictFutureDates, 50);
                });
            }

            // Event listeners para Livewire
            document.addEventListener('livewire:load', () => setTimeout(restrictFutureDates, 100));
            document.addEventListener('livewire:update', () => setTimeout(restrictFutureDates, 50));
            document.addEventListener('livewire:navigated', () => setTimeout(restrictFutureDates, 100));

            // Aplicar periódicamente (cada 1 segundo)
            setInterval(restrictFutureDates, 1000);
        })();
    </script>

    @stack('scripts')
</body>

</html>

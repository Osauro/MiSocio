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
    <title>{{ config('app.name', 'MiSocio') }}</title>
    <!-- Favicon icon-->
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json" />
    <link rel="apple-touch-icon" href="/assets/images/icon-192.png" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="MiSocio" />
    @php $landlordThemeColor = getThemeColor(config('app.landlord_theme', 1)); @endphp
    <meta name="theme-color" content="{{ $landlordThemeColor }}" />
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
    <link id="color" rel="stylesheet" href="{{ asset('assets/css/color-' . config('app.landlord_theme', 1) . '.css') }}" media="screen" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}" />

    <style>
        .active>.page-link,
        .page-link.active {
            background-color: #0766AD;
            border-color: #0766AD !important
        }

        .page-link {
            color: #0766AD !important
        }
    </style>

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

    <div class="page-wrapper compact-sidebar" id="pageWrapper">

        @include('layouts.landlord.header')


        <!-- Page Body Start-->
        <div class="page-body-wrapper">

            <!-- Page sidebar start-->
            @include('layouts.landlord.sidebar')
            <!-- Page sidebar end-->


            <div class="page-body">
                {{ $slot }}
            </div>

        </div>

        <!-- Sidebar de Perfil de Usuario -->
        @livewire('perfil-usuario')

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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    @stack('scripts')

    <script>
        $(document).ready(function () {
            localStorage.setItem("color", 'color-5');
            localStorage.setItem("primary", '#0766AD');
            localStorage.setItem("secondary", '#29ADB2');
        })
    </script>

    <!-- PWA: Service Worker + Banner Instalación -->
    <div id="pwa-banner" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:99999;
        background:#1a472a;color:#fff;padding:12px 20px;
        display:none;align-items:center;justify-content:space-between;gap:12px;
        box-shadow:0 -2px 12px rgba(0,0,0,0.3);font-family:inherit;">
        <div style="display:flex;align-items:center;gap:10px;">
            <img src="/assets/images/favicon.png" style="width:32px;height:32px;border-radius:6px;">
            <div>
                <div style="font-weight:700;font-size:.95rem;">Instalar MiSocio</div>
                <div style="font-size:.78rem;opacity:.85;">Accede más rápido desde tu dispositivo</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <button id="pwa-install-btn" style="background:#fff;color:#1a472a;border:none;border-radius:6px;
                padding:8px 16px;font-weight:700;cursor:pointer;font-size:.85rem;">Instalar</button>
            <button id="pwa-dismiss-btn" style="background:transparent;color:#fff;border:1px solid rgba(255,255,255,.5);
                border-radius:6px;padding:8px 12px;cursor:pointer;font-size:.85rem;">✕</button>
        </div>
    </div>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
        let deferredPrompt = null;
        const banner = document.getElementById('pwa-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');
        window.addEventListener('beforeinstallprompt', e => {
            e.preventDefault();
            deferredPrompt = e;
            if (!localStorage.getItem('pwa-dismissed')) {
                banner.style.display = 'flex';
            }
        });
        if (installBtn) installBtn.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            banner.style.display = 'none';
        });
        if (dismissBtn) dismissBtn.addEventListener('click', () => {
            banner.style.display = 'none';
            localStorage.setItem('pwa-dismissed', '1');
        });
        window.addEventListener('appinstalled', () => {
            banner.style.display = 'none';
        });
    </script>
</body>

</html>

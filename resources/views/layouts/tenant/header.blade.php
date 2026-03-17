<header class="page-header row">
    <div class="logo-wrapper d-flex align-items-center col-auto p-0">
        <a href="/">
            <img class="light-logo img-fluid" src="{{ asset('assets/images/logo.png') }}" alt="logo"
                style="height: 60px!important; margin-left:10px" />
        </a>
        <a class="close-btn toggle-sidebar" href="javascript:void(0)">
            <i class="fa-solid fa-bars fa-lg"></i>
        </a>

        <!-- Botón selector de tenant -->
        @php
            $currentTenant = currentTenant();
            $tenantColor = getThemeColor();
        @endphp
        <button class="btn btn-tenant-selector ms-2" onclick="Livewire.dispatch('openTenantSelector')"
            title="{{ $currentTenant?->name ?? 'Cambiar tienda' }}" style="background: {{ $tenantColor }};">
            <i class="fa-solid fa-store"></i>
        </button>

        <!-- Botón de modo Landlord (solo para Super Admins) -->
        @if (Auth::user()->isSuperAdmin())
            <a href="{{ route('admin.dashboard') }}" class="btn btn-mode-switch ms-2"
                title="Ir a modo Landlord (Gestión del Sistema)">
                <i class="fa-solid fa-crown"></i>
            </a>
        @endif
    </div>
    <div class="page-main-header col d-flex justify-content-end align-items-center">
        <div class="nav-right">
            <ul class="header-right">
                <li class="cart-nav">
                </li>
                <!-- Cart Icons -->
                <li class="cart-nav">
                    @livewire('venta-cart')
                </li>
                @if (Auth::user()->canManageCurrentTenant())
                <li class="cart-nav">
                    @livewire('compra-cart')
                </li>
                @endif
                @if(prestamosHabilitados())
                <li class="cart-nav">
                    @livewire('prestamo-cart')
                </li>
                @endif
                <li class="profile-nav">
                    <div class="user-img" id="toggleProfileSidebar" style="cursor: pointer;">
                        <img id="headerAvatar" src="{{ Auth::user()->photo_url }}" alt="user"
                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" />
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('avatarUpdated', (data) => {
            const avatarImg = document.getElementById('headerAvatar');
            if (avatarImg) {
                avatarImg.src = data[0].url + '?t=' + new Date().getTime();
            }
        });
    });
</script>

<style>
    .btn-mode-switch {
        border: 2px solid #fff;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex !important;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        padding: 0;
    }

    .btn-mode-switch:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .btn-mode-switch i {
        color: #ffd700;
        font-size: 18px;
        transition: all 0.3s ease;
    }

    .btn-mode-switch:hover i {
        color: #fff;
        filter: drop-shadow(0 0 4px rgba(255, 215, 0, 0.6));
    }

    .btn-tenant-selector {
        border: none;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex !important;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-tenant-selector:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .btn-tenant-selector i {
        color: white;
        font-size: 18px;
    }

    /* Ocultar botones en móvil (se mostrarán en el sidebar del perfil) */
    @media (max-width: 767px) {

        .btn-tenant-selector,
        .btn-mode-switch {
            display: none !important;
        }
    }

    /* Cart Icons Styles */
    .cart-nav {
        margin-right: 4px;
        margin-left: 4px;
        display: flex;
        align-items: center;
        position: relative;
        z-index: 10000;
        background-color: var(--theme-default, #7366ff);
        border-radius: 50%;
    }

    .cart-icon-link {
        background-color: var(--theme-default, #7366ff);
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        outline: none;
        box-shadow: 0 0 0 8px var(--theme-default, #7366ff), 0 2px 8px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 10;
    }

    .cart-icon-link:focus {
        outline: none;
        border: none;
    }

    .cart-icon-link i {
        color: white !important;
        pointer-events: none;
    }

    .cart-icon-link:hover {
        background-color: var(--theme-default, #7366ff);
        color: white;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 0 0 12px var(--theme-default, #7366ff), 0 6px 16px rgba(0, 0, 0, 0.3);
    }

    .cart-icon-link .badge {
        font-size: 11px;
        font-weight: 700;
        padding: 5px 8px;
        min-width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-shadow: 0 2px 6px rgba(220, 53, 69, 0.5);
        z-index: 11;
    }

    @media (max-width: 768px) {
        .cart-nav {
            margin-right: 2px;
            margin-left: 2px;
        }

        .cart-icon-link {
            width: 36px;
            height: 36px;
        }

        .cart-icon-link i {
            font-size: 1rem !important;
        }

        .cart-icon-link .badge {
            font-size: 10px;
            padding: 3px 6px;
        }
    }

    /* Asegurar que el sidebar esté por encima del buscador */
    .page-sidebar {
        z-index: 1050 !important;
    }

    /* Asegurar que el header esté por encima de todo y fijo en la parte superior */
    .page-header {
        z-index: 1060 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
    }

    /* Agregar padding-top al contenido para compensar el header fijo */
    .page-body-wrapper {
        padding-top: 70px !important;
    }

    /* Padding-top mayor en escritorio */
    @media (min-width: 768px) {
        .page-body-wrapper {
            padding-top: 90px !important;
        }
    }

    /* Reducir z-index del contenido del page-wrapper cuando el sidebar está activo */
    .page-wrapper {
        z-index: 1 !important;
    }
</style>

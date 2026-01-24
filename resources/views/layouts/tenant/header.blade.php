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
        <button class="btn btn-tenant-selector ms-2"
                onclick="Livewire.dispatch('openTenantSelector')"
                title="{{ $currentTenant?->name ?? 'Cambiar tienda' }}"
                style="background: {{ $tenantColor }};">
            <i class="fa-solid fa-store"></i>
        </button>

        <!-- Botón de modo Landlord (solo para Super Admins) -->
        @if(Auth::user()->isSuperAdmin())
            <a href="{{ route('landlord.home') }}"
               class="btn btn-mode-switch ms-2"
               title="Ir a modo Landlord (Gestión del Sistema)">
                <i class="fa-solid fa-crown"></i>
            </a>
        @endif
    </div>
    <div class="page-main-header col">
        <div class="header-left position-relative d-flex align-items-center justify-content-center w-100">
        </div>
        <div class="nav-right">
            <ul class="header-right">
                <li class="profile-nav">
                    <div class="user-img" id="toggleProfileSidebar" style="cursor: pointer;">
                        <img id="headerAvatar"
                            src="{{ Auth::user()->photo_url }}"
                            alt="user" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" />
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
</style>

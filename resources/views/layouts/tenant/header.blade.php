<header class="page-header row">
    <div class="logo-wrapper d-flex align-items-center col-auto p-0">
        <a href="/">
            <img class="light-logo img-fluid" src="{{ asset('assets/images/logo.png') }}" alt="logo"
                style="height: 60px!important; margin-left:10px" />
        </a>
        <a class="close-btn toggle-sidebar" href="javascript:void(0)">
            <i class="fa-solid fa-bars fa-lg"></i>
        </a>
    </div>
    <div class="page-main-header col">
        <div class="header-left position-relative d-flex align-items-center justify-content-center w-100">
            <!-- Logo para móviles -->
            <a href="/" class="d-md-none position-absolute" style="left: 50%; transform: translateX(-50%);">
                <img src="{{ asset('assets/images/logo.png') }}" alt="logo"
                    style="height: 35px; width: auto; object-fit: contain;" />
            </a>
        </div>
        <div class="nav-right">
            <ul class="header-right">
                <!-- Botón de modo Landlord (solo para Super Admins) -->
                @if(Auth::user()->isSuperAdmin())
                    <li>
                        <a href="{{ route('landlord.home') }}" 
                           class="btn btn-mode-switch"
                           title="Ir a modo Landlord (Gestión del Sistema)">
                            <i class="fa-solid fa-crown"></i>
                        </a>
                    </li>
                @endif

                <!-- Botón selector de tenant -->
                <li>
                    @php
                        $currentTenant = currentTenant();
                        $tenantColor = getThemeColor();
                    @endphp
                    <button class="btn btn-tenant-selector"
                            onclick="Livewire.dispatch('openTenantSelector')"
                            title="{{ $currentTenant?->name ?? 'Cambiar tienda' }}"
                            style="background: {{ $tenantColor }};">
                        <i class="fa-solid fa-store"></i>
                    </button>
                </li>
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
        border: 2px solid #dc3545;
        background: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex !important;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        padding: 0;
    }

    .btn-mode-switch:hover {
        background: #dc3545;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
    }

    .btn-mode-switch i {
        color: #dc3545;
        font-size: 16px;
        transition: color 0.3s ease;
    }

    .btn-mode-switch:hover i {
        color: white;
    }

    .btn-tenant-selector {
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
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

    /* Asegurar que los botones sean visibles en móvil */
    @media (max-width: 767px) {
        .header-right > li {
            display: inline-block !important;
        }

        .btn-tenant-selector,
        .btn-mode-switch {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    }
</style>

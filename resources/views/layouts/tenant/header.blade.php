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
                <!-- Botón selector de tenant -->
                <li>
                    @php
                        $currentTenant = currentTenant();
                        $tenantColor = $currentTenant?->theme_color ?? '#7366ff';
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
    .btn-tenant-selector {
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
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
</style>

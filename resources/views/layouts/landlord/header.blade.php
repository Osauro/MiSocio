<header class="page-header row">
    <div class="logo-wrapper d-flex align-items-center col-auto p-0">
        <a href="/">
            <img class="light-logo img-fluid" src="{{ asset('assets/images/saas_pos_blanco_editado.png') }}" alt="logo" style="height: 60px!important" />
            <img class="dark-logo img-fluid" src="{{ asset('assets/images/saas_pos_blanco_editado.png') }}" alt="logo" style="height: 60px!important" />
        </a>
        <a class="close-btn toggle-sidebar" href="javascript:void(0)">
            <i class="fa-solid fa-bars fa-lg"></i>
        </a>
    </div>
    <div class="page-main-header col">
        <div class="header-left">
            @livewire('tenant-switcher')
        </div>
        <div class="nav-right">
            <ul class="header-right">
                <!-- Botón para volver al modo Tenant -->
                @if(Auth::user()->tenants()->wherePivot('is_active', true)->count() > 0)
                    <li>
                        <a href="{{ route('tenant.home') }}" 
                           class="btn btn-mode-switch-tenant"
                           title="Ir a modo Tenant (Trabajar en una tienda)">
                            <i class="fa-solid fa-store"></i>
                        </a>
                    </li>
                @endif

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

    // Abrir sidebar de perfil al hacer clic en la imagen
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleProfileSidebar');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                Livewire.dispatch('togglePerfilSidebar');
            });
        }
    });
</script>

<style>
    .btn-mode-switch-tenant {
        border: 2px solid #0d6efd;
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

    .btn-mode-switch-tenant:hover {
        background: #0d6efd;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
    }

    .btn-mode-switch-tenant i {
        color: #0d6efd;
        font-size: 18px;
        transition: color 0.3s ease;
    }

    .btn-mode-switch-tenant:hover i {
        color: white;
    }

    /* Asegurar que el botón sea visible en móvil */
    @media (max-width: 767px) {
        .header-right > li {
            display: inline-block !important;
        }

        .btn-mode-switch-tenant {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    }
</style>

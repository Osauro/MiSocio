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

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
        <div class="header-left"></div>
        <div class="nav-right">
            <ul class="header-right">
                <li class="profile-nav custom-dropdown">
                    <div class="user-wrap">
                        <div class="user-img">
                            <img src="{{ asset('assets/images/profile.png') }}" alt="user" />
                        </div>
                        <div class="user-content">
                            <h6>{{ Str::limit(Auth::user()->name ?? 'Usuario', 10, '...') }}</h6>
                            <p class="mb-0">
                                {{ Auth::user()->role ?? 'Role' }}
                                <i class="fa-solid fa-chevron-down"></i>
                            </p>
                        </div>
                    </div>
                    <div class="custom-menu overflow-hidden">
                        <ul class="profile-body">
                            <li class="d-flex">
                                <svg class="svg-color">
                                    <use href="{{ asset('assets/svg/iconly-sprite.svg#Login') }}"></use>
                                </svg>
                                <a class="ms-2" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Salir
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

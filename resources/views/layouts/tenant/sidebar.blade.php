<aside class="page-sidebar">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div class="main-sidebar" id="main-sidebar">
        <ul class="sidebar-menu" id="simple-bar">
            <li class="pin-title sidebar-main-title">
                <div>
                    <h5 class="sidebar-title f-w-700">Menú Principal</h5>
                </div>
            </li>

            <!-- Dashboard - Solo Admins (tenant y super admin) -->
            @if(canManageTenant())
                <li class="sidebar-list">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" href="{{ route('home') }}">
                        <i class="fa-solid fa-house fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Inicio</h6>
                    </a>
                </li>
            @endif

            <!-- Productos - Solo Admins -->
            @if(canManageTenant())
                <li class="sidebar-list">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" href="{{ route('productos') }}">
                        <i class="fa-solid fa-box fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Productos</h6>
                    </a>
                </li>
            @endif

            <!-- Categorías - Solo Admins -->
            @if(canManageTenant())
                <li class="sidebar-list">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" href="{{ route('categorias') }}">
                        <i class="fa-solid fa-layer-group fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Categorías</h6>
                    </a>
                </li>
            @endif

            <!-- Clientes - Solo Admins -->
            @if(canManageTenant())
                <li class="sidebar-list">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" href="{{ route('clientes') }}">
                        <i class="fa-solid fa-users fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Clientes</h6>
                    </a>
                </li>
            @endif

            <!-- Ventas - Todos los usuarios -->
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('ventas') }}">
                    <i class="fa-solid fa-shopping-cart fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Ventas</h6>
                </a>
            </li>

            <!-- Compras - Solo Admins -->
            @if(canManageTenant())
                <li class="sidebar-list">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" href="{{ route('compras') }}">
                        <i class="fa-solid fa-basket-shopping fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Compras</h6>
                    </a>
                </li>
            @endif

            <!-- Kardex - Todos los usuarios -->
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('kardex') }}">
                    <i class="fa-solid fa-clipboard-list fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Kardex</h6>
                </a>
            </li>

            <!-- Movimientos - Solo Admins -->
            @if(canManageTenant())
                <li class="sidebar-list">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" href="{{ route('movimientos') }}">
                        <i class="fa-solid fa-file-invoice-dollar fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Movimientos</h6>
                    </a>
                </li>
            @endif

            <!-- Usuarios - Solo Admins -->
            @if(canManageTenant())
                <li class="sidebar-list">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" href="{{ route('usuarios') }}">
                        <i class="fa-solid fa-user-gear fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Usuarios</h6>
                    </a>
                </li>
            @endif

            <!-- Reportes - Solo Admins -->
            @if(canManageTenant())
                <li class="sidebar-list">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" href="#">
                        <i class="fa-solid fa-chart-pie fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Reportes</h6>
                    </a>
                </li>
            @endif

            <!-- Configuración - Solo Admins -->
            @if(canManageTenant())
                <li class="sidebar-list">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" href="{{ route('config') }}">
                        <i class="fa-solid fa-gear fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Configuración</h6>
                    </a>
                </li>
            @endif
        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
</aside>

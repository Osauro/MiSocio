<aside class="page-sidebar">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div class="main-sidebar" id="main-sidebar">
        <ul class="sidebar-menu" id="simple-bar">

            <!-- Dashboard -->
            @if(canManageTenant())
                <li class="sidebar-list {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-house fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Dashboard</h6>
                    </a>
                </li>
            @endif

            {{-- ===== POS ===== --}}
            @if(canManageTenant())
                <li class="sidebar-list {{ request()->routeIs('productos') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('productos') ? 'active' : '' }}" href="{{ route('productos') }}">
                        <i class="fa-solid fa-box fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Productos</h6>
                    </a>
                </li>
                <li class="sidebar-list {{ request()->routeIs('categorias') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('categorias') ? 'active' : '' }}" href="{{ route('categorias') }}">
                        <i class="fa-solid fa-layer-group fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Categorías</h6>
                    </a>
                </li>
            @endif
            @if(ventasHabilitados())
            <li class="sidebar-list {{ request()->routeIs('ventas') ? 'active' : '' }}">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link {{ request()->routeIs('ventas') ? 'active' : '' }}" href="{{ route('ventas') }}">
                    <i class="fa-solid fa-shopping-cart fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Ventas</h6>
                </a>
            </li>
            @endif
            @if(canManageTenant() && comprasHabilitados())
                <li class="sidebar-list {{ request()->routeIs('compras') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('compras') ? 'active' : '' }}" href="{{ route('compras') }}">
                        <i class="fa-solid fa-basket-shopping fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Compras</h6>
                    </a>
                </li>
            @endif
            @if(prestamosHabilitados())
                <li class="sidebar-list {{ request()->routeIs('prestamos') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('prestamos') ? 'active' : '' }}" href="{{ route('prestamos') }}">
                        <i class="fa-solid fa-handshake fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Préstamos</h6>
                    </a>
                </li>
            @endif
            @if(comprasHabilitados())
            <li class="sidebar-list {{ request()->routeIs('kardex') ? 'active' : '' }}">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link {{ request()->routeIs('kardex') ? 'active' : '' }}" href="{{ route('kardex') }}">
                    <i class="fa-solid fa-clipboard-list fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Kardex</h6>
                </a>
            </li>
            @endif
            @if(canManageTenant())
                <li class="sidebar-list {{ request()->routeIs('inventarios') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('inventarios') ? 'active' : '' }}" href="{{ route('inventarios') }}">
                        <i class="fa-solid fa-boxes-stacked fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Inventarios</h6>
                    </a>
                </li>
            @endif

            {{-- ===== Hospedaje (solo si está habilitado) ===== --}}
            @if(canManageTenant() && hospedajesHabilitados())
                <li class="sidebar-list {{ request()->routeIs('habitaciones') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('habitaciones') ? 'active' : '' }}" href="{{ route('habitaciones') }}">
                        <i class="fa-solid fa-door-open fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Habitaciones</h6>
                    </a>
                </li>
                <li class="sidebar-list {{ request()->routeIs('hospedajes') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('hospedajes') ? 'active' : '' }}" href="{{ route('hospedajes') }}">
                        <i class="fa-solid fa-book fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Historial Hosp.</h6>
                    </a>
                </li>
                <li class="sidebar-list {{ request()->routeIs('tipos-habitacion') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('tipos-habitacion') ? 'active' : '' }}" href="{{ route('tipos-habitacion') }}">
                        <i class="fa-solid fa-tags fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Tarifas</h6>
                    </a>
                </li>
                <li class="sidebar-list {{ request()->routeIs('modalidades') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('modalidades') ? 'active' : '' }}" href="{{ route('modalidades') }}">
                        <i class="fa-solid fa-clock fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Modalidades</h6>
                    </a>
                </li>
            @endif

            {{-- ===== Administración ===== --}}
            @if(canManageTenant())
                <li class="sidebar-list {{ request()->routeIs('usuarios') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('usuarios') ? 'active' : '' }}" href="{{ route('usuarios') }}">
                        <i class="fa-solid fa-user-gear fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Usuarios</h6>
                    </a>
                </li>
                <li class="sidebar-list {{ request()->routeIs('clientes') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('clientes') ? 'active' : '' }}" href="{{ route('clientes') }}">
                        <i class="fa-solid fa-users fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Clientes</h6>
                    </a>
                </li>
                <li class="sidebar-list {{ request()->routeIs('movimientos') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('movimientos') ? 'active' : '' }}" href="{{ route('movimientos') }}">
                        <i class="fa-solid fa-file-invoice-dollar fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Movimientos</h6>
                    </a>
                </li>
                <li class="sidebar-list {{ request()->routeIs('suscripcion') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('suscripcion') ? 'active' : '' }}" href="{{ route('suscripcion') }}">
                        <i class="fa-solid fa-credit-card fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Suscripción</h6>
                    </a>
                </li>
                <li class="sidebar-list {{ request()->routeIs('config') ? 'active' : '' }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ request()->routeIs('config') ? 'active' : '' }}" href="{{ route('config') }}">
                        <i class="fa-solid fa-gear fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600">Configuración</h6>
                    </a>
                </li>
            @endif

        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
</aside>

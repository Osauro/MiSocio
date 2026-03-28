<aside class="page-sidebar"
    x-data="{
        section: (() => {
            const hasHospedaje = {{ hospedajesHabilitados() ? 'true' : 'false' }};
            const detected = '{{ match(true) {
                request()->routeIs('productos','categorias','ventas','compras','prestamos','kardex','inventarios') => 'pos',
                request()->routeIs('habitaciones','hospedajes','tipos-habitacion','modalidades') => 'hospedaje',
                request()->routeIs('usuarios','clientes','movimientos','suscripcion','config') => 'admin',
                default => ''
            } }}';
            if (detected) { localStorage.setItem('sidebarSection', detected); return detected; }
            const stored = localStorage.getItem('sidebarSection') || 'pos';
            if (stored === 'hospedaje' && !hasHospedaje) return 'pos';
            return stored;
        })(),
        open(s) {
            this.section = s;
            localStorage.setItem('sidebarSection', s);
        }
    }">
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

            {{-- ===== ACTIVO: items POS suben al tope ===== --}}
            <template x-if="section === 'pos'">
                <div>
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
                    <li class="sidebar-list {{ request()->routeIs('ventas') ? 'active' : '' }}">
                        <i class="fa-solid fa-thumbtack"></i>
                        <a class="sidebar-link {{ request()->routeIs('ventas') ? 'active' : '' }}" href="{{ route('ventas') }}">
                            <i class="fa-solid fa-shopping-cart fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                            <h6 class="f-w-600">Ventas</h6>
                        </a>
                    </li>
                    @if(canManageTenant())
                        <li class="sidebar-list {{ request()->routeIs('compras') ? 'active' : '' }}">
                            <i class="fa-solid fa-thumbtack"></i>
                            <a class="sidebar-link {{ request()->routeIs('compras') ? 'active' : '' }}" href="{{ route('compras') }}">
                                <i class="fa-solid fa-basket-shopping fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                                <h6 class="f-w-600">Compras</h6>
                            </a>
                        </li>
                    @endif
                    @if(canManageTenant() && prestamosHabilitados())
                        <li class="sidebar-list {{ request()->routeIs('prestamos') ? 'active' : '' }}">
                            <i class="fa-solid fa-thumbtack"></i>
                            <a class="sidebar-link {{ request()->routeIs('prestamos') ? 'active' : '' }}" href="{{ route('prestamos') }}">
                                <i class="fa-solid fa-handshake fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                                <h6 class="f-w-600">Préstamos</h6>
                            </a>
                        </li>
                    @endif
                    <li class="sidebar-list {{ request()->routeIs('kardex') ? 'active' : '' }}">
                        <i class="fa-solid fa-thumbtack"></i>
                        <a class="sidebar-link {{ request()->routeIs('kardex') ? 'active' : '' }}" href="{{ route('kardex') }}">
                            <i class="fa-solid fa-clipboard-list fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                            <h6 class="f-w-600">Kardex</h6>
                        </a>
                    </li>
                    @if(canManageTenant())
                        <li class="sidebar-list {{ request()->routeIs('inventarios') ? 'active' : '' }}">
                            <i class="fa-solid fa-thumbtack"></i>
                            <a class="sidebar-link {{ request()->routeIs('inventarios') ? 'active' : '' }}" href="{{ route('inventarios') }}">
                                <i class="fa-solid fa-boxes-stacked fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                                <h6 class="f-w-600">Inventarios</h6>
                            </a>
                        </li>
                    @endif
                </div>
            </template>

            {{-- ===== ACTIVO: items Hospedaje suben al tope ===== --}}
            @if(canManageTenant() && hospedajesHabilitados())
            <template x-if="section === 'hospedaje'">
                    <div>
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
                                <h6 class="f-w-600">Historial</h6>
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
                    </div>
            </template>
            @endif

            {{-- ===== ACTIVO: items Admin suben al tope ===== --}}
            @if(canManageTenant())
            <template x-if="section === 'admin'">
                    <div>
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
                    </div>
            </template>
            @endif

            {{-- ===== CABECERAS INACTIVAS (aparecen debajo de los items activos) ===== --}}

            {{-- POS: visible solo cuando NO es la sección activa --}}
            <template x-if="section !== 'pos'">
                <li class="sidebar-list" @click="open('pos')" style="cursor: pointer; user-select: none;">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" @click.prevent style="pointer-events: none;">
                        <i class="fa-solid fa-cash-register fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600" style="flex: 1;">POS</h6>
                        <i class="fa-solid fa-chevron-right" style="font-size: 11px; margin-left: auto;"></i>
                    </a>
                </li>
            </template>

            {{-- Hospedaje: visible solo cuando NO es la sección activa --}}
            @if(canManageTenant() && hospedajesHabilitados())
            <template x-if="section !== 'hospedaje'">
                <li class="sidebar-list" @click="open('hospedaje')" style="cursor: pointer; user-select: none;">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" @click.prevent style="pointer-events: none;">
                        <i class="fa-solid fa-hotel fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600" style="flex: 1;">Hospedaje</h6>
                        <i class="fa-solid fa-chevron-right" style="font-size: 11px; margin-left: auto;"></i>
                    </a>
                </li>
            </template>
            @endif

            {{-- Administración: visible solo cuando NO es la sección activa --}}
            @if(canManageTenant())
            <template x-if="section !== 'admin'">
                <li class="sidebar-list" @click="open('admin')" style="cursor: pointer; user-select: none;">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link" @click.prevent style="pointer-events: none;">
                        <i class="fa-solid fa-shield-halved fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                        <h6 class="f-w-600" style="flex: 1;">Administración</h6>
                        <i class="fa-solid fa-chevron-right" style="font-size: 11px; margin-left: auto;"></i>
                    </a>
                </li>
            </template>
            @endif

        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
</aside>

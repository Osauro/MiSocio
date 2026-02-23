<aside class="page-sidebar">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div class="main-sidebar" id="main-sidebar">
        <ul class="sidebar-menu" id="simple-bar">
            <li class="pin-title sidebar-main-title">
                <div>
                    <h5 class="sidebar-title f-w-700">Panel de Administración</h5>
                </div>
            </li>

            <!-- Dashboard -->
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('admin.home') }}">
                    <i class="fa-solid fa-home fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Dashboard</h6>
                </a>
            </li>

            <!-- Tenants -->
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('admin.tenants') }}">
                    <i class="fa-solid fa-store fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Tenants</h6>
                </a>
            </li>

            <!-- Planes de Suscripción -->
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('admin.planes') }}">
                    <i class="fa-solid fa-layer-group fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Planes</h6>
                </a>
            </li>

            <!-- Pagos -->
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('admin.pagos') }}">
                    <i class="fa-solid fa-money-check-dollar fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Pagos</h6>
                </a>
            </li>

        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
</aside>

<aside class="page-sidebar">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div class="main-sidebar" id="main-sidebar">
        <ul class="sidebar-menu" id="simple-bar">
            <li class="pin-title sidebar-main-title">
                <div>
                    <h5 class="sidebar-title f-w-700">Menú Principal</h5>
                </div>
            </li>

            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('tenant.home') }}">
                    <i class="fa-solid fa-house" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Inicio</h6>
                </a>
            </li>

            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('tenant.productos') }}">
                    <i class="fa-solid fa-box" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Productos</h6>
                </a>
            </li>

            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('tenant.categorias') }}">
                    <i class="fa-solid fa-layer-group" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Categorías</h6>
                </a>
            </li>

            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('tenant.clientes') }}">
                    <i class="fa-solid fa-users" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Clientes</h6>
                </a>
            </li>

            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="#">
                    <i class="fa-solid fa-shopping-cart" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Ventas</h6>
                </a>
            </li>

            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="#">
                    <i class="fa-solid fa-cart-shopping" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Compras</h6>
                </a>
            </li>

            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="{{ route('tenant.usuarios') }}">
                    <i class="fa-solid fa-user-gear" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Usuarios</h6>
                </a>
            </li>

            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="#">
                    <i class="fa-solid fa-chart-pie" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Reportes</h6>
                </a>
            </li>

            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link" href="#">
                    <i class="fa-solid fa-gear" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Configuración</h6>
                </a>
            </li>
        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
</aside>

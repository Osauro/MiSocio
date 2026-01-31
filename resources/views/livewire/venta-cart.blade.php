<div style="display: contents;">
    <a href="{{ route('ventas') }}" class="cart-icon-link position-relative" title="Ventas">
        <i class="fa-solid fa-shopping-cart fa-lg"></i>
        @if($cantidadPendientes > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $cantidadPendientes }}
                <span class="visually-hidden">ventas pendientes</span>
            </span>
        @endif
    </a>
</div>

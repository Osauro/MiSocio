<div style="display: contents;">
    <a href="{{ route('prestamos') }}" class="cart-icon-link position-relative" title="Préstamos">
        <i class="fa-solid fa-box-open fa-lg"></i>
        @if($cantidadPendientes > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                {{ $cantidadPendientes }}
                <span class="visually-hidden">préstamos pendientes</span>
            </span>
        @endif
    </a>
</div>

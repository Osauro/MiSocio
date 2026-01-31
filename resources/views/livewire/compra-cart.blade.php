<div x-data x-on:actualizar-badge-compra.window="$wire.actualizarContador()">
@if($compraPendienteId)
    <a href="{{ route('compra', ['compraId' => $compraPendienteId]) }}" class="cart-icon-link position-relative" title="Compra en proceso">
        <i class="fa-solid fa-basket-shopping fa-lg"></i>
        @if($cantidadPendientes > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $cantidadPendientes }}
                <span class="visually-hidden">items en compra</span>
            </span>
        @endif
    </a>
@else
    <a href="{{ route('compras') }}" class="cart-icon-link position-relative" title="Compras">
        <i class="fa-solid fa-basket-shopping fa-lg"></i>
    </a>
@endif
</div>

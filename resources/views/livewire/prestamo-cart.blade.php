<div x-data x-on:actualizar-badge-prestamo.window="$wire.actualizarContador()">
    <a href="{{ $prestamoPendienteId ? route('prestamo', ['prestamoId' => $prestamoPendienteId]) : route('prestamos') }}"
       class="cart-icon-link position-relative"
       title="{{ $prestamoPendienteId ? 'Préstamo en proceso' : 'Préstamos' }}">
        <i class="fa-solid fa-handshake fa-lg"></i>
        @if($cantidadItems > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                {{ $cantidadItems }}
                <span class="visually-hidden">items en préstamo</span>
            </span>
        @endif
    </a>
</div>

<a href="#" class="cart-icon-link position-relative" title="Préstamos"
    onclick="alert('Módulo de Préstamos próximamente'); return false;">
    <i class="fa-solid fa-handshake fa-lg"></i>
    @if ($cantidadPendientes > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{ $cantidadPendientes }}
            <span class="visually-hidden">préstamos pendientes</span>
        </span>
    @endif
</a>

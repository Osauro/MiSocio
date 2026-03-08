<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Préstamos</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    @if ($fecha_inicio && $fecha_fin)
                                        <button type="button" class="btn btn-outline-danger"
                                            wire:click="limpiarFiltroFechas" title="Limpiar filtro de fechas">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-outline-secondary" wire:click="abrirModalFiltro"
                                            title="Filtrar por fechas">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </button>
                                    @endif
                                    <input type="text" class="form-control text-start"
                                        placeholder="Buscar préstamo..." wire:model.live="search"
                                        style="min-width: 200px;" id="searchInput" autofocus>
                                    <button class="btn btn-primary" wire:click="crearPrestamo"><i
                                            class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para móvil -->
                    <div class="card-header card-no-border d-md-none"
                        style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="input-group">
                            @if ($fecha_inicio && $fecha_fin)
                                <button type="button" class="btn btn-outline-danger" wire:click="limpiarFiltroFechas"
                                    title="Limpiar filtro de fechas">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            @else
                                <button class="btn btn-outline-secondary" wire:click="abrirModalFiltro"
                                    title="Filtrar por fechas">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </button>
                            @endif
                            <input type="text" class="form-control text-start" placeholder="Buscar préstamo..."
                                wire:model.live="search" id="searchInputMobile" autofocus>
                            <button class="btn btn-primary" wire:click="crearPrestamo"><i
                                    class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-2">
                        <div class="row g-3">
                            @forelse($prestamos as $prestamo)
                                @php
                                    $estadoReal = $prestamo->estado_real;
                                @endphp
                                <div class="col-md-4 col-12" wire:key="prestamo-{{ $prestamo->id }}">
                                    <div
                                        class="card mb-0 shadow-sm {{ $estadoReal === 'Devuelto' ? 'opacity-75' : ($estadoReal === 'Vencido' ? 'border-danger' : '') }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <!-- Header: [titulo][botones] -->
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h4 class="mb-0 fw-bold">
                                                            Préstamo #{{ $prestamo->numero_folio }}
                                                        </h4>
                                                        <div class="d-flex gap-1">
                                                            @if ($estadoReal === 'Pendiente')
                                                                <a href="{{ route('prestamo', ['prestamoId' => $prestamo->id]) }}"
                                                                    class="btn btn-sm btn-warning"
                                                                    title="Continuar préstamo">
                                                                    <i class="fa-solid fa-arrow-right"></i>
                                                                </a>
                                                            @else
                                                                <button class="btn btn-sm btn-info"
                                                                    wire:click="verDetalles({{ $prestamo->id }})"
                                                                    title="Ver detalles">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Items: Avatar Group de productos -->
                                                    @if ($prestamo->prestamoItems->count() > 0)
                                                        <div class="avatar-group mb-3">
                                                            @foreach ($prestamo->prestamoItems as $item)
                                                                <div class="avatar" style="cursor: pointer;"
                                                                    x-on:click="$dispatch('mostrarKardex', { productoId: {{ $item->producto_id }} })"
                                                                    title="{{ $item->producto->nombre ?? 'Producto' }} - Clic para ver Kardex">
                                                                    <img src="{{ $item->producto->photo_url ?? '' }}"
                                                                        alt="{{ $item->producto->nombre ?? 'Producto' }}">
                                                                    <span
                                                                        class="quantity-badge">{{ $item->cantidad }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    <!-- Monto y Vencimiento -->
                                                    <div class="d-flex gap-2 flex-wrap mb-2">
                                                        <span class="badge bg-primary">
                                                            <i class="fa-solid fa-coins me-1"></i>
                                                            Bs. {{ number_format($prestamo->deposito, 2) }}
                                                        </span>
                                                        @if ($prestamo->fecha_vencimiento)
                                                            <span
                                                                class="badge {{ $estadoReal === 'Vencido' ? 'bg-danger' : ($estadoReal === 'Devuelto' ? 'bg-success' : 'bg-warning text-dark') }}">
                                                                <i class="fa-solid fa-calendar-check me-1"></i>
                                                                {{ $prestamo->fecha_vencimiento->format('d/m/Y') }}
                                                            </span>
                                                        @endif
                                                        <span
                                                            class="badge {{ $estadoReal === 'Devuelto' ? 'bg-success' : ($estadoReal === 'Vencido' ? 'bg-danger' : ($estadoReal === 'Prestado' ? 'bg-info' : 'bg-secondary')) }}">
                                                            {{ $estadoReal }}
                                                        </span>
                                                    </div>

                                                    <!-- Footer: [usuario][fecha][cliente] -->
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-muted flex-wrap gap-1">
                                                        <small>
                                                            <i
                                                                class="fa-solid fa-user-tie me-1"></i>{{ $prestamo->user->name ?? 'Usuario' }}
                                                        </small>
                                                        <small>
                                                            <i
                                                                class="fa-solid fa-calendar me-1"></i>{{ $prestamo->created_at->format('d/m/Y H:i') }}
                                                        </small>
                                                        <small>
                                                            <i
                                                                class="fa-solid fa-user me-1"></i>{{ $prestamo->cliente->nombre ?? 'Sin cliente' }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-handshake fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No se encontraron préstamos</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.paginate-bar', ['results' => $prestamos, 'storageKey' => 'prestamos'])

    <!-- Modal de Filtro de Fechas -->
    @if ($mostrarModalFiltro)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Filtrar por Fechas</h5>
                        <button type="button" class="btn-close" wire:click="cerrarModalFiltro"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Desde</label>
                                <input type="date" class="form-control" wire:model.live="fecha_inicio"
                                    @if ($fecha_fin) max="{{ $fecha_fin }}" @endif>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Hasta</label>
                                <input type="date" class="form-control" wire:model.live="fecha_fin"
                                    @if ($fecha_inicio) min="{{ $fecha_inicio }}" @endif
                                    @if (!$fecha_inicio) disabled @endif>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="cerrarModalFiltro">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Detalles de Préstamo -->
    @if ($mostrarModal && $prestamoSeleccionado)
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-handshake me-2"></i>Préstamo
                            #{{ $prestamoSeleccionado->numero_folio }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"
                            aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-0">
                        <!-- Tabla de productos -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="align-middle">Producto</th>
                                        <th class="text-center align-middle">Cantidad</th>
                                        <th class="text-end align-middle">Precio</th>
                                        <th class="text-end align-middle">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($prestamoSeleccionado->prestamoItems as $item)
                                        <tr style="cursor: pointer;"
                                            x-on:click="$dispatch('mostrarKardex', { productoId: {{ $item->producto_id }} })"
                                            title="Clic para ver movimientos de Kardex">
                                            <td class="align-middle text-truncate">
                                                <strong>{{ $item->producto->nombre ?? 'Producto' }}</strong>
                                            </td>
                                            <td class="text-center align-middle text-truncate">
                                                <span class="badge bg-info text-dark">{{ $item->cantidad }}</span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">Bs.
                                                {{ number_format($item->precio_por_paquete, 2) }}</td>
                                            <td class="text-end align-middle text-truncate">
                                                <strong>Bs. {{ number_format($item->subtotal, 2) }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Tarjeta de depósito -->
                        <div class="p-3">
                            <div class="row g-2 justify-content-center">
                                <div class="col-6 col-md-4">
                                    <div class="rounded px-3 py-2 text-center h-100 d-flex flex-column justify-content-center" style="background-color: #f0f0f0;">
                                        <small class="text-dark d-block">Depósito</small>
                                        <span class="fw-bold fs-5 text-dark">Bs. {{ number_format($prestamoSeleccionado->deposito, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-user-tie me-1"></i>{{ $prestamoSeleccionado->user->name ?? 'Usuario' }}
                            </small>
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-user me-1"></i>{{ $prestamoSeleccionado->cliente->nombre ?? 'Sin cliente' }}
                            </small>
                            @if ($prestamoSeleccionado->fecha_vencimiento)
                                <small class="text-muted">
                                    <i
                                        class="fa-solid fa-calendar-check me-1"></i>{{ $prestamoSeleccionado->fecha_vencimiento->format('d/m/Y') }}
                                </small>
                            @endif
                            @if ($prestamoSeleccionado->estado === 'Prestado')
                                <button type="button" class="btn btn-success" wire:click="procesarDevolucion"
                                    wire:loading.attr="disabled" @if ($procesandoDevolucion) disabled @endif>
                                    <span wire:loading.remove wire:target="procesarDevolucion">
                                        <i class="fa-solid fa-rotate-left me-1"></i>Devolver
                                    </span>
                                    <span wire:loading wire:target="procesarDevolucion">
                                        <i class="fa-solid fa-spinner fa-spin me-1"></i>Procesando...
                                    </span>
                                </button>
                            @else
                                <span
                                    class="badge bg-{{ $prestamoSeleccionado->estado === 'Devuelto' ? 'success' : 'secondary' }} fs-6">
                                    {{ $prestamoSeleccionado->estado }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
        <script>
            // Gestionar el estado del body cuando hay modales abiertos
            $wire.on('$refresh', () => {
                if ($wire.mostrarModal || $wire.mostrarModalFiltro) {
                    document.body.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = '0px';
                } else {
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            });

            // Cerrar modal al hacer clic fuera del contenido
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-backdrop') ||
                    (e.target.classList.contains('modal') && e.target.classList.contains('show'))) {
                    if ($wire.mostrarModal) {
                        $wire.call('cerrarModal');
                    } else if ($wire.mostrarModalFiltro) {
                        $wire.call('cerrarModalFiltro');
                    }
                }
            });

            // SweetAlert para toasts
            $wire.on('alert', (event) => {
                const data = event[0] || event;
                Swal.fire({
                    title: data.type === 'success' ? '¡Éxito!' : 'Error',
                    text: data.message,
                    icon: data.type,
                    confirmButtonColor: data.type === 'success' ? '#28a745' : '#d33',
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    @endscript

    <!-- Componente anidado de Kardex Modal -->
    <livewire:kardex-modal />
</div>

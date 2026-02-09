<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Compras</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    @if($fecha_inicio && $fecha_fin)
                                        <button type="button" class="btn btn-outline-danger" wire:click="limpiarFiltroFechas" title="Limpiar filtro de fechas">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-outline-secondary" wire:click="abrirModalFiltro" title="Filtrar por fechas">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </button>
                                    @endif
                                    <input type="text" class="form-control text-start" placeholder="Buscar compra..."
                                        wire:model.live="search" style="min-width: 200px;" id="searchInput" autofocus>
                                    <button class="btn btn-primary" wire:click="crearCompra"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para móvil -->
                    <div class="card-header card-no-border d-md-none" style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="input-group">
                            @if($fecha_inicio && $fecha_fin)
                                <button type="button" class="btn btn-outline-danger" wire:click="limpiarFiltroFechas" title="Limpiar filtro de fechas">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            @else
                                <button class="btn btn-outline-secondary" wire:click="abrirModalFiltro" title="Filtrar por fechas">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </button>
                            @endif
                            <input type="text" class="form-control text-start" placeholder="Buscar compra..."
                                wire:model.live="search" id="searchInput" autofocus>
                            <button class="btn btn-primary" wire:click="crearCompra"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-2">
                        <div class="row g-3">
                            @forelse($compras as $compra)
                                <div class="col-md-4 col-12">
                                    <div class="card mb-0 shadow-sm {{ $compra->estado === 'Eliminado' ? 'opacity-50' : '' }}">
                                        <div class="card-body compra-card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <!-- Header -->
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h4 class="mb-0 fw-bold">
                                                            Compra #{{ $compra->numero_folio }}
                                                            @if($compra->estado === 'Eliminado')
                                                                <span class="badge bg-danger ms-2">Cancelada</span>
                                                            @endif
                                                        </h4>
                                                        <div class="d-flex gap-1">
                                                            @if ($compra->estado === 'Eliminado')
                                                                <button class="btn btn-sm btn-info"
                                                                    wire:click="verDetalles({{ $compra->id }})"
                                                                    title="Ver detalles">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-secondary"
                                                                    wire:click="generarPDF({{ $compra->id }})"
                                                                    title="Generar PDF">
                                                                    <i class="fa-solid fa-file-pdf"></i>
                                                                </button>
                                                            @elseif ($compra->estado === 'Completo')
                                                                <button class="btn btn-sm btn-info"
                                                                    wire:click="verDetalles({{ $compra->id }})"
                                                                    title="Ver detalles">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                                @if ($compra->credito > 0)
                                                                    <button class="btn btn-sm btn-warning"
                                                                        wire:click="abrirModalPago({{ $compra->id }})"
                                                                        title="Pagar crédito">
                                                                        <i class="fa-solid fa-money-bill"></i>
                                                                    </button>
                                                                @endif
                                                                <button class="btn btn-sm btn-secondary"
                                                                    wire:click="generarPDF({{ $compra->id }})"
                                                                    title="Generar PDF">
                                                                    <i class="fa-solid fa-file-pdf"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger"
                                                                    wire:click="$dispatch('confirm-delete', { id: {{ $compra->id }}, message: '¿Está seguro de eliminar la compra #{{ $compra->numero_folio }}?' })"
                                                                    title="Cancelar">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            @else
                                                                <a href="{{ route('compra', ['compraId' => $compra->id]) }}"
                                                                    class="btn btn-sm btn-success"
                                                                    title="Continuar compra">
                                                                    <i class="fa-solid fa-arrow-right"></i>
                                                                </a>
                                                                <button class="btn btn-sm btn-danger"
                                                                    wire:click="$dispatch('confirm-delete', { id: {{ $compra->id }}, message: '¿Está seguro de eliminar la compra #{{ $compra->numero_folio }}?' })"
                                                                    title="Cancelar">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Avatar Group de productos -->
                                                    <div class="avatar-group mb-3">
                                                        @foreach ($compra->compraItems as $item)
                                                            <div class="avatar" style="cursor: pointer;"
                                                                x-on:click="$dispatch('mostrarKardex', { productoId: {{ $item->producto_id }} })"
                                                                title="{{ $item->producto->nombre ?? 'Producto' }} - Clic para ver Kardex">
                                                                <img src="{{ $item->producto->photo_url }}"
                                                                    alt="{{ $item->producto->nombre }}">
                                                                <span
                                                                    class="quantity-badge">{{ $item->cantidad_formateada }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <!-- Badges de totales -->
                                                    <div class="d-flex gap-2 flex-wrap mb-2">
                                                        @php
                                                            $total = $compra->efectivo + $compra->credito;
                                                        @endphp
                                                        <span class="badge bg-primary d-none d-md-inline">Total:
                                                            Bs. {{ number_format($total, 2) }}</span>
                                                        @if ($compra->efectivo > 0)
                                                            <span class="badge bg-success">
                                                                Bs. {{ number_format($compra->efectivo, 2) }}</span>
                                                        @endif
                                                        @if ($compra->credito > 0)
                                                            <span class="badge bg-danger">
                                                                Bs. {{ number_format($compra->credito, 2) }}</span>
                                                        @endif
                                                    </div>

                                                    <!-- Footer info -->
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-muted">
                                                        <small class="d-none d-md-inline"><i
                                                                class="fa-solid fa-user me-1"></i>{{ $compra->user->name ?? 'Usuario' }}</small>
                                                        <small><i
                                                                class="fa-solid fa-calendar me-1"></i>{{ $compra->created_at->format('d/m/Y H:i') }}</small>
                                                        <small><i
                                                                class="fa-solid fa-truck me-1"></i>{{ $compra->proveedor->nombre ?? 'Sin proveedor' }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-cart-shopping fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No se encontraron compras</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer fijo con paginado -->
    <footer class="fixed-footer shadow-sm py-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted d-none d-md-block">Created By <a href="https://dieguitosoft.com"
                        target="_blank">DieguitoSoft.com</a></small>
                <div class="d-flex align-items-center gap-2">
                    <div x-data="{
                        init() {
                            const saved = localStorage.getItem('paginateCompras') || document.cookie.split('; ').find(row => row.startsWith('paginateCompras='))?.split('=')[1];
                            if (saved) {
                                $wire.set('perPage', parseInt(saved));
                            }
                        }
                    }">
                        <input type="number" class="form-control form-control-sm text-center" style="width: 60px;"
                            wire:model.live="perPage" min="1" max="100" title="Registros por página"
                            onfocus="this.select()"
                            @input="
                                   localStorage.setItem('paginateCompras', $event.target.value);
                                   document.cookie = 'paginateCompras=' + $event.target.value + '; path=/; max-age=31536000';
                               ">
                    </div>
                    {{ $compras->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal de Detalles de Compra -->
    @if ($mostrarModal && $compraSeleccionada)
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-shopping-cart me-2"></i>Compra #{{ $compraSeleccionada->id }}
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
                                        <th class="text-end align-middle" style="width: 35px;">Cantidad</th>
                                        <th class="text-end align-middle" style="width: 105px;">Precio</th>
                                        <th class="text-end align-middle" style="width: 130px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($compraSeleccionada->compraItems as $item)
                                        <tr style="cursor: pointer;"
                                            x-on:click="$dispatch('mostrarKardex', { productoId: {{ $item->producto_id }} })"
                                            title="{{ $item->producto->nombre ?? 'Producto' }}">
                                            <td class="align-middle" style="max-width: 220px;">
                                                <strong class="d-block text-truncate">{{ $item->producto->nombre ?? 'Producto' }}</strong>
                                            </td>
                                            <td class="text-end align-middle" style="width: 35px;">{{ $item->cantidad_formateada }}</td>
                                            <td class="text-end align-middle text-truncate" style="max-width: 105px;">Bs. {{ number_format($item->precio, 2) }}</td>
                                            <td class="text-end align-middle text-truncate" style="max-width: 130px;"><strong>Bs. {{ number_format($item->subtotal, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Tarjetas de totales -->
                        @php
                            $totalCompra = $compraSeleccionada->efectivo + $compraSeleccionada->credito;

                            // Crear array de tarjetas
                            $tarjetas = [];
                            $tarjetas[] = ['label' => 'Total', 'valor' => $totalCompra, 'color' => ''];
                            if ($compraSeleccionada->efectivo > 0) {
                                $tarjetas[] = ['label' => 'Efectivo', 'valor' => $compraSeleccionada->efectivo, 'color' => ''];
                            }
                            if ($compraSeleccionada->credito > 0) {
                                $tarjetas[] = ['label' => 'Crédito', 'valor' => $compraSeleccionada->credito, 'color' => ''];
                            }

                            $numTarjetas = count($tarjetas);

                            // Calcular clase de columna (móvil col-6, desktop dinámico)
                            $colClass = match($numTarjetas) {
                                1 => 'col-6 col-md-12',
                                2 => 'col-6',
                                3 => 'col-6 col-md-4',
                                default => 'col-6 col-md-4'
                            };
                        @endphp
                        <div class="p-3">
                            <div class="row g-2">
                                @foreach ($tarjetas as $tarjeta)
                                    <div class="{{ $colClass }}">
                                        <div class="rounded px-3 py-2 text-center h-100 d-flex flex-column justify-content-center" style="background-color: #f0f0f0;">
                                            <small class="text-dark d-block">{{ $tarjeta['label'] }}</small>
                                            <span class="fw-bold fs-5 {{ $tarjeta['color'] ?: 'text-dark' }}">Bs. {{ number_format($tarjeta['valor'], 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-user me-1"></i>{{ $compraSeleccionada->user->name ?? 'Usuario' }}
                            </small>
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-calendar me-1"></i>{{ $compraSeleccionada->created_at->format('d/m/Y H:i') }}
                            </small>
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-truck me-1"></i>{{ $compraSeleccionada->proveedor->nombre ?? 'Sin proveedor' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Resumen de Eliminación -->
    @if ($mostrarResumenEliminacion && !empty($resumenEliminacion))
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            Compra #{{ $resumenEliminacion['compra_id'] }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarResumen"
                            aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="align-middle">Producto</th>
                                        <th class="text-end align-middle">Anterior</th>
                                        <th class="text-end align-middle">Cantidad</th>
                                        <th class="text-end align-middle">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resumenEliminacion['productos'] as $producto)
                                        <tr>
                                            <td class="align-middle text-truncate">
                                                <strong>{{ $producto['nombre'] }}</strong>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-warning text-dark">
                                                    {{ $producto['stock_anterior_formateado'] }}
                                                </span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-info text-dark">
                                                    {{ $producto['cantidad_formateada'] }}
                                                </span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-success">
                                                    {{ $producto['stock_nuevo_formateado'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="row g-2 justify-content-center w-100">
                            <div class="col-6 col-md-4">
                                <div class="rounded px-3 py-2 text-center h-100 d-flex flex-column justify-content-center" style="background-color: #f0f0f0;">
                                    <small class="text-dark d-block">Devuelto a Caja</small>
                                    <span class="fw-bold fs-5 text-success">Bs. {{ number_format($resumenEliminacion['devuelto_caja'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Error de Stock Insuficiente -->
    @if ($mostrarErrorStock && !empty($productosInsuficientes))
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Compra #{{ $productosInsuficientes['compra_id'] }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarErrorStock"
                            aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="align-middle">Producto</th>
                                        <th class="text-end align-middle">Stock</th>
                                        <th class="text-end align-middle">Requerido</th>
                                        <th class="text-end align-middle">Faltante</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($productosInsuficientes['productos'] as $producto)
                                        <tr>
                                            <td class="align-middle text-truncate">
                                                <strong>{{ $producto['nombre'] }}</strong>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-warning text-dark">
                                                    {{ $producto['stock_formateado'] }}
                                                </span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-info text-dark">
                                                    {{ $producto['cantidad_formateada'] }}
                                                </span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-danger">
                                                    {{ $producto['faltante_formateado'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="alert alert-danger mb-0 w-100" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>No se puede eliminar esta compra</strong> porque devolver los productos al inventario
                            generaría cantidades negativas en el stock.
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
                if ($wire.mostrarErrorStock || $wire.mostrarResumenEliminacion || $wire.mostrarModal) {
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
                // Detectar clic en el backdrop
                if (e.target.classList.contains('modal-backdrop')) {
                    if ($wire.mostrarErrorStock) {
                        $wire.call('cerrarErrorStock');
                    } else if ($wire.mostrarResumenEliminacion) {
                        $wire.call('cerrarResumen');
                    } else if ($wire.mostrarModal) {
                        $wire.call('cerrarModal');
                    }
                }

                // Detectar clic en el área del modal pero fuera del modal-content
                if (e.target.classList.contains('modal') && e.target.classList.contains('show')) {
                    if ($wire.mostrarErrorStock) {
                        $wire.call('cerrarErrorStock');
                    } else if ($wire.mostrarResumenEliminacion) {
                        $wire.call('cerrarResumen');
                    } else if ($wire.mostrarModal) {
                        $wire.call('cerrarModal');
                    }
                }
            });

            $wire.on('alert', (event) => {
                // En Livewire 3, el evento llega como array
                const data = event[0] || event;
                Swal.fire({
                    title: data.type === 'success' ? '¡Éxito!' : 'Error',
                    text: data.message,
                    icon: data.type,
                    confirmButtonColor: data.type === 'success' ? '#28a745' : '#d33',
                    confirmButtonText: 'Aceptar'
                });
            });

            $wire.on('confirm-delete', (event) => {
                const data = event[0] || event;
                Swal.fire({
                    title: '¿Está seguro?',
                    text: data.message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.call('eliminar', data.id);
                    }
                });
            });
        </script>
    @endscript

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
                                    @if($fecha_fin) max="{{ $fecha_fin }}" @endif>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Hasta</label>
                                <input type="date" class="form-control" wire:model.live="fecha_fin"
                                    @if($fecha_inicio) min="{{ $fecha_inicio }}" @endif
                                    @if(!$fecha_inicio) disabled @endif>
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

    <!-- Overlay de Pago de Crédito - Paso 1: Añadir Fondos -->
    @if ($mostrarModalPago && $compraAPagar && $pasoPago === 1)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-money-bill me-2"></i>
                            Paso 1: Añadir Fondos
                        </h5>
                    </div>
                    <div class="modal-body">
                        <!-- Información de la Compra -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Compra:</small>
                                    <strong class="d-block text-dark">#{{ $compraAPagar->numero_folio }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Usuario:</small>
                                    <strong class="d-block text-truncate text-dark px-2" title="{{ $compraAPagar->user->name ?? 'N/A' }}">{{ $compraAPagar->user->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Proveedor:</small>
                                    <strong class="d-block text-truncate text-dark px-2" title="{{ $compraAPagar->proveedor->nombre ?? 'Sin proveedor' }}">{{ $compraAPagar->proveedor->nombre ?? 'Sin proveedor' }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Faltante:</small>
                                    <strong class="d-block text-danger">Bs. {{ number_format(max($compraAPagar->credito - $saldoCaja, 0), 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Saldos -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Saldo en Caja</small>
                                    <h5 class="mb-0 text-primary">Bs. {{ number_format($saldoCaja, 2) }}</h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Monto a Pagar</small>
                                    <h5 class="mb-0 text-danger">Bs. {{ number_format($compraAPagar->credito, 2) }}</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Input para añadir fondos -->
                        <div class="mb-3">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">Bs.</span>
                                <input type="number"
                                    id="montoAñadirCaja"
                                    class="form-control"
                                    wire:model="montoAñadirCaja"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    autofocus>
                            </div>
                            <small class="text-muted">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                Presiona Enter para continuar
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModalPago">
                            <i class="fa-solid fa-times me-1"></i>
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-warning" wire:click="avanzarPasoPago1">
                            <i class="fa-solid fa-arrow-right me-1"></i>
                            Continuar <span class="badge bg-white text-dark ms-1">Enter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Overlay de Pago de Crédito - Paso 2: Ingresar Monto -->
    @if ($mostrarModalPago && $compraAPagar && $pasoPago === 2)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-money-bill me-2"></i>
                            Paso 2: Monto a Pagar
                        </h5>
                    </div>
                    <div class="modal-body">
                        <!-- Información de la Compra -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Compra:</small>
                                    <strong class="d-block text-dark">#{{ $compraAPagar->numero_folio }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Usuario:</small>
                                    <strong class="d-block text-truncate text-dark px-2" title="{{ $compraAPagar->user->name ?? 'N/A' }}">{{ $compraAPagar->user->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Proveedor:</small>
                                    <strong class="d-block text-truncate text-dark px-2" title="{{ $compraAPagar->proveedor->nombre ?? 'Sin proveedor' }}">{{ $compraAPagar->proveedor->nombre ?? 'Sin proveedor' }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Máximo a Pagar:</small>
                                    <strong class="d-block text-primary">Bs. {{ number_format(min($saldoCaja, $compraAPagar->credito), 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Saldos -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Saldo en Caja</small>
                                    <h5 class="mb-0 {{ $saldoCaja >= $compraAPagar->credito ? 'text-success' : 'text-warning' }}">
                                        Bs. {{ number_format($saldoCaja, 2) }}
                                    </h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Deuda a Pagar</small>
                                    <h5 class="mb-0 text-danger">Bs. {{ number_format($compraAPagar->credito, 2) }}</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Input para monto a pagar -->
                        <div class="mb-3">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">Bs.</span>
                                <input type="number"
                                    id="montoPago"
                                    class="form-control"
                                    wire:model.defer="montoPago"
                                    step="0.01"
                                    min="0.01"
                                    max="{{ min($saldoCaja, $compraAPagar->credito) }}"
                                    placeholder="0.00"
                                    autofocus>
                            </div>
                            @if ($montoPago > 0 && $montoPago < $compraAPagar->credito)
                                <small class="text-warning d-block mt-2">
                                    <i class="fa-solid fa-info-circle me-1"></i>
                                    Pago parcial. Saldo pendiente: Bs. {{ number_format($compraAPagar->credito - $montoPago, 2) }}
                                </small>
                            @endif
                            @if ($montoPago > $saldoCaja)
                                <small class="text-danger d-block mt-2">
                                    <i class="fa-solid fa-exclamation-triangle me-1"></i>
                                    El monto excede el saldo en caja
                                </small>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="retrocederPasoPago">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Atrás
                        </button>
                        <button type="button"
                            class="btn btn-warning"
                            wire:click="avanzarPasoPago2"
                            {{ ($montoPago <= 0 || $montoPago > $compraAPagar->credito || $montoPago > $saldoCaja) ? 'disabled' : '' }}>
                            <i class="fa-solid fa-check me-1"></i>
                            Procesar Pago <span class="badge bg-white text-dark ms-1">Enter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Overlay de Pago de Crédito - Paso 3: Procesando -->
    @if ($mostrarModalPago && $compraAPagar && $pasoPago === 3)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-body py-5">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-4" role="status" style="width: 4rem; height: 4rem;">
                                <span class="visually-hidden">Procesando...</span>
                            </div>
                            <h4 class="text-primary mb-3">
                                <i class="fa-solid fa-clock me-2"></i>
                                Procesando pago...
                            </h4>
                            <p class="text-muted mb-0">
                                Por favor espere mientras se completa la transacción
                            </p>
                            <div class="progress mt-4" style="height: 8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                     role="progressbar"
                                     style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
        <script>
            // Manejo de teclado para la secuencia de pago
            document.addEventListener('keydown', function(e) {
                // Solo si el modal de pago está abierto
                if (!$wire.mostrarModalPago) return;

                // Paso 1: Añadir fondos
                if ($wire.pasoPago === 1) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $wire.avanzarPasoPago1();
                    }
                }

                // Paso 2: Monto de pago
                if ($wire.pasoPago === 2) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const montoPago = parseFloat($wire.montoPago);
                        const saldoCaja = parseFloat($wire.saldoCaja);

                        // Validar que el monto sea válido
                        if (montoPago > 0 && montoPago <= saldoCaja) {
                            $wire.avanzarPasoPago2();
                        }
                    }
                }

                // Escape para cerrar (solo en pasos 1 y 2)
                if ($wire.pasoPago < 3 && e.key === 'Escape') {
                    e.preventDefault();
                    $wire.cerrarModalPago();
                }
            });

            // Auto-focus en inputs cuando cambian los pasos
            $wire.on('paso-changed', () => {
                setTimeout(() => {
                    const input = document.querySelector('#montoAñadirCaja, #montoPago');
                    if (input) {
                        input.focus();
                        input.select();
                    }
                }, 100);
            });

            // Focus inicial
            Livewire.hook('morph.updated', ({ el, component }) => {
                if ($wire.mostrarModalPago) {
                    setTimeout(() => {
                        const input = document.querySelector('#montoAñadirCaja, #montoPago');
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    }, 100);
                }
            });
        </script>
    @endscript

    <!-- Componente anidado de Kardex Modal -->
    <livewire:kardex-modal />
</div>

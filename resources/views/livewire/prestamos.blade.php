<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">PrÃ©stamos de Envases</h3>
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
                                    <input type="text" class="form-control text-start" placeholder="Buscar prÃ©stamo..."
                                        wire:model.live="search" style="min-width: 200px;" id="searchInput" autofocus>
                                    <button class="btn btn-primary" wire:click="crearPrestamo"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para mÃ³vil -->
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
                            <input type="text" class="form-control text-start" placeholder="Buscar prÃ©stamo..."
                                wire:model.live="search" id="searchInput" autofocus>
                            <button class="btn btn-primary" wire:click="crearPrestamo"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-2">
                        <div class="row g-3">
                            @forelse($prestamos as $prestamo)
                                <div class="col-md-4 col-12">
                                    <div class="card mb-0 shadow-sm {{ $prestamo->estado === 'Eliminado' ? 'opacity-50' : '' }}">
                                        <div class="card-body compra-card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <!-- Header -->
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h4 class="mb-0 fw-bold">
                                                            PrÃ©stamo #{{ $prestamo->numero_folio }}
                                                            @if($prestamo->estado === 'Eliminado')
                                                                <span class="badge bg-danger ms-2">Cancelado</span>
                                                            @elseif($prestamo->estado === 'Devuelto')
                                                                <span class="badge bg-success ms-2">Devuelto</span>
                                                            @endif
                                                        </h4>
                                                        <div class="d-flex gap-1">
                                                            @if ($prestamo->estado === 'Eliminado')
                                                                <button class="btn btn-sm btn-info"
                                                                    wire:click="verDetalles({{ $prestamo->id }})"
                                                                    title="Ver detalles">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-secondary"
                                                                    wire:click="generarPDF({{ $prestamo->id }})"
                                                                    title="Generar PDF">
                                                                    <i class="fa-solid fa-file-pdf"></i>
                                                                </button>
                                                            @elseif ($prestamo->estado === 'Completo')
                                                                <button class="btn btn-sm btn-info"
                                                                    wire:click="verDetalles({{ $prestamo->id }})"
                                                                    title="Ver detalles">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-secondary"
                                                                    wire:click="generarPDF({{ $prestamo->id }})"
                                                                    title="Generar PDF">
                                                                    <i class="fa-solid fa-file-pdf"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger"
                                                                    wire:click="$dispatch('confirm-delete', { id: {{ $prestamo->id }}, message: '¿Está seguro de eliminar la Prï¿½stamo #{{ $prestamo->numero_folio }}?' })"
                                                                    title="Cancelar">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            @else
                                                                @if($prestamo->user_id === auth()->id())
                                                                    <a href="{{ route('prestamo', ['prestamoId' => $prestamo->id]) }}"
                                                                        class="btn btn-sm btn-success"
                                                                        title="Continuar prestamo">
                                                                        <i class="fa-solid fa-arrow-right"></i>
                                                                    </a>
                                                                @endif
                                                                @if($prestamo->user_id === auth()->id())
                                                                    <button class="btn btn-sm btn-danger"
                                                                        wire:click="$dispatch('confirm-delete', { id: {{ $prestamo->id }}, message: '¿Está seguro de eliminar la Prï¿½stamo #{{ $prestamo->numero_folio }}?' })"
                                                                        title="Cancelar">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </button>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Avatar Group de productos -->
                                                    <div class="avatar-group mb-3">
                                                        @foreach ($prestamo->prestamoItems as $item)
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
                                                        @if ($prestamo->deposito > 0)
                                                            <span class="badge bg-warning text-dark">
                                                                DepÃ³sito: Bs. {{ number_format($prestamo->deposito, 2) }}</span>
                                                        @endif
                                                        @if ($prestamo->estado === 'Completo')
                                                            <span class="badge bg-info">Prestado</span>
                                                        @elseif ($prestamo->estado === 'Devuelto')
                                                            <span class="badge bg-success">Devuelto</span>
                                                        @endif
                                                    </div>

                                                    <!-- Footer info -->
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-muted">
                                                        <small class="d-none d-md-inline"><i
                                                                class="fa-solid fa-user me-1"></i>{{ $prestamo->user->name ?? 'Usuario' }}</small>
                                                        <small><i
                                                                class="fa-solid fa-calendar me-1"></i>{{ $prestamo->created_at->format('d/m/Y H:i') }}</small>
                                                        <small><i
                                                                class="fa-solid fa-user-tie me-1"></i>{{ $prestamo->cliente->nombre ?? 'Sin cliente' }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                                        <i class="fa-solid fa-box-open fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No se encontraron prÃ©stamos</p>
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
                            const saved = localStorage.getItem('paginateprï¿½stamos') || document.cookie.split('; ').find(row => row.startsWith('paginateprï¿½stamos='))?.split('=')[1];
                            if (saved) {
                                $wire.set('perPage', parseInt(saved));
                            }
                        }
                    }">
                        <input type="number" class="form-control form-control-sm text-center" style="width: 60px;"
                            wire:model.live="perPage" min="1" max="100" title="Registros por página"
                            onfocus="this.select()"
                            @input="
                                   localStorage.setItem('paginatePrestamos', $event.target.value);
                                   document.cookie = 'paginatePrestamos=' + $event.target.value + '; path=/; max-age=31536000';
                               ">
                    </div>
                    {{ $prestamos->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal de Detalles de prestamo -->
    @if ($mostrarModal && $prï¿½stamoseleccionada)
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-box-open me-2"></i>Prï¿½stamo #{{ $prï¿½stamoseleccionada->numero_folio }}
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
                                    @foreach ($prï¿½stamoseleccionada->prestamoItems as $item)
                                        <tr style="cursor: pointer;"
                                            x-on:click="$dispatch('mostrarKardex', { productoId: {{ $item->producto_id }} })"
                                            title="Clic para ver movimientos de Kardex">
                                            <td class="align-middle text-truncate">
                                                <strong>{{ $item->producto->nombre ?? 'Producto' }}</strong>
                                            </td>
                                            <td class="text-center align-middle text-truncate">
                                                <span
                                                    class="badge bg-info text-dark">{{ $item->cantidad_formateada }}</span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">Bs.
                                                {{ number_format($item->precio_deposito, 2) }}</td>
                                            <td class="text-end align-middle text-truncate">
                                                <strong>Bs. {{ number_format($item->subtotal_deposito, 2) }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end align-middle text-truncate">
                                            <strong>Total:</strong></td>
                                        <td class="text-end align-middle text-truncate">
                                            <strong class="text-primary fs-5">
                                                Bs.
                                                {{ number_format($prï¿½stamoseleccionada->efectivo + $prï¿½stamoseleccionada->online + $prï¿½stamoseleccionada->credito, 2) }}
                                            </strong>
                                        </td>
                                    </tr>
                                    @if ($prï¿½stamoseleccionada->efectivo > 0)
                                        <tr>
                                            <td colspan="3" class="text-end align-middle text-truncate">Efectivo:</td>
                                            <td class="text-end text-success align-middle text-truncate">
                                                <strong>Bs.
                                                    {{ number_format($prï¿½stamoseleccionada->efectivo, 2) }}</strong>
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($prï¿½stamoseleccionada->online > 0)
                                        <tr>
                                            <td colspan="3" class="text-end align-middle text-truncate">Online:</td>
                                            <td class="text-end text-info align-middle text-truncate">
                                                <strong>Bs.
                                                    {{ number_format($prï¿½stamoseleccionada->online, 2) }}</strong>
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($prï¿½stamoseleccionada->credito > 0)
                                        <tr>
                                            <td colspan="3" class="text-end align-middle text-truncate">CrÃ©dito:</td>
                                            <td class="text-end text-danger align-middle text-truncate">
                                                <strong>Bs.
                                                    {{ number_format($prï¿½stamoseleccionada->credito, 2) }}</strong>
                                            </td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-user me-1"></i>{{ $prï¿½stamoseleccionada->user->name ?? 'Usuario' }}
                            </small>
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-calendar me-1"></i>{{ $prï¿½stamoseleccionada->created_at->format('d/m/Y H:i') }}
                            </small>
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-user-tie me-1"></i>{{ $prï¿½stamoseleccionada->cliente->nombre ?? 'Sin cliente' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Resumen de EliminaciÃ³n -->
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
                            prestamo Eliminada #{{ $resumenEliminacion['prestamo_id'] }}
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
                                        <th class="text-end align-middle">Nuevo</th>
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
                        <div class="w-100 text-end">
                            <strong class="text-dark">Retirado de Caja: </strong>
                            <strong class="text-danger fs-5">Bs. {{ number_format($resumenEliminacion['devuelto_caja'] ?? 0, 2) }}</strong>
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

                // Detectar clic en el Ã¡rea del modal pero fuera del modal-content
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
                    title: data.type === 'success' ? 'Â¡Ã‰xito!' : 'Error',
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
                    confirmButtonText: 'SÃ­, eliminar',
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

    <!-- Overlay de Pago de CrÃ©dito - Paso 1: AÃ±adir Fondos -->
    {{-- Modal de Pago de CrÃ©dito --}}
    @if ($mostrarModalPago && $prestamoAPagar)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;"
            x-data="{
                efectivo: {{ $montoPagoEfectivo ?? 0 }},
                online: {{ $montoPagoOnline ?? 0 }},
                creditoTotal: {{ $prestamoAPagar->credito }},
                get totalPago() {
                    return parseFloat(this.efectivo || 0) + parseFloat(this.online || 0);
                },
                get creditoRestante() {
                    return Math.max(0, this.creditoTotal - this.totalPago);
                },
                finalizarPago() {
                    if ({{ $procesandoPago ? 'true' : 'false' }} || this.totalPago <= 0 || this.totalPago > this.creditoTotal) {
                        return;
                    }
                    $wire.set('montoPagoEfectivo', this.efectivo);
                    $wire.set('montoPagoOnline', this.online);
                    $wire.pagarCredito();
                }
            }"
            @keydown.enter="finalizarPago()">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-money-bill me-2"></i>
                            Pagar CrÃ©dito
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalPago" {{ $procesandoPago ? 'disabled' : '' }}></button>
                    </div>
                    <div class="modal-body">
                        <!-- InformaciÃ³n de la prestamo -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">prestamo:</small>
                                    <strong class="d-block text-dark">#{{ $prestamoAPagar->numero_folio }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Cliente:</small>
                                    <strong class="d-block text-truncate text-dark px-2" title="{{ $prestamoAPagar->cliente->nombre ?? 'Sin cliente' }}">{{ $prestamoAPagar->cliente->nombre ?? 'Sin cliente' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Total CrÃ©dito -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total CrÃ©dito</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Bs.</span>
                                    <input type="number"
                                        class="form-control bg-danger bg-opacity-10 text-danger fw-bold text-end"
                                        :value="creditoTotal"
                                        disabled>
                                </div>
                            </div>

                            <!-- Efectivo -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Efectivo</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Bs.</span>
                                    <input type="number"
                                        id="montoPagoEfectivo"
                                        class="form-control text-end"
                                        x-model.number="efectivo"
                                        step="0.01"
                                        min="0"
                                        :max="creditoTotal"
                                        placeholder="0.00"
                                        {{ $procesandoPago ? 'disabled' : '' }}>
                                </div>
                            </div>

                            <!-- Online -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Online</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Bs.</span>
                                    <input type="number"
                                        id="montoPagoOnline"
                                        class="form-control text-end"
                                        x-model.number="online"
                                        step="0.01"
                                        min="0"
                                        :max="creditoTotal"
                                        placeholder="0.00"
                                        {{ $procesandoPago ? 'disabled' : '' }}>
                                </div>
                            </div>

                            <!-- CrÃ©dito Restante -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">CrÃ©dito Restante</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Bs.</span>
                                    <input type="number"
                                        class="form-control fw-bold text-end"
                                        :class="creditoRestante > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success'"
                                        :value="creditoRestante.toFixed(2)"
                                        disabled>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen en barra horizontal -->
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Total a Pagar:</small>
                                    <strong class="d-block" :class="totalPago > 0 ? 'text-success' : 'text-muted'">
                                        Bs. <span x-text="totalPago.toFixed(2)">0.00</span>
                                    </strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Efectivo:</small>
                                    <strong class="d-block text-primary">Bs. <span x-text="(efectivo || 0).toFixed(2)">0.00</span></strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Online:</small>
                                    <strong class="d-block text-info">Bs. <span x-text="(online || 0).toFixed(2)">0.00</span></strong>
                                </div>
                            </div>
                        </div>

                        <div x-show="totalPago > creditoTotal" class="alert alert-danger mb-0">
                            <i class="fa-solid fa-exclamation-triangle me-1"></i>
                            El monto total excede la deuda pendiente
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-secondary"
                            wire:click="cerrarModalPago"
                            {{ $procesandoPago ? 'disabled' : '' }}>
                            <i class="fa-solid fa-times me-1"></i>
                            Cancelar
                        </button>
                        <button type="button"
                            class="btn btn-success"
                            @click="finalizarPago()"
                            :disabled="{{ $procesandoPago ? 'true' : 'false' }} || totalPago <= 0 || totalPago > creditoTotal">
                            @if ($procesandoPago)
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Procesando...
                            @else
                                <i class="fa-solid fa-check me-1"></i>
                                Finalizar Pago <span class="badge bg-white text-success ms-1">Enter</span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Procesando (Spinner Completo) --}}
    @if ($procesandoPago && $mostrarModalPago)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.7); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-transparent border-0 shadow-lg">
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 4rem; height: 4rem;">
                            <span class="visually-hidden">Procesando...</span>
                        </div>
                        <h5 class="text-white mb-2">
                            <i class="fa-solid fa-clock me-2"></i>
                            Procesando pago
                        </h5>
                        <p class="text-white-50 mb-0">Por favor espere mientras se completa la transacciÃ³n</p>
                        <div class="progress mt-3" style="height: 3px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
        <script>
            // Manejo de teclado para el modal de pago
            document.addEventListener('keydown', function(e) {
                // Solo si el modal de pago estÃ¡ abierto y no estÃ¡ procesando
                if (!$wire.mostrarModalPago || $wire.procesandoPago) return;

                // Enter para procesar pago
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const efectivo = parseFloat($wire.montoPagoEfectivo || 0);
                    const online = parseFloat($wire.montoPagoOnline || 0);
                    const totalPago = efectivo + online;
                    const creditoPendiente = parseFloat($wire.prestamoAPagar.credito);

                    // Validar que el monto sea vÃ¡lido
                    if (totalPago > 0 && totalPago <= creditoPendiente) {
                        $wire.pagarCredito();
                    }
                }

                // Escape para cerrar
                if (e.key === 'Escape') {
                    e.preventDefault();
                    $wire.cerrarModalPago();
                }
            });

            // Focus inicial al abrir modal
            Livewire.hook('morph.updated', ({ el, component }) => {
                if ($wire.mostrarModalPago && !$wire.procesandoPago) {
                    setTimeout(() => {
                        const input = document.querySelector('#montoPagoEfectivo');
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






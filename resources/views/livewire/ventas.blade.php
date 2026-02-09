<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Ventas</h3>
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
                                    <input type="text" class="form-control text-start" placeholder="Buscar venta..."
                                        wire:model.live="search" style="min-width: 200px;" id="searchInput" autofocus>
                                    <button class="btn btn-primary" wire:click="crearVenta"><i class="fa-solid fa-plus"></i></button>
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
                            <input type="text" class="form-control text-start" placeholder="Buscar venta..."
                                wire:model.live="search" id="searchInput" autofocus>
                            <button class="btn btn-primary" wire:click="crearVenta"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-2">
                        <div class="row g-3">
                            @forelse($ventas as $venta)
                                <div class="col-md-4 col-12">
                                    <div class="card mb-0 shadow-sm {{ $venta->estado === 'Eliminado' ? 'opacity-50' : '' }}">
                                        <div class="card-body compra-card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <!-- Header -->
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h4 class="mb-0 fw-bold">
                                                            Venta #{{ $venta->numero_folio }}
                                                            @if($venta->estado === 'Eliminado')
                                                                <span class="badge bg-danger ms-2">Cancelada</span>
                                                            @endif
                                                        </h4>
                                                        <div class="d-flex gap-1">
                                                            @if ($venta->estado === 'Eliminado')
                                                                <button class="btn btn-sm btn-info"
                                                                    wire:click="verDetalles({{ $venta->id }})"
                                                                    title="Ver detalles">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-secondary"
                                                                    wire:click="generarPDF({{ $venta->id }})"
                                                                    title="Generar PDF">
                                                                    <i class="fa-solid fa-file-pdf"></i>
                                                                </button>
                                                            @elseif ($venta->estado === 'Completo')
                                                                <button class="btn btn-sm btn-info"
                                                                    wire:click="verDetalles({{ $venta->id }})"
                                                                    title="Ver detalles">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-success"
                                                                    wire:click="imprimirTicket({{ $venta->id }})"
                                                                    title="Imprimir ticket">
                                                                    <i class="fa-solid fa-print"></i>
                                                                </button>
                                                                @if ($venta->credito > 0)
                                                                    <button class="btn btn-sm btn-warning"
                                                                        wire:click="abrirModalPago({{ $venta->id }})"
                                                                        title="Cobrar crédito">
                                                                        <i class="fa-solid fa-money-bill"></i>
                                                                    </button>
                                                                @endif
                                                                <button class="btn btn-sm btn-secondary"
                                                                    wire:click="generarPDF({{ $venta->id }})"
                                                                    title="Generar PDF">
                                                                    <i class="fa-solid fa-file-pdf"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger"
                                                                    wire:click="$dispatch('confirm-delete', { id: {{ $venta->id }}, message: '¿Está seguro de eliminar la venta #{{ $venta->numero_folio }}?' })"
                                                                    title="Cancelar">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            @else
                                                                @if($venta->user_id === auth()->id())
                                                                    <a href="{{ route('venta', ['ventaId' => $venta->id]) }}"
                                                                        class="btn btn-sm btn-success"
                                                                        title="Continuar venta">
                                                                        <i class="fa-solid fa-arrow-right"></i>
                                                                    </a>
                                                                @endif
                                                                @if($venta->user_id === auth()->id())
                                                                    <button class="btn btn-sm btn-danger"
                                                                        wire:click="$dispatch('confirm-delete', { id: {{ $venta->id }}, message: '¿Está seguro de eliminar la venta #{{ $venta->numero_folio }}?' })"
                                                                        title="Cancelar">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </button>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Avatar Group de productos -->
                                                    <div class="avatar-group mb-3">
                                                        @foreach ($venta->ventaItems as $item)
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
                                                            $total = $venta->efectivo + $venta->online + $venta->credito;
                                                        @endphp
                                                        <span class="badge bg-primary d-none d-md-inline">Total:
                                                            Bs. {{ number_format($total, 2) }}</span>
                                                        @if ($venta->efectivo > 0)
                                                            <span class="badge bg-success">
                                                                Bs. {{ number_format($venta->efectivo, 2) }}</span>
                                                        @endif
                                                        @if ($venta->online > 0)
                                                            <span class="badge bg-info">
                                                                Bs. {{ number_format($venta->online, 2) }}</span>
                                                        @endif
                                                        @if ($venta->credito > 0)
                                                            <span class="badge bg-danger">
                                                                Bs. {{ number_format($venta->credito, 2) }}</span>
                                                        @endif
                                                    </div>

                                                    <!-- Footer info -->
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-muted">
                                                        <small class="d-none d-md-inline"><i
                                                                class="fa-solid fa-user me-1"></i>{{ $venta->user->name ?? 'Usuario' }}</small>
                                                        <small><i
                                                                class="fa-solid fa-calendar me-1"></i>{{ $venta->created_at->format('d/m/Y H:i') }}</small>
                                                        <small><i
                                                                class="fa-solid fa-user-tie me-1"></i>{{ $venta->cliente->nombre ?? 'Sin cliente' }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-shopping-cart fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No se encontraron ventas</p>
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
                            const saved = localStorage.getItem('paginateVentas') || document.cookie.split('; ').find(row => row.startsWith('paginateVentas='))?.split('=')[1];
                            if (saved) {
                                $wire.set('perPage', parseInt(saved));
                            }
                        }
                    }">
                        <input type="number" class="form-control form-control-sm text-center" style="width: 60px;"
                            wire:model.live="perPage" min="1" max="100" title="Registros por página"
                            onfocus="this.select()"
                            @input="
                                   localStorage.setItem('paginateVentas', $event.target.value);
                                   document.cookie = 'paginateVentas=' + $event.target.value + '; path=/; max-age=31536000';
                               ">
                    </div>
                    {{ $ventas->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal de Detalles de Venta -->
    @if ($mostrarModal && $ventaSeleccionada)
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-shopping-cart me-2"></i>Venta #{{ $ventaSeleccionada->numero_folio }}
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
                                        @if (auth()->user()->canManageCurrentTenant())
                                            <th class="text-end align-middle" style="width: 105px;">Compra</th>
                                        @endif
                                        <th class="text-end align-middle" style="width: 105px;">Precio</th>
                                        @if (auth()->user()->canManageCurrentTenant())
                                            <th class="text-end align-middle" style="width: 105px;">Beneficio</th>
                                        @endif
                                        <th class="text-end align-middle" style="width: 130px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ventaSeleccionada->ventaItems as $item)
                                        <tr style="cursor: pointer;"
                                            x-on:click="$dispatch('mostrarKardex', { productoId: {{ $item->producto_id }} })"
                                            title="{{ $item->producto->nombre ?? 'Producto' }}">
                                            <td class="align-middle" style="max-width: 220px;">
                                                <strong class="d-block text-truncate">{{ $item->producto->nombre ?? 'Producto' }}</strong>
                                            </td>
                                            <td class="text-end align-middle" style="width: 35px;">{{ $item->cantidad_formateada }}</td>
                                            @if (auth()->user()->canManageCurrentTenant())
                                                <td class="text-end align-middle text-truncate" style="max-width: 105px;">Bs. {{ number_format($item->precio_compra, 2) }}</td>
                                            @endif
                                            <td class="text-end align-middle text-truncate" style="max-width: 105px;">Bs. {{ number_format($item->precio, 2) }}</td>
                                            @if (auth()->user()->canManageCurrentTenant())
                                                <td class="text-end align-middle text-truncate" style="max-width: 105px;">Bs. {{ number_format($item->beneficio, 2) }}</td>
                                            @endif
                                            <td class="text-end align-middle text-truncate" style="max-width: 130px;"><strong>Bs. {{ number_format($item->subtotal, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Tarjetas de totales -->
                        @php
                            $totalVenta = $ventaSeleccionada->efectivo + $ventaSeleccionada->online + $ventaSeleccionada->credito;
                            $beneficioTotal = $ventaSeleccionada->ventaItems->sum('beneficio');
                            $isAdmin = auth()->user()->canManageCurrentTenant();

                            // Crear array de tarjetas
                            $tarjetas = [];
                            $tarjetas[] = ['label' => 'Total', 'valor' => $totalVenta, 'color' => ''];
                            if ($isAdmin) {
                                $tarjetas[] = ['label' => 'Beneficio', 'valor' => $beneficioTotal, 'color' => $beneficioTotal >= 0 ? 'text-success' : 'text-danger'];
                            }
                            if ($ventaSeleccionada->efectivo > 0) {
                                $tarjetas[] = ['label' => 'Efectivo', 'valor' => $ventaSeleccionada->efectivo, 'color' => ''];
                            }
                            if ($ventaSeleccionada->online > 0) {
                                $tarjetas[] = ['label' => 'Online', 'valor' => $ventaSeleccionada->online, 'color' => ''];
                            }
                            if ($ventaSeleccionada->credito > 0) {
                                $tarjetas[] = ['label' => 'Crédito', 'valor' => $ventaSeleccionada->credito, 'color' => ''];
                            }

                            $numTarjetas = count($tarjetas);

                            // Distribuir en filas (máximo 4 por fila)
                            // 5: 2+3, 6: 3+3, 7: 3+4
                            if ($numTarjetas <= 4) {
                                $filas = [array_slice($tarjetas, 0, $numTarjetas)];
                            } elseif ($numTarjetas == 5) {
                                $filas = [array_slice($tarjetas, 0, 2), array_slice($tarjetas, 2, 3)];
                            } elseif ($numTarjetas == 6) {
                                $filas = [array_slice($tarjetas, 0, 3), array_slice($tarjetas, 3, 3)];
                            } else {
                                $filas = [array_slice($tarjetas, 0, 3), array_slice($tarjetas, 3, 4)];
                            }
                        @endphp
                        <div class="p-3">
                            @foreach ($filas as $fila)
                                @php
                                    $numEnFila = count($fila);
                                    // Clase de columna según cantidad en la fila (móvil col-6, desktop dinámico)
                                    $colClass = match($numEnFila) {
                                        1 => 'col-6 col-md-12',
                                        2 => 'col-6',
                                        3 => 'col-6 col-md-4',
                                        4 => 'col-6 col-md-3',
                                        default => 'col-6 col-md-3'
                                    };
                                @endphp
                                <div class="row g-2 @if (!$loop->last) mb-2 @endif">
                                    @foreach ($fila as $tarjeta)
                                        <div class="{{ $colClass }}">
                                            <div class="rounded px-3 py-2 text-center h-100 d-flex flex-column justify-content-center" style="background-color: #f0f0f0;">
                                                <small class="text-dark d-block">{{ $tarjeta['label'] }}</small>
                                                <span class="fw-bold fs-5 {{ $tarjeta['color'] ?: 'text-dark' }}">Bs. {{ number_format($tarjeta['valor'], 2) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-user me-1"></i>{{ $ventaSeleccionada->user->name ?? 'Usuario' }}
                            </small>
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-calendar me-1"></i>{{ $ventaSeleccionada->created_at->format('d/m/Y H:i') }}
                            </small>
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-user-tie me-1"></i>{{ $ventaSeleccionada->cliente->nombre ?? 'Sin cliente' }}
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
                            Venta Eliminada #{{ $resumenEliminacion['venta_id'] }}
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
                        <div class="row g-2 justify-content-center w-100">
                            <div class="col-6 col-md-4">
                                <div class="rounded px-3 py-2 text-center h-100 d-flex flex-column justify-content-center" style="background-color: #f0f0f0;">
                                    <small class="text-dark d-block">Retirado de Caja</small>
                                    <span class="fw-bold fs-5 text-danger">Bs. {{ number_format($resumenEliminacion['devuelto_caja'] ?? 0, 2) }}</span>
                                </div>
                            </div>
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

            // ========== IMPRESIÓN DE TICKET DE VENTA ==========
            $wire.on('imprimir-ticket-venta', async (data) => {
                const ticket = data[0] || data;

                // Si no hay impresora configurada, usar navegador
                if (!ticket.impresora) {
                    imprimirTicketNavegador(ticket);
                    return;
                }

                // Intentar imprimir con QZ Tray
                if (typeof qz !== 'undefined') {
                    try {
                        if (!qz.websocket.isActive()) {
                            Swal.fire({
                                title: 'Conectando con QZ Tray...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            await qz.websocket.connect();
                        }

                        Swal.fire({
                            title: 'Imprimiendo...',
                            text: 'Enviando a ' + ticket.impresora,
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        const printerConfig = qz.configs.create(ticket.impresora);
                        const comandos = generarComandosTicketVenta(ticket);

                        await qz.print(printerConfig, [{
                            type: 'raw',
                            format: 'plain',
                            data: comandos
                        }]);

                        Swal.fire({
                            icon: 'success',
                            title: 'Ticket impreso',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        return;

                    } catch (e) {
                        Swal.close();
                        console.error('Error QZ Tray:', e);
                        imprimirTicketNavegador(ticket);
                    }
                } else {
                    imprimirTicketNavegador(ticket);
                }
            });

            // Generar comandos ESC/POS para ticket de venta
            function generarComandosTicketVenta(ticket) {
                const ESC = '\x1B';
                const GS = '\x1D';
                const ancho = ticket.ancho || 48;
                const linea = '='.repeat(ancho);
                const lineaSimple = '-'.repeat(ancho);

                let cmd = '';

                cmd += ESC + '@';
                cmd += ESC + 'a\x01';  // Centrar
                cmd += ESC + 'E\x01';  // Negrita ON
                cmd += (ticket.nombre_tienda || 'MI TIENDA').toUpperCase() + '\n';
                cmd += ESC + 'E\x00';  // Negrita OFF

                if (ticket.direccion) cmd += ticket.direccion + '\n';
                if (ticket.telefono) cmd += 'Tel: ' + ticket.telefono + '\n';
                if (ticket.nit) cmd += 'NIT: ' + ticket.nit + '\n';

                cmd += linea + '\n';
                cmd += ESC + 'E\x01';
                cmd += '**** TICKET DE VENTA ****\n';
                cmd += ESC + 'E\x00';
                cmd += linea + '\n';

                cmd += ESC + 'a\x00';  // Izquierda
                cmd += 'FECHA:   ' + ticket.fecha + '\n';
                cmd += 'USUARIO: ' + ticket.usuario + '\n';
                cmd += 'CLIENTE: ' + ticket.cliente + '\n';
                cmd += 'FOLIO:   #' + ticket.folio + '\n';

                cmd += lineaSimple + '\n';
                cmd += ESC + 'a\x01';
                cmd += 'D E T A L L E\n';
                cmd += lineaSimple + '\n';
                cmd += ESC + 'a\x00';

                ticket.items.forEach(item => {
                    const cantStr = item.cantidad + 'u';
                    const precioStr = item.subtotal.toFixed(2);
                    const nombre = item.nombre.substring(0, ancho - cantStr.length - precioStr.length - 4);
                    const espacios = ancho - cantStr.length - nombre.length - precioStr.length - 1;
                    cmd += cantStr + ' ' + nombre + '.'.repeat(Math.max(1, espacios)) + precioStr + '\n';
                });

                cmd += lineaSimple + '\n';

                const formatTotal = (label, valor) => {
                    const valorStr = valor.toFixed(2);
                    const espacios = ancho - label.length - valorStr.length;
                    return label + ' '.repeat(Math.max(1, espacios)) + valorStr + '\n';
                };

                cmd += ESC + 'E\x01';
                cmd += formatTotal('TOTAL:', ticket.total);
                cmd += ESC + 'E\x00';

                if (ticket.efectivo > 0) cmd += formatTotal('EFECTIVO:', ticket.efectivo);
                if (ticket.online > 0) cmd += formatTotal('ONLINE:', ticket.online);
                if (ticket.credito > 0) cmd += formatTotal('CREDITO:', ticket.credito);
                if (ticket.cambio > 0) cmd += formatTotal('CAMBIO:', ticket.cambio);

                cmd += linea + '\n';
                cmd += ESC + 'a\x01';
                cmd += '\n';
                cmd += 'GRACIAS POR SU COMPRA\n';
                cmd += '\n\n';

                if (ticket.corte) {
                    cmd += GS + 'V\x00';
                }

                if (ticket.abrir_cajon) {
                    cmd += ESC + 'p\x00\x19\xFA';
                }

                return cmd;
            }

            // Fallback: imprimir ticket por navegador
            function imprimirTicketNavegador(ticket) {
                const ancho = ticket.papel === '58mm' ? '58mm' : '80mm';

                let itemsHtml = ticket.items.map(item =>
                    `<div class="item">
                        <span>${item.cantidad}u ${item.nombre}</span>
                        <span>${item.subtotal.toFixed(2)}</span>
                    </div>`
                ).join('');

                const html = `
                    <html>
                    <head>
                        <title>Ticket #${ticket.folio}</title>
                        <style>
                            body { font-family: 'Courier New', monospace; font-size: 12px; margin: 0; padding: 10px; width: ${ancho}; }
                            .center { text-align: center; }
                            .bold { font-weight: bold; }
                            .linea { border-top: 1px dashed #000; margin: 5px 0; }
                            .item { display: flex; justify-content: space-between; }
                            .total-row { display: flex; justify-content: space-between; }
                            @media print { @page { margin: 0; } body { width: 100%; } }
                        </style>
                    </head>
                    <body>
                        <div class="center bold">${ticket.nombre_tienda || 'MI TIENDA'}</div>
                        ${ticket.direccion ? '<div class="center">' + ticket.direccion + '</div>' : ''}
                        ${ticket.telefono ? '<div class="center">Tel: ' + ticket.telefono + '</div>' : ''}
                        <div class="linea"></div>
                        <div class="center bold">TICKET DE VENTA #${ticket.folio}</div>
                        <div class="linea"></div>
                        <div>Fecha: ${ticket.fecha}</div>
                        <div>Usuario: ${ticket.usuario}</div>
                        <div>Cliente: ${ticket.cliente}</div>
                        <div class="linea"></div>
                        <div class="center">DETALLE</div>
                        <div class="linea"></div>
                        ${itemsHtml}
                        <div class="linea"></div>
                        <div class="total-row bold"><span>TOTAL:</span><span>${ticket.total.toFixed(2)}</span></div>
                        ${ticket.efectivo > 0 ? '<div class="total-row"><span>Efectivo:</span><span>' + ticket.efectivo.toFixed(2) + '</span></div>' : ''}
                        ${ticket.online > 0 ? '<div class="total-row"><span>Online:</span><span>' + ticket.online.toFixed(2) + '</span></div>' : ''}
                        ${ticket.credito > 0 ? '<div class="total-row"><span>Crédito:</span><span>' + ticket.credito.toFixed(2) + '</span></div>' : ''}
                        ${ticket.cambio > 0 ? '<div class="total-row"><span>Cambio:</span><span>' + ticket.cambio.toFixed(2) + '</span></div>' : ''}
                        <div class="linea"></div>
                        <div class="center">GRACIAS POR SU COMPRA</div>
                    </body>
                    </html>
                `;

                const printWindow = window.open('', '_blank', 'width=400,height=600');
                printWindow.document.write(html);
                printWindow.document.close();
                printWindow.onload = function() {
                    printWindow.print();
                    printWindow.onafterprint = function() { printWindow.close(); };
                };
            }
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
    {{-- Modal de Pago de Crédito --}}
    @if ($mostrarModalPago && $ventaAPagar)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;"
            x-data="{
                efectivo: {{ $montoPagoEfectivo ?? 0 }},
                online: {{ $montoPagoOnline ?? 0 }},
                creditoTotal: {{ $ventaAPagar->credito }},
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
                            Pagar Crédito
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalPago" {{ $procesandoPago ? 'disabled' : '' }}></button>
                    </div>
                    <div class="modal-body">
                        <!-- Información de la Venta -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Venta:</small>
                                    <strong class="d-block text-dark">#{{ $ventaAPagar->numero_folio }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Cliente:</small>
                                    <strong class="d-block text-truncate text-dark px-2" title="{{ $ventaAPagar->cliente->nombre ?? 'Sin cliente' }}">{{ $ventaAPagar->cliente->nombre ?? 'Sin cliente' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Total Crédito -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Crédito</label>
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

                            <!-- Crédito Restante -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Crédito Restante</label>
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
                        <p class="text-white-50 mb-0">Por favor espere mientras se completa la transacción</p>
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
                // Solo si el modal de pago está abierto y no está procesando
                if (!$wire.mostrarModalPago || $wire.procesandoPago) return;

                // Enter para procesar pago
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const efectivo = parseFloat($wire.montoPagoEfectivo || 0);
                    const online = parseFloat($wire.montoPagoOnline || 0);
                    const totalPago = efectivo + online;
                    const creditoPendiente = parseFloat($wire.ventaAPagar.credito);

                    // Validar que el monto sea válido
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

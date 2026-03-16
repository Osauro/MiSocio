<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Inventarios</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    <input type="text" class="form-control text-start" placeholder="Buscar inventario..."
                                        wire:model.live="search" style="min-width: 200px;" autofocus>
                                    <button class="btn btn-primary" wire:click="crearInventario">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para móvil -->
                    <div class="card-header card-no-border d-md-none" style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="input-group">
                            <input type="text" class="form-control text-start" placeholder="Buscar inventario..."
                                wire:model.live="search">
                            <button class="btn btn-primary" wire:click="crearInventario">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-1 pb-1">
                        <div class="row g-2">
                            @forelse($inventarios as $inventario)
                                <div class="col-md-4 col-12">
                                    <div class="card mb-0 shadow-sm {{ $inventario->estado === 'Eliminado' ? 'opacity-50' : '' }}">
                                        <div class="card-body compra-card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <h4 class="mb-0 fw-bold">
                                                            Inventario #{{ $inventario->numero_folio }}
                                                            @if($inventario->estado === 'Eliminado')
                                                                <span class="badge bg-danger ms-2">Eliminado</span>
                                                            @elseif($inventario->estado === 'Pendiente')
                                                                <span class="badge bg-warning ms-2">Pendiente</span>
                                                            @else
                                                                <span class="badge bg-success ms-2">Completo</span>
                                                            @endif
                                                        </h4>
                                                        <div class="d-flex gap-1">
                                                            @if($inventario->estado === 'Pendiente')
                                                                <a href="{{ route('inventario', ['inventarioId' => $inventario->id]) }}"
                                                                   class="btn btn-sm btn-primary" title="Continuar inventario">
                                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                                </a>
                                                                <button class="btn btn-sm btn-danger"
                                                                    wire:click="eliminar({{ $inventario->id }})"
                                                                    wire:confirm="¿Eliminar este inventario pendiente?"
                                                                    title="Eliminar">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            @else
                                                                <button class="btn btn-sm btn-info"
                                                                    wire:click="verDetalles({{ $inventario->id }})"
                                                                    title="Ver detalles">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Avatar group de productos ajustados -->
                                                    <div class="avatar-group mb-1" style="display:grid; grid-template-columns: repeat(12, auto); column-gap:2px; row-gap:22px; justify-content:start;">
                                                        @foreach($inventario->items->take(24) as $item)
                                                            @php
                                                                $prod     = $item->producto;
                                                                $cantidad = $prod ? ($prod->cantidad ?? 1) : 1;
                                                                $medAbrev = $prod ? strtolower(substr($prod->medida ?? 'u', 0, 1)) : 'u';
                                                                $absDif   = abs($item->diferencia);
                                                                if ($cantidad > 1) {
                                                                    $ent = intdiv($absDif, $cantidad);
                                                                    $uni = $absDif % $cantidad;
                                                                    if ($ent > 0 && $uni > 0) $difBadge = $ent . $medAbrev . '-' . $uni . 'u';
                                                                    elseif ($ent > 0)         $difBadge = $ent . $medAbrev;
                                                                    else                      $difBadge = $uni . 'u';
                                                                } else {
                                                                    $difBadge = $absDif . $medAbrev;
                                                                }
                                                                $badgeColor = $item->diferencia > 0 ? '#198754' : ($item->diferencia < 0 ? '#dc3545' : '#ffc107');
                                                                $difBadge   = ($item->diferencia === 0) ? '-' : $difBadge;
                                                            @endphp
                                                            <div class="avatar" title="{{ $prod->nombre ?? 'Producto' }}">
                                                                <img src="{{ $prod->photo_url ?? '' }}" alt="{{ $prod->nombre ?? '' }}">
                                                                <span class="quantity-badge" style="background-color: {{ $badgeColor }};">{{ $difBadge }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <!-- Badges de totales -->
                                                    @php
                                                        $totalAjuste = $inventario->items->sum(function ($item) {
                                                            $prod = $item->producto;
                                                            if (!$prod) return 0;
                                                            $cantidad = $prod->cantidad ?? 1;
                                                            $abs      = abs($item->diferencia);
                                                            $precio   = $item->diferencia > 0
                                                                ? (float)($prod->precio_de_compra ?? 0)
                                                                : (float)($prod->precio_por_mayor ?? 0);
                                                            $total    = ($cantidad > 1 ? ($abs / $cantidad) : $abs) * $precio;
                                                            return $item->diferencia < 0 ? -$total : $total;
                                                        });
                                                    @endphp
                                                    <div class="d-flex gap-2 flex-wrap mb-1">
                                                        <span class="badge bg-primary d-none d-md-inline">
                                                            Total: {{ $inventario->items_count }} productos
                                                        </span>
                                                        @if($inventario->items->count() > 0)
                                                            <span class="badge {{ $totalAjuste >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                Bs. {{ number_format($totalAjuste, 2) }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Footer info -->
                                                    <div class="d-flex justify-content-between align-items-center text-muted">
                                                        <small class="d-none d-md-inline"><i class="fa-solid fa-user me-1"></i>{{ $inventario->user->name ?? 'N/A' }}</small>
                                                        <small><i class="fa-solid fa-calendar me-1"></i>{{ $inventario->created_at->format('d/m/Y H:i') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa-solid fa-boxes-stacked fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay inventarios registrados</p>
                                    <button class="btn btn-primary" wire:click="crearInventario">
                                        <i class="fa-solid fa-plus me-1"></i> Crear nuevo inventario
                                    </button>
                                </div>
                            @endforelse
                        </div>

                        <!-- Paginación -->
                        @if($inventarios->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $inventarios->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de detalles -->
    @if($mostrarModal && !empty($modalData))
        @php
            $totalItems   = count($modalAllItems);
            $totalPages   = $modalPerPage > 0 ? (int)ceil($totalItems / $modalPerPage) : 1;
            $offset       = ($modalPage - 1) * $modalPerPage;
            $itemsPagina  = array_slice($modalAllItems, $offset, $modalPerPage);
            $totalGeneral = array_sum(array_column($modalAllItems, 'total'));
        @endphp

        <!-- Backdrop -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;" wire:click.self="cerrarModal">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">

                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-boxes-stacked me-2"></i>Inventario #{{ $modalData['folio'] }}
                            @if($modalData['estado'] === 'Completo')
                                <span class="badge bg-success ms-2">Completo</span>
                            @else
                                <span class="badge bg-danger ms-2">Eliminado</span>
                            @endif
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="align-middle">Producto</th>
                                        <th class="text-end align-middle">Anterior</th>
                                        <th class="text-end align-middle">Diferencia</th>
                                        <th class="text-end align-middle">Actual</th>
                                        <th class="text-end align-middle">Precio</th>
                                        <th class="text-end align-middle">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemsPagina as $item)
                                        <tr>
                                            <td class="align-middle" style="max-width:220px;">
                                                <strong class="d-block text-truncate">{{ $item['nombre'] }}</strong>
                                            </td>
                                            <td class="text-end align-middle">{{ $item['sys_display'] }}</td>
                                            <td class="text-end align-middle">
                                                @if($item['diferencia'] > 0)
                                                    <span class="badge bg-success">{{ $item['dif_display'] }}</span>
                                                @elseif($item['diferencia'] < 0)
                                                    <span class="badge bg-danger">{{ $item['dif_display'] }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $item['dif_display'] }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end align-middle">{{ $item['cnt_display'] }}</td>
                                            <td class="text-end align-middle">
                                                @if($item['diferencia'] != 0)
                                                    {{ number_format($item['precio'], 2) }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end align-middle">
                                                @if($item['diferencia'] != 0)
                                                    <strong class="{{ $item['diferencia'] > 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format(abs($item['total']), 2) }}
                                                    </strong>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Total general -->
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="rounded px-3 py-2 text-center" style="background-color: {{ $totalGeneral >= 0 ? '#198754' : '#dc3545' }};">
                                        <small class="text-white d-block">Total ajuste</small>
                                        <span class="fw-bold fs-5 text-white">Bs. {{ number_format($totalGeneral, 2) }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="rounded px-3 py-2 text-center" style="background-color: var(--theme-default, #7366ff);">
                                        <small class="text-white d-block">Productos ajustados</small>
                                        <span class="fw-bold fs-5 text-white">{{ count(array_filter($modalAllItems, fn($i) => $i['diferencia'] != 0)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2">
                        <div class="d-flex justify-content-between align-items-center w-100 gap-2">
                            <div class="row g-2 flex-grow-1">
                                <div class="col-6">
                                    <div class="rounded px-2 py-1 text-center" style="background-color: #f0f0f0;">
                                        <small class="text-muted d-block" style="font-size:0.7rem;"><i class="fa-solid fa-user me-1"></i>Responsable</small>
                                        <span class="fw-bold text-dark" style="font-size:0.8rem;">{{ $modalData['user'] }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="rounded px-2 py-1 text-center" style="background-color: #f0f0f0;">
                                        <small class="text-muted d-block" style="font-size:0.7rem;"><i class="fa-solid fa-calendar me-1"></i>Fecha</small>
                                        <span class="fw-bold text-dark" style="font-size:0.8rem;">{{ $modalData['fecha'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0 align-items-center">
                                <button class="btn btn-sm btn-outline-secondary"
                                    wire:click="$set('modalPage', {{ max(1, $modalPage - 1) }})"
                                    @disabled($modalPage <= 1)>
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <span class="small text-dark">{{ $modalPage }}/{{ $totalPages }}</span>
                                <button class="btn btn-sm btn-outline-secondary"
                                    wire:click="$set('modalPage', {{ min($totalPages, $modalPage + 1) }})"
                                    @disabled($modalPage >= $totalPages)>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>

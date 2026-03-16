<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="mb-0 d-none d-md-block">Inventarios</h3>
                            <div class="d-flex gap-2 align-items-center">
                                <button class="btn btn-primary" wire:click="crearInventario" title="Nuevo inventario">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-2 pb-1">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead style="background-color: var(--theme-default, #7366ff); color: #fff;">
                                    <tr>
                                        <th style="width:1%; white-space:nowrap;">ID</th>
                                        <th style="width:1%; white-space:nowrap;">Fecha</th>
                                        <th style="width:1%; white-space:nowrap;">Hora</th>
                                        <th class="d-none d-md-table-cell">Usuario</th>
                                        <th class="text-end" style="width:1%; white-space:nowrap;">Sobra</th>
                                        <th class="text-end d-none d-md-table-cell" style="width:1%; white-space:nowrap;">Sobrante</th>
                                        <th class="text-end" style="width:1%; white-space:nowrap;">Falta</th>
                                        <th class="text-end d-none d-md-table-cell" style="width:1%; white-space:nowrap;">Faltante</th>
                                        <th class="text-end" style="width:1%; white-space:nowrap;">Total</th>
                                        <th style="width:10px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inventarios as $inv)
                                        @php
                                            $sobra = 0; $sobrante = 0.0;
                                            $falta = 0; $faltante = 0.0;
                                            foreach ($inv->items as $item) {
                                                $prod = $item->producto;
                                                if (!$prod) continue;
                                                $cantidad = $prod->cantidad ?? 1;
                                                $abs      = abs($item->diferencia);
                                                $unidades = $cantidad > 1 ? ($abs / $cantidad) : $abs;
                                                if ($item->diferencia > 0) {
                                                    $sobra    += $item->diferencia;
                                                    $sobrante += $unidades * (float)($prod->precio_de_compra ?? 0);
                                                } elseif ($item->diferencia < 0) {
                                                    $falta    += $abs;
                                                    $faltante += $unidades * (float)($prod->precio_por_mayor ?? 0);
                                                }
                                            }
                                            $total = $sobrante - $faltante;
                                        @endphp
                                        <tr class="{{ $inv->estado === 'Eliminado' ? 'opacity-50' : '' }}">
                                            <td style="white-space:nowrap;">
                                                <span class="fw-bold">{{ str_pad($inv->numero_folio, 6, '0', STR_PAD_LEFT) }}</span>
                                                @if($inv->estado === 'Pendiente')
                                                    <span class="badge bg-warning ms-1">Pendiente</span>
                                                @elseif($inv->estado === 'Eliminado')
                                                    <span class="badge bg-danger ms-1">Eliminado</span>
                                                @endif
                                            </td>
                                            <td style="white-space:nowrap;">{{ $inv->created_at->format('d/m/Y') }}</td>
                                            <td style="white-space:nowrap;">{{ $inv->created_at->format('H:i') }}</td>
                                            <td class="d-none d-md-table-cell">{{ $inv->user->name ?? 'N/A' }}</td>
                                            <td class="text-end text-success fw-bold" style="white-space:nowrap;">{{ $sobra > 0 ? $sobra : '-' }}</td>
                                            <td class="text-end text-success d-none d-md-table-cell" style="white-space:nowrap;">{{ $sobrante > 0 ? number_format($sobrante, 2) : '-' }}</td>
                                            <td class="text-end text-danger fw-bold" style="white-space:nowrap;">{{ $falta > 0 ? $falta : '-' }}</td>
                                            <td class="text-end text-danger d-none d-md-table-cell" style="white-space:nowrap;">{{ $faltante > 0 ? number_format($faltante, 2) : '-' }}</td>
                                            <td class="text-end fw-bold {{ $total > 0 ? 'text-success' : ($total < 0 ? 'text-danger' : 'text-muted') }}" style="white-space:nowrap;">
                                                {{ ($sobra > 0 || $falta > 0) ? number_format($total, 2) : '-' }}
                                            </td>
                                            <td style="width:10px; white-space:nowrap;">
                                                @if($inv->estado === 'Pendiente')
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('inventario', ['inventarioId' => $inv->id]) }}"
                                                           class="btn btn-sm btn-primary" title="Continuar inventario">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-danger"
                                                            wire:click="eliminar({{ $inv->id }})"
                                                            wire:confirm="¿Eliminar este inventario pendiente?"
                                                            title="Eliminar">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="d-flex gap-1">
                                                        <button class="btn btn-sm btn-info"
                                                            wire:click="verDetalles({{ $inv->id }})"
                                                            title="Ver detalles">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </button>
                                                        <a href="{{ route('inventario.pdf', $inv->id) }}"
                                                           target="_blank"
                                                           class="btn btn-sm btn-danger" title="Descargar PDF">
                                                            <i class="fa-solid fa-file-pdf"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-5 text-muted">
                                                <i class="fa-solid fa-boxes-stacked fa-3x mb-3 d-block"></i>
                                                No hay inventarios registrados
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.paginate-bar', ['results' => $inventarios, 'storageKey' => 'inventarios'])

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

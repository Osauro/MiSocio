<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Kardex</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    @if($fecha_inicio && $fecha_fin)
                                        <button type="button" class="btn btn-outline-danger" wire:click="limpiarFiltroFechas" title="Limpiar filtro de fechas">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterDateModalKardex" title="Filtrar por fechas">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </button>
                                    @endif
                                    <input type="text" class="form-control" placeholder="Buscar en kardex"
                                        wire:model.live="search" style="min-width: 200px;" autofocus>
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
                                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterDateModalKardex" title="Filtrar por fechas">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </button>
                            @endif
                            <input type="text" class="form-control" placeholder="Buscar en kardex"
                                wire:model.live="search" autofocus>
                        </div>
                    </div>

                    <div class="card-body pt-3">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px"></th>
                                        <th style="width: 100px">Fecha</th>
                                        <th>Producto</th>
                                        <th style="width: 100px" class="text-end">Anterior</th>
                                        <th style="width: 100px" class="text-end">Ent/Sal</th>
                                        <th style="width: 100px" class="text-end">Saldo</th>
                                        <th style="width: 100px" class="text-end">Precio</th>
                                        <th style="width: 100px" class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kardex as $item)
                                        <tr wire:click="filtrarPorProducto('{{ $item->producto->nombre }}')"
                                            style="cursor: pointer;">
                                            <td>
                                                <img src="{{ $item->producto->photo_url }}"
                                                    alt="{{ $item->producto->nombre }}" class="rounded"
                                                    style="width: 64px; height: 64px; object-fit: cover;">
                                            </td>
                                            <td class="text-center text-truncate">
                                                {{ $item->created_at->format('d/m/Y') }}
                                                <br><span
                                                    class="text-muted fs-4">{{ $item->created_at->format('H:i') }}</span>
                                            </td>
                                            <td class="text-truncate">
                                                <strong>{{ $item->producto->nombre }}</strong>
                                                @if ($item->producto->codigo)
                                                    <br><small class="text-muted">{{ $item->producto->codigo }}</small>
                                                @endif
                                                @if ($item->obs)
                                                    <br><small class="text-muted">{{ $item->obs }}</small>
                                                @endif
                                            </td>
                                            <td class="text-end text-truncate">{{ $item->anterior_formateado }}</td>
                                            <td class="text-end text-truncate">
                                                @if ($item->entrada > 0)
                                                    <span
                                                        class="text-success fw-bold">{{ $item->movimiento_formateado }}</span>
                                                @elseif($item->salida > 0)
                                                    <span
                                                        class="text-danger fw-bold">{{ $item->movimiento_formateado }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-truncate">
                                                <strong>{{ $item->saldo_formateado }}</strong>
                                            </td>
                                            <td class="text-end text-truncate">
                                                @if($item->entrada > 0 && !canManageTenant())
                                                    ***
                                                @else
                                                    {{ number_format($item->precio, 2) }}
                                                @endif
                                            </td>
                                            <td class="text-end text-truncate">
                                                @if($item->entrada > 0 && !canManageTenant())
                                                    ***
                                                @else
                                                    <strong>{{ number_format($item->total, 2) }}</strong>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                    @endforelse
                                </tbody>
                            </table>
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
                             const saved = localStorage.getItem('paginateKardex') || document.cookie.split('; ').find(row => row.startsWith('paginateKardex='))?.split('=')[1];
                             if (saved) {
                                 $wire.set('perPage', parseInt(saved));
                             }
                         }
                     }">
                        <input type="number"
                               class="form-control form-control-sm text-center"
                               style="width: 60px;"
                               wire:model.live="perPage"
                               min="1"
                               max="100"
                               title="Registros por página"
                               onfocus="this.select()"
                               @input="
                                   localStorage.setItem('paginateKardex', $event.target.value);
                                   document.cookie = 'paginateKardex=' + $event.target.value + '; path=/; max-age=31536000';
                               ">
                    </div>
                    {{ $kardex->links() }}
                </div>
            </div>
        </div>
    </footer>

    <style>
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.85rem;
        }

        .badge {
            font-size: 0.8rem;
        }
    </style>

    <!-- Modal de Filtro de Fechas -->
    <div class="modal fade" id="filterDateModalKardex" tabindex="-1" aria-labelledby="filterDateModalKardexLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterDateModalKardexLabel">Filtrar por Fechas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

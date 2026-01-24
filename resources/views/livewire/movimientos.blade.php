<div>
    <div class="container-fluid">
        <div class="row starter-main">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header card-no-border pb-0">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Movimientos</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    @if($fecha_inicio && $fecha_fin)
                                        <button type="button" class="btn btn-outline-danger" wire:click="limpiarFiltroFechas" title="Limpiar filtro de fechas">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterDateModal" title="Filtrar por fechas">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </button>
                                    @endif
                                    <input type="text" class="form-control" placeholder="Buscar movimientos"
                                        wire:model.live="search" style="min-width: 200px;" autofocus>
                                    @if(canManageTenant())
                                        <button class="btn btn-primary" wire:click="create"><i class="fa-solid fa-plus"></i></button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-3">
                        <!-- Resumen de saldos -->
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2">
                                <div class="card shadow-sm border-0"
                                    style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                    <div class="card-body py-3 text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-1 opacity-75" style="font-size: 0.85rem;">Total Ingresos
                                                </p>
                                                <h3 class="mb-0 fw-bold">Bs. {{ number_format($totalIngresos, 2) }}</h3>
                                            </div>
                                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                                <i class="fa-solid fa-arrow-trend-up fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="card shadow-sm border-0"
                                    style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                                    <div class="card-body py-3 text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-1 opacity-75" style="font-size: 0.85rem;">Total Egresos</p>
                                                <h3 class="mb-0 fw-bold">Bs. {{ number_format($totalEgresos, 2) }}</h3>
                                            </div>
                                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                                <i class="fa-solid fa-arrow-trend-down fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="card shadow-sm border-0"
                                    style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                                    <div class="card-body py-3 text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-1 opacity-75" style="font-size: 0.85rem;">Saldo Actual</p>
                                                <h3 class="mb-0 fw-bold">Bs. {{ number_format($saldoActual, 2) }}</h3>
                                            </div>
                                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                                <i class="fa-solid fa-wallet fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de movimientos -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 100px;">Fecha</th>
                                        <th>Detalle</th>
                                        <th class="text-end" style="width: 150px;">Monto</th>
                                        <th class="text-end" style="width: 150px;">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movimientos as $movimiento)
                                        <tr>
                                            <td class="text-center">
                                                <div class="fw-semibold">{{ $movimiento->created_at->format('d/m/Y') }}
                                                </div>
                                                <small
                                                    class="text-muted">{{ $movimiento->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <small class="text-muted d-block">{{ $movimiento->user->name }}</small>
                                                <span>{{ $movimiento->detalle }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if ($movimiento->ingreso > 0)
                                                    <span class="text-success fw-semibold">
                                                        + Bs. {{ number_format($movimiento->ingreso, 2) }}
                                                    </span>
                                                @else
                                                    <span class="text-danger fw-semibold">
                                                        - Bs. {{ number_format($movimiento->egreso, 2) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold">Bs.
                                                    {{ number_format($movimiento->saldo, 2) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <i
                                                    class="fa-solid fa-file-invoice-dollar fa-3x text-muted mb-3 d-block"></i>
                                                <p class="text-muted mb-0">No hay movimientos registrados</p>
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

    <!-- Footer fijo con paginado -->
    <footer class="fixed-footer shadow-sm py-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted d-none d-md-block">Created By <a href="https://dieguitosoft.com"
                        target="_blank">DieguitoSoft.com</a></small>
                <div class="d-flex align-items-center gap-2">
                    <div x-data="{
                        init() {
                            const saved = localStorage.getItem('paginateMovimientos') || document.cookie.split('; ').find(row => row.startsWith('paginateMovimientos='))?.split('=')[1];
                            if (saved) {
                                $wire.set('perPage', parseInt(saved));
                            }
                        }
                    }">
                        <input type="number" class="form-control form-control-sm text-center" style="width: 60px;"
                            wire:model.live="perPage" min="1" max="100" title="Registros por página"
                            onfocus="this.select()"
                            @input="
                                   localStorage.setItem('paginateMovimientos', $event.target.value);
                                   document.cookie = 'paginateMovimientos=' + $event.target.value + '; path=/; max-age=31536000';
                               ">
                    </div>
                    {{ $movimientos->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal para Registrar Movimiento -->
    <div wire:ignore.self class="modal fade" id="crudModal" tabindex="-1" role="dialog"
        aria-labelledby="modalcrud">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Movimiento</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo de Movimiento</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="tipo" value="ingreso"
                                        id="tipoIngreso">
                                    <label class="form-check-label" for="tipoIngreso">
                                        <i class="fa-solid fa-arrow-up text-success"></i> Ingreso
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="tipo" value="egreso"
                                        id="tipoEgreso">
                                    <label class="form-check-label" for="tipoEgreso">
                                        <i class="fa-solid fa-arrow-down text-danger"></i> Egreso
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Detalle</label>
                            <input type="text" class="form-control @error('detalle') is-invalid @enderror"
                                wire:model="detalle" placeholder="Descripción del movimiento">
                            @error('detalle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Monto (Bs.)</label>
                            <input type="number" step="0.01"
                                class="form-control @error('monto') is-invalid @enderror" wire:model="monto"
                                placeholder="0.00">
                            @error('monto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary"
                                wire:click="closeModal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Filtro de Fechas -->
    <div class="modal fade" id="filterDateModal" tabindex="-1" aria-labelledby="filterDateModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterDateModalLabel">Filtrar por Fechas</h5>
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

@script
    <script>
        $wire.on('showmodal', () => {
            $('#crudModal').modal('show');
        });

        $wire.on('closemodal', () => {
            $('#crudModal').modal('hide');
        });
    </script>
@endscript

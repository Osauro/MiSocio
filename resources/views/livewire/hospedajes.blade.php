<div>
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-clipboard-list me-2 text-primary"></i>Historial de Hospedajes</h4>
        </div>
        <a href="{{ route('habitaciones') }}" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-door-open me-1"></i>Panel de Habitaciones
        </a>
    </div>

    <!-- Filtros -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-5">
            <input type="text" class="form-control form-control-sm" wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por huésped o folio...">
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select form-select-sm" wire:model.live="filtroEstado">
                <option value="">Todos los estados</option>
                <option value="activo">Activo</option>
                <option value="finalizado">Finalizado</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="small">Folio</th>
                        <th class="small">Huésped</th>
                        <th class="small">Habitación(es)</th>
                        <th class="small">Entrada</th>
                        <th class="small">Salida</th>
                        <th class="small">Total</th>
                        <th class="small">Estado</th>
                        <th class="small text-center">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hospedajes as $hosp)
                    <tr>
                        <td class="fw-semibold small">#{{ $hosp->numero_folio }}</td>
                        <td class="small">{{ $hosp->cliente?->nombre ?? '—' }}</td>
                        <td class="small">
                            @foreach($hosp->habitaciones as $hh)
                                <span class="badge bg-secondary">{{ $hh->habitacion?->numero }}</span>
                            @endforeach
                        </td>
                        <td class="small">{{ $hosp->fecha_entrada?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="small">{{ $hosp->fecha_salida_real?->format('d/m/Y H:i') ?? ($hosp->fecha_salida_estimada?->format('d/m/Y H:i') ?? '—') }}</td>
                        <td class="fw-semibold small text-success">Bs. {{ number_format($hosp->total, 2) }}</td>
                        <td>
                            @if($hosp->estado === 'activo')
                                <span class="badge bg-danger">Activo</span>
                            @elseif($hosp->estado === 'finalizado')
                                <span class="badge bg-success">Finalizado</span>
                            @else
                                <span class="badge bg-secondary">Cancelado</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-xs btn-outline-primary" wire:click="verDetalles({{ $hosp->id }})">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                            No hay hospedajes registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $hospedajes->links() }}</div>

    <!-- ═══════════════════════ MODAL DETALLES ═══════════════════════ -->
    @if($mostrarModal && $hospedajeSeleccionado)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-hotel me-2"></i>
                        Folio #{{ $hospedajeSeleccionado->numero_folio }}
                        — {{ $hospedajeSeleccionado->cliente?->nombre ?? 'Sin cliente' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-1 small"><strong>Registrado por:</strong> {{ $hospedajeSeleccionado->user?->name }}</p>
                            <p class="mb-1 small"><strong>Entrada:</strong> {{ $hospedajeSeleccionado->fecha_entrada?->format('d/m/Y H:i') }}</p>
                            <p class="mb-1 small"><strong>Salida:</strong> {{ $hospedajeSeleccionado->fecha_salida_real?->format('d/m/Y H:i') ?? 'En curso' }}</p>
                            <p class="mb-1 small"><strong>Personas:</strong> {{ $hospedajeSeleccionado->numero_personas }}</p>
                            @if($hospedajeSeleccionado->observaciones)
                            <p class="mb-1 small"><strong>Obs:</strong> {{ $hospedajeSeleccionado->observaciones }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 small"><strong>Efectivo:</strong> Bs. {{ number_format($hospedajeSeleccionado->efectivo, 2) }}</p>
                            <p class="mb-1 small"><strong>QR/Online:</strong> Bs. {{ number_format($hospedajeSeleccionado->online, 2) }}</p>
                            <p class="mb-1 small"><strong>Crédito:</strong> Bs. {{ number_format($hospedajeSeleccionado->credito, 2) }}</p>
                            <p class="mb-0 fw-bold text-success"><strong>TOTAL:</strong> Bs. {{ number_format($hospedajeSeleccionado->total, 2) }}</p>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-semibold"><i class="fa-solid fa-door-open me-1"></i>Habitaciones</h6>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">Habitación</th>
                                <th class="small">Modalidad</th>
                                <th class="small">Unidades</th>
                                <th class="small">Precio unit.</th>
                                <th class="small">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hospedajeSeleccionado->habitaciones as $hh)
                            <tr>
                                <td class="small">{{ $hh->habitacion?->numero }} — {{ $hh->habitacion?->tipoHabitacion?->nombre }}</td>
                                <td class="small text-capitalize">{{ $hh->modalidad }}</td>
                                <td class="small">{{ $hh->unidades }}</td>
                                <td class="small">Bs. {{ number_format($hh->precio_unitario, 2) }}</td>
                                <td class="small fw-semibold">Bs. {{ number_format($hh->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="cerrarModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
.btn-xs { padding: .2rem .5rem; font-size: .75rem; }
</style>
</div>

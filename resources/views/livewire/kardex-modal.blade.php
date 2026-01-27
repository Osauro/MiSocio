<div x-data="{
    init() {
        const saved = localStorage.getItem('paginateKardexModal') || document.cookie.split('; ').find(row => row.startsWith('paginateKardexModal='))?.split('=')[1];
        if (saved && parseInt(saved) !== $wire.perPage) {
            $wire.set('perPage', parseInt(saved));
        }
    }
}">
    <!-- Modal de Kardex -->
    @if ($mostrar && $producto)
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;" wire:click.self="cerrarKardex">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>
                            {{ $producto->nombre }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                            wire:click="cerrarKardex" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-0">
                        @if($movimientos->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 100px" class="align-middle">Fecha</th>
                                            <th class="align-middle">Observación</th>
                                            <th style="width: 100px" class="text-end align-middle">Anterior</th>
                                            <th style="width: 100px" class="text-end align-middle">Ent/Sal</th>
                                            <th style="width: 100px" class="text-end align-middle">Saldo</th>
                                            <th style="width: 100px" class="text-end align-middle">Precio</th>
                                            <th style="width: 100px" class="text-end align-middle">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($movimientos as $movimiento)
                                            <tr>
                                                <td class="text-center align-middle text-truncate">
                                                    {{ $movimiento->created_at->format('d/m/Y') }}
                                                    <br><span class="text-muted small">{{ $movimiento->created_at->format('H:i') }}</span>
                                                </td>
                                                <td class="align-middle text-truncate">
                                                    {{ $movimiento->obs ?? '-' }}
                                                    @if($movimiento->user)
                                                        <br><small class="text-muted"><i class="fa-solid fa-user me-1"></i>{{ $movimiento->user->name }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-end align-middle text-truncate">{{ $movimiento->anterior_formateado }}</td>
                                                <td class="text-end align-middle text-truncate">
                                                    @if ($movimiento->entrada > 0)
                                                        <span class="text-success fw-bold">{{ $movimiento->movimiento_formateado }}</span>
                                                    @elseif($movimiento->salida > 0)
                                                        <span class="text-danger fw-bold">{{ $movimiento->movimiento_formateado }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-end align-middle text-truncate">
                                                    <strong>{{ $movimiento->saldo_formateado }}</strong>
                                                </td>
                                                <td class="text-end align-middle text-truncate">
                                                    {{ number_format($movimiento->precio, 2) }}
                                                </td>
                                                <td class="text-end align-middle text-truncate">
                                                    <strong>{{ number_format($movimiento->total, 2) }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-5 text-center text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 opacity-50"></i>
                                <h5 class="mb-2">No hay movimientos</h5>
                                <p class="mb-0">No hay movimientos registrados para este producto</p>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                Mostrando
                            </small>
                            <input type="number"
                                   class="form-control form-control-sm text-center"
                                   style="width: 60px;"
                                   wire:model.live="perPage"
                                   min="1"
                                   max="100"
                                   title="Registros por página"
                                   onfocus="this.select()"
                                   @input="
                                       localStorage.setItem('paginateKardexModal', $event.target.value);
                                       document.cookie = 'paginateKardexModal=' + $event.target.value + '; path=/; max-age=31536000';
                                   ">
                            <small class="text-muted">movimientos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

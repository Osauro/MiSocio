<div>
    <div class="container-fluid">
        <div class="row starter-main">
            <div class="col-sm-12">
                <div class="card">

                    <!-- Header escritorio -->
                    <div class="card-header card-no-border pb-0 d-none d-md-block"
                        style="position: sticky; top: 0; z-index: 1050; background-color: white;">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Inventario #{{ $inventarioFolio }}</h3>
                            <div class="d-flex gap-2 align-items-center">
                                <button class="btn btn-secondary" title="Cancelar inventario"
                                    onclick="swalCancelarInventario({{ $inventarioId }}, '{{ $inventarioFolio }}')"
                                    wire:ignore>
                                    <i class="fa-solid fa-times"></i>
                                </button>
                                @if(count($items) > 0)
                                    <button class="btn btn-success"
                                        onclick="swalFinalizarInventario({{ $inventarioId }}, '{{ $inventarioFolio }}', {{ count($items) }})"
                                        wire:ignore>
                                        <i class="fa-solid fa-check me-1"></i>
                                        <span class="d-none d-md-inline">Finalizar</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Header movil -->
                    <div class="card-header card-no-border d-md-none"
                        style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="d-flex gap-2 align-items-center">
                            <button class="btn btn-secondary btn-sm flex-shrink-0" title="Cancelar inventario"
                                onclick="swalCancelarInventario({{ $inventarioId }}, '{{ $inventarioFolio }}')"
                                wire:ignore>
                                <i class="fa-solid fa-times"></i>
                            </button>
                            @if(count($items) > 0)
                                <button class="btn btn-success btn-sm flex-shrink-0"
                                    onclick="swalFinalizarInventario({{ $inventarioId }}, '{{ $inventarioFolio }}', {{ count($items) }})"
                                    wire:ignore>
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Grilla de productos -->
                    <div class="card-body pt-2 pb-2">
                        <div class="row g-2">
                            @forelse($items as $item)
                                @php
                                    $medAbrev = strtolower(substr($item['medida'], 0, 1));
                                    $cantPor  = $item['cantidad_por_medida'];
                                    if ($cantPor > 1) {
                                        $sysDisplay = $item['sys_ent'] . $medAbrev . '-' . $item['sys_uni'] . 'u';
                                    } else {
                                        $sysDisplay = $item['stock_sistema'] . $medAbrev;
                                    }
                                @endphp

                                <div class="col-12 col-md-4 col-lg-3" wire:key="inv-item-{{ $item['id'] }}"
                                     x-data="{
                                         ent: {{ (int)$item['cnt_ent'] }},
                                         uni: {{ (int)$item['cnt_uni'] }},
                                         can: {{ (int)$cantPor }},
                                         med: '{{ $medAbrev }}',
                                         sys: {{ (int)$item['stock_sistema'] }},
                                         contado: {{ $item['contado'] ? 'true' : 'false' }},
                                         get diff() {
                                             let cnt = this.can > 1 ? (this.ent * this.can + this.uni) : this.ent;
                                             return cnt - this.sys;
                                         },
                                         get difDisplay() {
                                             if (!this.contado) return '-';
                                             if (this.diff === 0) return '=';
                                             let abs = Math.abs(this.diff);
                                             if (this.can > 1) {
                                                 let e = Math.floor(abs / this.can);
                                                 let u = abs % this.can;
                                                 if (e > 0 && u > 0) return e + this.med + '-' + u + 'u';
                                                 if (e > 0) return e + this.med;
                                                 return u + 'u';
                                             }
                                             return abs + this.med;
                                         },
                                         get difBg() {
                                             if (!this.contado) return '#f0ad4e';
                                             if (this.diff === 0) return '#6c757d';
                                             return this.diff > 0 ? '#198754' : '#dc3545';
                                         },
                                         update() {
                                             if (this.can > 1 && this.uni >= this.can) {
                                                 this.ent += Math.floor(this.uni / this.can);
                                                 this.uni = this.uni % this.can;
                                             }
                                             this.contado = true;
                                             $wire.actualizarEntUni({{ $item['id'] }}, this.ent, this.uni);
                                         }
                                     }">
                                    <div class="card mb-0 shadow-sm">
                                        <div class="card-body p-2">

                                            <!-- Fila 1: Nombre -->
                                            <div class="mb-1">
                                                <span class="fw-bold text-truncate d-block"
                                                      style="font-size: 0.78rem;"
                                                      title="{{ $item['nombre'] }}">
                                                    {{ $item['nombre'] }}
                                                </span>
                                            </div>

                                            <!-- Filas 2-3: Imagen izquierda + controles derecha -->
                                            <div class="d-flex gap-2">

                                                <!-- Imagen / Avatar -->
                                                <div class="flex-shrink-0 rounded overflow-hidden"
                                                     style="width: 64px; align-self: stretch;">
                                                    @if($item['imagen'])
                                                        <img src="{{ $item['imagen'] }}"
                                                             alt="{{ $item['nombre'] }}"
                                                             style="width:100%; height:100%; object-fit:cover; display:block;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center fw-bold text-white w-100 h-100"
                                                             style="background: var(--theme-default, #7366ff); font-size: 1.3rem; letter-spacing: -1px;">
                                                            {{ strtoupper(substr($item['nombre'], 0, 2)) }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Columna derecha -->
                                                <div class="flex-grow-1 d-flex flex-column gap-1" style="min-width: 0;">

                                                    <!-- Fila 2: inputs -->
                                                    @if($cantPor > 1)
                                                        <div class="d-flex gap-1">
                                                            <div class="input-group input-group-sm flex-fill">
                                                                <span class="input-group-text px-1 justify-content-center"
                                                                      style="min-width:26px;" title="Enteros">
                                                                    <i class="fa-solid fa-box" style="font-size:0.65rem;"></i>
                                                                </span>
                                                                <input type="number" min="0"
                                                                    class="form-control form-control-sm text-center"
                                                                    style="font-size:0.8rem; padding:2px; min-width:0;"
                                                                    x-model.number="ent"
                                                                    @change="update()"
                                                                    onclick="this.select()"
                                                                    placeholder="0">
                                                            </div>
                                                            <div class="input-group input-group-sm flex-fill">
                                                                <span class="input-group-text px-1 justify-content-center"
                                                                      style="min-width:26px;" title="Unidades">
                                                                    <i class="fa-solid fa-cube" style="font-size:0.65rem;"></i>
                                                                </span>
                                                                <input type="number" min="0"
                                                                    class="form-control form-control-sm text-center"
                                                                    style="font-size:0.8rem; padding:2px; min-width:0;"
                                                                    x-model.number="uni"
                                                                    @change="update()"
                                                                    onclick="this.select()"
                                                                    placeholder="0">
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text px-1 justify-content-center"
                                                                  style="min-width:26px;" title="Unidades">
                                                                <i class="fa-solid fa-cube" style="font-size:0.65rem;"></i>
                                                            </span>
                                                            <input type="number" min="0"
                                                                class="form-control form-control-sm text-center"
                                                                style="font-size:0.8rem; padding:2px;"
                                                                x-model.number="ent"
                                                                @change="update()"
                                                                onclick="this.select()"
                                                                placeholder="0">
                                                        </div>
                                                    @endif

                                                    <!-- Fila 3: Stock sistema + Diferencia -->
                                                    <div class="d-flex gap-1">
                                                        <input type="text" readonly
                                                            class="form-control form-control-sm text-center fw-bold flex-fill"
                                                            style="font-size:0.78rem; padding:2px; min-width:0; background:var(--theme-default,#7366ff); color:#fff; border:none; cursor:default;"
                                                            value="{{ $sysDisplay }}"
                                                            title="Stock en sistema">
                                                        <input type="text" readonly
                                                            class="form-control form-control-sm text-center fw-bold flex-fill"
                                                            :style="'font-size:0.78rem; padding:2px; min-width:0; background:' + difBg + '; color:#fff; border:none; cursor:default;'"
                                                            :value="difDisplay"
                                                            title="Diferencia">
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa-solid fa-boxes-stacked fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay productos en este inventario</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<script>
function swalCancelarInventario(id, folio) {
    Swal.fire({
        title: '\u00bfCancelar inventario #' + folio + '?',
        html: 'El inventario quedar\u00e1 marcado como <strong>Eliminado</strong> y no podr\u00e1 recuperarse.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'S\u00ed, cancelar',
        cancelButtonText: 'Volver'
    }).then(function(result) {
        if (result.isConfirmed) {
            Livewire.dispatch('cancelarInventario', { id: id });
        }
    });
}

function swalFinalizarInventario(id, folio, total) {
    Swal.fire({
        title: '\u00bfFinalizar inventario #' + folio + '?',
        html: '<p>Se procesar\u00e1n <strong>' + total + '</strong> productos.</p><p class="text-muted small mb-0">Se ajustar\u00e1 el stock, se actualizar\u00e1 la fecha de control y se registrar\u00e1n las diferencias en el Kardex.</p>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'S\u00ed, finalizar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) {
            Livewire.dispatch('ejecutarFinalizar', { id: id });
        }
    });
}
</script>

{{-- PASO 1: OCUPANTES DE LA HABITACIÓN --}}
@if($mostrarModal && $habitacion && $pasoCheckIn === 1)
<div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.55);"
     x-data x-init="$nextTick(() => document.getElementById('buscadorCliente')?.focus())"
     @keydown.escape.window="$wire.cerrarModal()"
     x-on:ocupante-agregado.window="$nextTick(() => document.getElementById('buscadorCliente')?.focus())">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-users me-2"></i>
                    Paso 1: Ocupantes de la habitación
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"></button>
            </div>

            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fa-solid fa-door-open fa-2x mb-2 text-primary"></i>
                    <p class="fw-bold mb-0">Habitación {{ $habitacion->numero }}</p>
                    <small class="text-muted">{{ $habitacion->tipoHabitacion->nombre }}</small>
                </div>

                {{-- Lista de ocupantes agregados --}}
                @if(count($ocupantes) > 0)
                <div class="mb-3">
                    <h6 class="fw-semibold mb-2">
                        <i class="fa-solid fa-list me-1"></i>
                        Ocupantes ({{ count($ocupantes) }})
                    </h6>
                    <div class="list-group">
                        @foreach($ocupantes as $index => $ocup)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                @if($ocup['es_responsable'])
                                    <span class="badge bg-primary me-2">Responsable</span>
                                @else
                                    <span class="badge bg-secondary me-2">Acomp.</span>
                                @endif
                                <strong>{{ $ocup['nombre'] }}</strong>
                                <span class="text-muted ms-2">• {{ $ocup['celular'] }}</span>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" wire:click="eliminarOcupante({{ $index }})" title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Buscador único --}}
                <div class="border rounded p-3 bg-light">
                    <h6 class="fw-semibold mb-2">
                        <i class="fa-solid fa-magnifying-glass me-1"></i>
                        {{ count($ocupantes) === 0 ? 'Buscar huésped responsable' : 'Agregar otro ocupante' }}
                    </h6>

                    <div class="input-group mb-2">
                        <input type="text"
                               id="buscadorCliente"
                               class="form-control"
                               wire:model="celularBusqueda"
                               @keydown.enter="$wire.buscarCliente()"
                               placeholder="Celular o CI/DNI..."
                               maxlength="15"
                               autocomplete="off">
                        <button type="button" class="btn btn-primary" wire:click="buscarCliente">
                            <i class="fa-solid fa-magnifying-glass me-1"></i>
                            Buscar
                        </button>
                    </div>

                    {{-- Cliente encontrado --}}
                    @if($clienteEncontrado)
                    <div class="alert alert-success py-2 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <i class="fa-solid fa-check-circle me-1"></i>
                                <strong>{{ $clienteNombre }}</strong>
                                @if($clienteCelular)
                                    <span class="text-muted ms-2">Cel: {{ $clienteCelular }}</span>
                                @endif
                                @if($clienteCi)
                                    <span class="text-muted ms-2">CI: {{ $clienteCi }}</span>
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-success" wire:click="agregarOcupante">
                                <i class="fa-solid fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </div>
                    @elseif(!$clienteEncontrado && strlen($celularBusqueda) >= 6)
                    {{-- Formulario nuevo cliente --}}
                    <div class="alert alert-warning py-2 mb-2">
                        <i class="fa-solid fa-user-plus me-1"></i>
                        <strong>Nuevo cliente</strong> - Completa los datos
                    </div>
                    <div class="mb-2">
                        <input type="text"
                               class="form-control form-control-sm"
                               wire:model="clienteNombre"
                               placeholder="Nombre completo *"
                               autocomplete="off">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <input type="text"
                                   class="form-control form-control-sm"
                                   wire:model="clienteCelular"
                                   placeholder="Celular"
                                   maxlength="8"
                                   autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <input type="text"
                                   class="form-control form-control-sm"
                                   wire:model="clienteNit"
                                   placeholder="Carnet de identidad"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="text"
                               class="form-control form-control-sm"
                               wire:model="clienteDireccion"
                               placeholder="Dirección"
                               autocomplete="off">
                        <button type="button" class="btn btn-sm btn-success" wire:click="agregarOcupante">
                            <i class="fa-solid fa-plus me-1"></i>Agregar
                        </button>
                    </div>
                    @endif
                </div>

                @if(count($ocupantes) === 0)
                <div class="text-center text-muted small mt-3">
                    <i class="fa-solid fa-info-circle me-1"></i>
                    El primer cliente agregado será el <strong>responsable</strong>
                </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="cerrarModal">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <button type="button"
                        class="btn btn-primary"
                        wire:click="avanzarPaso1"
                        @if(count($ocupantes) === 0) disabled @endif>
                    <i class="fa-solid fa-arrow-right me-1"></i>Continuar
                    @if(count($ocupantes) > 0)
                        <span class="badge bg-light text-primary ms-1">{{ count($ocupantes) }} ocupante(s)</span>
                    @endif
                </button>
            </div>

        </div>
    </div>
</div>
@endif

{{-- PASO 2: MODALIDAD --}}
@if($mostrarModal && $habitacion && $pasoCheckIn === 3)
<div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.55);"
     x-data
     @keydown.escape.window="$wire.retrocederPaso()"
     @keydown.enter.window="$wire.avanzarPaso3()">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-bed me-2"></i>
                    Paso 2: Modalidad de alquiler
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"></button>
            </div>

            <div class="modal-body">
                {{-- Info del cliente --}}
                <div class="alert alert-info border-0 py-2 mb-3 text-center">
                    <div class="d-flex justify-content-center align-items-center">
                        <div>
                            <i class="fa-solid fa-user me-2"></i>
                            <strong class="fs-5">{{ $clienteNombre ?: 'Cliente' }}</strong>
                        </div>
                        <span class="mx-2 opacity-50">|</span>
                        <div>
                            <i class="fa-solid fa-mobile me-1"></i>
                            <span>{{ $celularBusqueda }}</span>
                        </div>
                    </div>
                </div>

                {{-- Tarjetas de modalidades --}}
                <p class="text-center text-muted small mb-3">Selecciona la modalidad de alquiler:</p>

                <div class="row g-3 mb-3">
                    @foreach($modalidades->where('activo', true) as $mod)
                        @php
                            $tarifa = $habitacion->tipoHabitacion->tarifas
                                ->where('modalidad', $mod->nombre)
                                ->where('activo', true)
                                ->first();
                            $precio = $tarifa ? $tarifa->precio : 0;
                            $isSelected = $modalidad === $mod->nombre;
                            // Iconos dinámicos
                            $icon = 'clock';
                            if (stripos($mod->nombre, 'noche') !== false) $icon = 'moon';
                            elseif (stripos($mod->nombre, 'semana') !== false || stripos($mod->nombre, 'dia') !== false) $icon = 'calendar-days';
                            elseif (stripos($mod->nombre, 'momentaneo') !== false) $icon = 'gauge-high';
                        @endphp
                        <div class="col-md-4 col-sm-6">
                            <div class="card h-100 {{ $isSelected ? 'border-info shadow' : 'border-secondary' }} cursor-pointer"
                                 wire:click="$set('modalidad', '{{ $mod->nombre }}')"
                                 style="cursor: pointer; border-width: 2px; {{ $isSelected ? 'background: #e7f5ff;' : '' }}">
                                <div class="card-body text-center p-3">
                                    <i class="fa-solid fa-{{ $icon }} {{ $isSelected ? 'text-info' : 'text-muted' }} mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="card-title fw-bold mb-1">{{ $mod->nombre }}</h6>
                                    <p class="card-text text-muted small mb-2">
                                        <i class="fa-solid fa-clock me-1"></i>{{ number_format($mod->horas, 0) }} horas
                                    </p>
                                    @if($precio > 0)
                                        <span class="badge bg-success fs-6 px-3 py-1">Bs. {{ number_format($precio, 2) }}</span>
                                    @else
                                        <span class="badge bg-secondary fs-6 px-3 py-1">Sin tarifa</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('modalidad')
                    <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror

                <div class="text-center text-muted small">
                    <i class="fa-solid fa-lightbulb me-1"></i>
                    Presiona <kbd>Enter</kbd> para continuar o <kbd>ESC</kbd> para retroceder
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="retrocederPaso">
                    <i class="fa-solid fa-arrow-left me-1"></i>Atrás
                </button>
                <button type="button" class="btn btn-info text-white"  wire:click="avanzarPaso3">
                    <i class="fa-solid fa-arrow-right me-1"></i>Continuar
                </button>
            </div>

        </div>
    </div>
</div>
@endif

{{-- PASO 4: CONFIRMACIÓN HORA SALIDA + PAGO --}}
@if($mostrarModal && $habitacion && $pasoCheckIn === 4 && !$procesandoCheckIn)
<div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.55);"
     x-data x-init="$nextTick(() => { let input = $el.querySelector('input[type=number]'); if(input) { input.focus(); input.select(); } })"
     @keydown.escape.window="$wire.retrocederPaso()">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-check-circle me-2"></i>
                    Paso 3: Confirmación
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"></button>
            </div>

            <div class="modal-body">
                {{-- Resumen --}}
                @php
                    $responsable = collect($ocupantes)->firstWhere('es_responsable', true) ?? ($ocupantes[0] ?? null);
                @endphp
                <div class="alert border mb-3 py-3" style="background:#fff;color:#333;">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-user-circle text-primary me-2" style="font-size: 2rem;"></i>
                                <div>
                                    <strong class="d-block text-dark" style="font-size: 1.1rem;">
                                        {{ $responsable['nombre'] ?? 'Sin nombre' }}
                                    </strong>
                                    <small style="color:#666;">
                                        <i class="fa-solid fa-mobile me-1"></i>
                                        {{ $responsable['celular'] ?? 'Sin dato' }}
                                    </small>
                                    @if(count($ocupantes) > 1)
                                        <small class="d-block" style="color:#666;">
                                            <i class="fa-solid fa-users me-1"></i>
                                            + {{ count($ocupantes) - 1 }} acompañante(s)
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-2 mt-md-0">
                            <span class="badge bg-info py-2 px-3" style="font-size: 0.95rem;">
                                <i class="fa-solid fa-bed me-1"></i>
                                {{ $modalidad ?: 'Sin modalidad' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Hora de salida estimada --}}
                @php
                    $modObj = $modalidades->firstWhere('nombre', $modalidad);
                    $horasEstimadas = $modObj ? $modObj->horas : 12;
                    $tarifaObj = $habitacion->tipoHabitacion->tarifas
                        ->where('modalidad', $modalidad)
                        ->where('activo', true)
                        ->first();
                    $numPersonas = count($ocupantes);
                @endphp
                <div class="alert alert-info py-2 mb-3">
                    <i class="fa-solid fa-calendar-check me-1"></i>
                    <strong>Salida estimada:</strong>
                    {{ \Carbon\Carbon::parse($fechaSalidaEst)->locale('es')->isoFormat('D [de] MMM, HH:mm') }}
                    <small class="text-muted d-block mt-1">
                        ({{ number_format($horasEstimadas, 1) }} horas desde ahora)
                    </small>
                </div>

                {{-- Desglose si es por persona --}}
                @if($tarifaObj && $tarifaObj->precio_por_persona)
                <div class="alert border py-2 mb-3" style="background:#fff;">
                    <div class="small" style="color:#333;">
                        <i class="fa-solid fa-users text-primary me-1"></i>
                        <strong style="color:#212529;">Precio por persona:</strong>
                        <span style="color:#333;">Bs. {{ number_format($tarifaObj->precio, 2) }}</span>
                        <span style="color:#333;">× {{ $numPersonas }} {{ $numPersonas > 1 ? 'personas' : 'persona' }}</span>
                        = <strong style="color:#198754;">Bs. {{ number_format($totalCheckIn, 2) }}</strong>
                    </div>
                    @if(count($ocupantes) > 1)
                    <div class="small mt-1" style="color:#666;">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        {{ count($ocupantes) }} ocupante(s) en la habitación
                    </div>
                    @endif
                </div>
                @endif

                {{-- Grid pago --}}
                @php
                    $efectivoNum  = floatval($montoPagoEfectivo ?? 0);
                    $onlineNum    = floatval($montoPagoOnline ?? 0);
                    $totalPagado  = $efectivoNum + $onlineNum;
                    $faltante     = max(0, $totalCheckIn - $totalPagado);
                    $cambio       = max(0, $totalPagado - $totalCheckIn);
                @endphp

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold small">
                            <i class="fa-solid fa-receipt text-primary me-1"></i>Total
                        </label>
                        <input type="text" class="form-control form-control-lg text-center fw-bold"
                               value="Bs. {{ number_format($totalCheckIn, 2) }}"
                               disabled readonly
                               style="background:#e3f2fd;border-color:#2196f3;">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small">
                            <i class="fa-solid fa-wallet text-secondary me-1"></i>Pagado
                        </label>
                        <input type="text" class="form-control form-control-lg text-center fw-bold"
                               value="Bs. {{ number_format($totalPagado, 2) }}"
                               disabled readonly
                               style="background:{{ $faltante > 0 ? '#ffebee' : '#e8f5e9' }};border-color:{{ $faltante > 0 ? '#f44336' : '#4caf50' }};">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small">
                            <i class="fa-solid fa-money-bill text-success me-1"></i>Efectivo
                        </label>
                        <input type="number"
                               class="form-control form-control-lg text-center"
                               wire:model.live.debounce.400ms="montoPagoEfectivo"
                               @keydown.enter="$wire.confirmarCheckIn()"
                               min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small">
                            <i class="fa-solid fa-qrcode text-info me-1"></i>Online / QR
                        </label>
                        <input type="number"
                               class="form-control form-control-lg text-center"
                               wire:model.live.debounce.400ms="montoPagoOnline"
                               @keydown.enter="$wire.confirmarCheckIn()"
                               min="0" step="0.01" placeholder="0.00">
                    </div>
                    @if($cambio > 0)
                    <div class="col-12">
                        <label class="form-label fw-bold small">
                            <i class="fa-solid fa-coins text-warning me-1"></i>Cambio
                        </label>
                        <input type="text" class="form-control form-control-lg text-center fw-bold"
                               value="Bs. {{ number_format($cambio, 2) }}"
                               disabled readonly
                               style="background:#fff3e0;border-color:#ff9800;">
                    </div>
                    @endif
                </div>

                @if($faltante > 0)
                <div class="alert alert-danger mb-0 small">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    <strong>Falta por pagar:</strong> Bs. {{ number_format($faltante, 2) }}
                    <br><small>No se acepta crédito en este servicio. El pago debe cubrir el total.</small>
                </div>
                @endif

                <div class="text-muted small mt-2">
                    <i class="fa-solid fa-keyboard me-1"></i>
                    Presiona <kbd>Enter</kbd> para confirmar o <kbd>ESC</kbd> para retroceder
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="retrocederPaso">
                    <i class="fa-solid fa-arrow-left me-1"></i>Atrás
                </button>
                <button type="button" class="btn btn-success" wire:click="confirmarCheckIn"
                        wire:loading.attr="disabled" wire:target="confirmarCheckIn">
                    <i class="fa-solid fa-check me-1" wire:loading.remove wire:target="confirmarCheckIn"></i>
                    <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="confirmarCheckIn"></span>
                    Registrar Check-in
                </button>
            </div>

        </div>
    </div>
</div>
@endif

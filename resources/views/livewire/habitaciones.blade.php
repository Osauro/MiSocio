<div>
<div class="container-fluid py-3">

    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-hotel me-2 text-primary"></i>Panel de Habitaciones</h4>
            <small class="text-muted">Haz clic en una habitación para gestionar su estado</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('tipos-habitacion') }}" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-layer-group me-1"></i>Tipos y Tarifas
            </a>
            <a href="{{ route('hospedajes') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-clipboard-list me-1"></i>Historial
            </a>
        </div>
    </div>

    <!-- Leyenda de colores -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="badge rounded-pill px-3 py-2" style="background:#198754;font-size:.8rem;">
            <i class="fa-solid fa-check-circle me-1"></i>Disponible
        </span>
        <span class="badge rounded-pill px-3 py-2" style="background:#dc3545;font-size:.8rem;">
            <i class="fa-solid fa-user me-1"></i>Ocupada
        </span>
        <span class="badge rounded-pill px-3 py-2" style="background:#fd7e14;font-size:.8rem;">
            <i class="fa-solid fa-broom me-1"></i>Limpieza
        </span>
        <span class="badge rounded-pill px-3 py-2" style="background:#6c757d;font-size:.8rem;">
            <i class="fa-solid fa-tools me-1"></i>Mantenimiento
        </span>
    </div>

    <!-- Filtros -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <select class="form-select form-select-sm" wire:model.live="filtroTipo">
                <option value="">Todos los tipos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select form-select-sm" wire:model.live="filtroEstado">
                <option value="">Todos los estados</option>
                <option value="disponible">Disponible</option>
                <option value="ocupada">Ocupada</option>
                <option value="limpieza">Limpieza</option>
                <option value="mantenimiento">Mantenimiento</option>
            </select>
        </div>
    </div>

    @if($habitaciones->isEmpty())
        <div class="alert alert-info">
            <i class="fa-solid fa-circle-info me-2"></i>
            No hay habitaciones registradas.
            <a href="{{ route('tipos-habitacion') }}" class="alert-link">Crea tipos y habitaciones</a> primero.
        </div>
    @else

    <!-- Panel de habitaciones por piso -->
    @php $pisos = $habitaciones->groupBy('piso'); @endphp

    @foreach($pisos as $piso => $habs)
        <div class="mb-4">
            <h6 class="text-muted fw-semibold mb-2">
                <i class="fa-solid fa-building me-1"></i>
                {{ $piso === null ? 'Sin piso asignado' : 'Piso ' . $piso }}
                <span class="badge bg-secondary ms-1">{{ $habs->count() }}</span>
            </h6>
            <div class="row g-2">
                @foreach($habs as $hab)
                @php
                    $color = \App\Models\Habitacion::COLORES_ESTADO[$hab->estado] ?? '#6c757d';
                    $icono = \App\Models\Habitacion::ICONOS_ESTADO[$hab->estado] ?? 'fa-question-circle';
                @endphp
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="card border-0 shadow-sm h-100 hab-card"
                         style="cursor:pointer; border-left: 5px solid {{ $color }} !important; border: 1px solid {{ $color }};"
                         wire:click="clickHabitacion({{ $hab->id }})"
                         title="{{ ucfirst($hab->estado) }} — {{ $hab->tipoHabitacion->nombre }}">

                        <div class="card-body p-2 text-center position-relative">
                            <!-- Icono estado (arriba derecha) -->
                            <span class="position-absolute top-0 end-0 m-1"
                                  style="color:{{ $color }}; font-size:.8rem;">
                                <i class="fa-solid {{ $icono }}"></i>
                            </span>

                            <!-- Número de habitación -->
                            <div class="fw-bold fs-4 lh-1 mt-1" style="color:{{ $color }};">
                                {{ $hab->numero }}
                            </div>

                            <!-- Tipo -->
                            <div class="text-muted" style="font-size:.65rem; line-height:1.3;">
                                {{ $hab->tipoHabitacion->nombre }}
                            </div>

                            <!-- Badge estado -->
                            <span class="badge mt-1" style="background:{{ $color }}; font-size:.6rem;">
                                {{ ucfirst($hab->estado) }}
                            </span>

                            <!-- Info huésped (solo si ocupada) -->
                            @if($hab->estado === 'ocupada' && $hab->hospedajeActivo)
                            @php
                                $hosp    = $hab->hospedajeActivo;
                                $habHosp = $hosp->habitaciones->where('habitacion_id', $hab->id)->first()
                                           ?? $hosp->habitaciones->first();
                                $nombre  = $hosp->cliente?->nombre ?? 'Huésped';
                                $nombreCorto = mb_strlen($nombre) > 11 ? mb_substr($nombre, 0, 10) . '…' : $nombre;
                            @endphp
                            <div class="border-top mt-1 pt-1" style="font-size:.6rem; line-height:1.35;">
                                <div class="text-truncate fw-semibold" style="color:#333;">
                                    <i class="fa-solid fa-user me-1 text-danger" style="font-size:.55rem;"></i>{{ $nombreCorto }}
                                    @if($hosp->acompanantes && count($hosp->acompanantes) > 0)
                                    <span class="badge bg-secondary ms-1" style="font-size:.5rem;">
                                        +{{ count($hosp->acompanantes) }}
                                    </span>
                                    @endif
                                </div>
                                @if($habHosp)
                                <div class="text-muted">
                                    {{ ucfirst($habHosp->modalidad) }}
                                    · {{ number_format($habHosp->unidades, 0) }}
                                    @if($hosp->fecha_salida_estimada)
                                    <br><i class="fa-solid fa-right-from-bracket me-1" style="font-size:.5rem;"></i>{{ \Carbon\Carbon::parse($hosp->fecha_salida_estimada)->format('d/m H:i') }}
                                    @endif
                                </div>
                                @endif
                                <div class="text-muted">
                                    Entrada: {{ \Carbon\Carbon::parse($hosp->fecha_entrada)->format('H:i') }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @endif

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- FLUJO CHECK-IN (3 PASOS)                                   --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    @include('livewire.habitaciones-checkin-steps')

    {{-- Spinner procesando --}}
    @if($procesandoCheckIn)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.55);">
        <div class="modal-dialog modal-dialog-centered" style="max-width:340px;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body py-4 text-center">
                    <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;"></div>
                    <h6 class="text-success mb-0">Registrando check-in...</h6>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- MODAL NORMAL: OCUPADA / LIMPIEZA / MANTENIMIENTO          --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    @if($mostrarModal && $habitacion && $pasoCheckIn === 0)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">

                <!-- Header con color según estado -->
                @php
                    $colorModal = \App\Models\Habitacion::COLORES_ESTADO[$habitacion->estado] ?? '#6c757d';
                    $iconoModal = \App\Models\Habitacion::ICONOS_ESTADO[$habitacion->estado] ?? 'fa-door-open';
                @endphp
                <div class="modal-header text-white" style="background:{{ $colorModal }};">
                    <h5 class="modal-title">
                        <i class="fa-solid {{ $iconoModal }} me-2"></i>
                        Habitación {{ $habitacion->numero }}
                        <small class="ms-2 opacity-75">{{ $habitacion->tipoHabitacion->nombre }}</small>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"></button>
                </div>

                <div class="modal-body">

                    {{-- ── OCUPADA: info + checkout ── --}}
                    @if($habitacion->estado === 'ocupada')
                    @php $hosAct = $habitacion->hospedajeActivo; @endphp

                    <div class="mb-2">
                        <div class="fw-semibold" style="font-size:1.05rem;">
                            <i class="fa-solid fa-user text-muted me-1"></i>{{ $hosAct?->cliente?->nombre ?? '—' }}
                        </div>
                        @if($hosAct?->acompanantes && count($hosAct->acompanantes) > 0)
                        <div class="text-muted small">
                            <i class="fa-solid fa-user-group me-1"></i>
                            @foreach($hosAct->acompanantes as $acomp)
                                {{ $acomp['nombre'] }}@if(!$loop->last), @endif
                            @endforeach
                        </div>
                        @endif
                    </div>

                    @php
                        $pivotHab = $hosAct?->habitaciones->first();
                        $tarifaActiva = $habitacion->tipoHabitacion->tarifas
                            ->where('activo', true)
                            ->firstWhere('modalidad', $pivotHab?->modalidad);
                    @endphp

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="rounded-2 p-2" style="background:#f0f0f0;">
                                <small class="text-muted d-block">Entrada</small>
                                <span class="fw-semibold small">{{ $hosAct?->fecha_entrada?->format('d/m/Y H:i') ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-2 p-2" style="background:#f0f0f0;">
                                <small class="text-muted d-block">Salida estimada</small>
                                <span class="fw-semibold small">{{ $hosAct?->fecha_salida_estimada?->format('d/m/Y H:i') ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-2 p-2" style="background:#f0f0f0;">
                                <small class="text-muted d-block">Modalidad</small>
                                <span class="fw-semibold small">{{ ucfirst($pivotHab?->modalidad ?? '—') }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-2 p-2" style="background:#f0f0f0;">
                                <small class="text-muted d-block">Total</small>
                                <span class="fw-bold small" style="color:#198754;">Bs. {{ number_format($hosAct?->total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    @endif

                    <!-- ── LIMPIEZA / MANTENIMIENTO ── -->
                    @if(in_array($habitacion->estado, ['limpieza', 'mantenimiento']))
                    <div class="text-center py-2">
                        <i class="fa-solid {{ $iconoModal }} fa-3x mb-2" style="color:{{ $colorModal }};"></i>
                        <p class="fw-semibold">La habitación está en <strong>{{ ucfirst($habitacion->estado) }}</strong></p>
                        <p class="text-muted small">¿Marcar como disponible?</p>
                    </div>
                    @endif

                </div><!-- /.modal-body -->

                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="cerrarModal">
                        <i class="fa-solid fa-xmark me-1"></i>Cancelar
                    </button>

                    @if($habitacion->estado === 'ocupada')
                    <button type="button" class="btn btn-success btn-sm" wire:click="solicitarEntrega">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Entregar Habitación
                    </button>
                    @endif

                    @if(in_array($habitacion->estado, ['limpieza', 'mantenimiento']))
                    <button type="button" class="btn btn-success btn-sm" wire:click="marcarDisponible">
                        <i class="fa-solid fa-check me-1"></i>Marcar Disponible
                    </button>
                    @endif

                    @if($habitacion->estado === 'limpieza')
                    <button type="button" class="btn btn-warning btn-sm" wire:click="marcarMantenimiento">
                        <i class="fa-solid fa-wrench me-1"></i>Mantenimiento
                    </button>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @endif

<style>
.hab-card:hover { transform: translateY(-2px); transition: transform .15s ease; box-shadow: 0 4px 12px rgba(0,0,0,.15) !important; }
.hab-card { transition: transform .15s ease; }
</style>
</div>

<div>
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-layer-group me-2 text-primary"></i>Tipos de Habitación</h4>
            <small class="text-muted">Administra los tipos, sus tarifas y habitaciones disponibles</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('habitaciones') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-door-open me-1"></i>Panel
            </a>
            <button class="btn btn-sm btn-primary" wire:click="create">
                <i class="fa-solid fa-plus me-1"></i>Nuevo Tipo
            </button>
        </div>
    </div>

    @if($tipos->isEmpty())
    <div class="alert alert-info">
        <i class="fa-solid fa-circle-info me-2"></i>No hay tipos de habitación. Crea el primero.
    </div>
    @else
    <div class="row g-3">
        @foreach($tipos as $tipo)
        <div class="col-md-6 col-xl-4">
            <div class="card border shadow-sm h-100" style="border-top: 4px solid {{ $tipo->color }} !important;">

                {{-- ── Cabecera del tipo ── --}}
                <div class="card-header d-flex justify-content-between align-items-center py-2 bg-light">
                    <div>
                        <span class="fw-bold fs-5 text-dark">{{ $tipo->nombre ?: 'Sin nombre' }}</span>
                        <span class="badge bg-secondary ms-2">
                            <i class="fa-solid fa-users me-1"></i>Máx. {{ $tipo->capacidad_maxima }}
                        </span>
                    </div>
                    <div class="d-flex gap-1 align-items-center">
                        <span class="badge rounded-pill" style="background:{{ $tipo->color }};">
                            {{ $tipo->habitaciones_count }}
                        </span>
                        <button class="btn btn-xs btn-outline-secondary" wire:click="verTarifas({{ $tipo->id }})" title="Tarifas">
                            <i class="fa-solid fa-tag"></i>
                        </button>
                        <button class="btn btn-xs btn-outline-primary" wire:click="edit({{ $tipo->id }})" title="Editar tipo">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-xs btn-outline-danger" wire:click="eliminarTipo({{ $tipo->id }})"
                                wire:confirm="¿Eliminar este tipo de habitación?" title="Eliminar">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body py-3">
                    {{-- ── Características ── --}}
                    @if($tipo->caracteristicas && count($tipo->caracteristicas) > 0)
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($tipo->caracteristicas as $caract)
                        <span class="badge border px-2 py-1" style="font-size:.75rem; background: #e7f5ff; color: #495057;">
                            <i class="fa-solid fa-check-circle text-success me-1"></i>
                            {{ $caract }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- ── Tarifas resumidas ── --}}
                    @if($tipo->tarifas->where('activo', true)->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($tipo->tarifas->where('activo', true) as $tar)
                        <span class="badge text-dark border px-2 py-1" style="font-size:.75rem; background: #e7f5ff;">
                            <i class="fa-solid fa-tag me-1 text-primary"></i>
                            <strong>{{ $tar->modalidad ?: 'Sin nombre' }}</strong>:
                            <span class="text-success fw-bold">Bs. {{ number_format($tar->precio, 2) }}</span>
                            @if($tar->precio_por_persona)
                            <small class="badge bg-success ms-1" style="font-size:.6rem;">/pers</small>
                            @endif
                        </span>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-warning py-2 mb-2">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        <strong>Sin tarifas configuradas.</strong>
                        <button class="btn btn-sm btn-warning ms-2" wire:click="verTarifas({{ $tipo->id }})">
                            Configurar ahora
                        </button>
                    </div>
                    @endif

                    {{-- ── Habitaciones de este tipo ── --}}
                    <div class="border-top pt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="fw-semibold text-secondary">
                                <i class="fa-solid fa-door-open me-1"></i>Habitaciones
                            </small>
                            <button class="btn btn-xs btn-success" wire:click="crearHabitacion({{ $tipo->id }})">
                                <i class="fa-solid fa-plus me-1"></i>Agregar
                            </button>
                        </div>

                        @if($tipo->habitaciones->isEmpty())
                        <p class="small text-muted text-center py-1 mb-0">Sin habitaciones asignadas</p>
                        @else
                        @php
                            $coloresHab = ['disponible'=>'success','ocupada'=>'danger','limpieza'=>'warning','mantenimiento'=>'secondary'];
                            $hexHab     = ['disponible'=>'#198754','ocupada'=>'#dc3545','limpieza'=>'#fd7e14','mantenimiento'=>'#6c757d'];
                            $pisos = $tipo->habitaciones->groupBy('piso')->sortKeys();
                        @endphp

                        @foreach($pisos as $piso => $grupo)
                        @if($pisos->count() > 1)
                        <div class="text-muted mb-1" style="font-size:.68rem;font-weight:600;letter-spacing:.04em;">
                            PISO {{ $piso }}
                        </div>
                        @endif
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @foreach($grupo as $hab)
                            @php
                                $col = $coloresHab[$hab->estado] ?? 'secondary';
                                $hex = $hexHab[$hab->estado]     ?? '#6c757d';
                            @endphp
                            <div class="hab-tipo-card"
                                 style="width:70px;height:70px;border:2px solid {{ $hex }};border-radius:8px;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:space-between;padding:8px 4px 4px 4px;"
                                 title="{{ ucfirst($hab->estado) }}">

                                {{-- Número grande --}}
                                <span class="fw-bold lh-1" style="font-size:1.4rem;color:{{ $hex }};">
                                    {{ $hab->numero }}
                                </span>

                                {{-- Botones abajo --}}
                                <div class="d-flex gap-1">
                                    <button class="btn p-0 d-flex align-items-center justify-content-center"
                                            wire:click="editarHabitacion({{ $hab->id }})"
                                            style="width:18px;height:18px;background:rgba(13,110,253,.15);border-radius:3px;border:none;"
                                            title="Editar">
                                        <i class="fa-solid fa-pen" style="font-size:.5rem;color:#0d6efd;"></i>
                                    </button>
                                    <button class="btn p-0 d-flex align-items-center justify-content-center"
                                            wire:click="eliminarHabitacion({{ $hab->id }})"
                                            wire:confirm="¿Eliminar habitación {{ $hab->numero }}?"
                                            style="width:18px;height:18px;background:rgba(220,53,69,.15);border-radius:3px;border:none;"
                                            title="Eliminar">
                                        <i class="fa-solid fa-trash" style="font-size:.5rem;color:#dc3545;"></i>
                                    </button>
                                </div>

                            </div>
                            @endforeach
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>

            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- MODAL TIPO DE HABITACIÓN                                       --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
@if($mostrarModal)
<div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" wire:ignore.self>
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-{{ $editMode ? 'pen' : 'plus' }} me-2"></i>
                    {{ $editMode ? 'Editar tipo de habitación' : 'Nuevo tipo de habitación' }}
                </h5>
                <button type="button" class="btn-close" wire:click="closeModal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('nombre') is-invalid @enderror"
                               wire:model="nombre"
                               placeholder="Ej: Simple, Doble, Suite...">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">
                            Capacidad máx. <span class="text-danger">*</span>
                        </label>
                        <input type="number" min="1" max="20"
                               class="form-control @error('capacidadMaxima') is-invalid @enderror"
                               wire:model="capacidadMaxima">
                        @error('capacidadMaxima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Color identificador</label>
                        <input type="color" class="form-control form-control-color w-100"
                               wire:model="color" style="height:2.5rem;">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">
                            Características
                            <i class="fa-solid fa-info-circle text-muted ms-1"
                               title="Separe las características con comas"></i>
                        </label>
                        <input type="text" class="form-control"
                               wire:model="caracteristicas"
                               placeholder="Ej: TV Cable, Baño privado, Ducha, Desayuno...">
                        <small class="text-muted">
                            <i class="fa-solid fa-lightbulb me-1"></i>
                            Separe cada característica con una coma (,)
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                <button type="button" class="btn btn-primary" wire:click="save">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- MODAL HABITACIÓN INDIVIDUAL                                    --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
@if($mostrarModalHabitacion)
<div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" wire:ignore.self>
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-{{ $editModeHabitacion ? 'pen' : 'plus' }} me-2"></i>
                    {{ $editModeHabitacion ? 'Editar habitación' : 'Nueva habitación' }}
                </h5>
                <button type="button" class="btn-close" wire:click="cerrarModalHabitacion"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Número / Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('numero') is-invalid @enderror"
                               wire:model="numero"
                               placeholder="Ej: 101, A1, Suite 3...">
                        @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Piso</label>
                        <input type="number" min="0" max="100"
                               class="form-control @error('piso') is-invalid @enderror"
                               wire:model="piso">
                        @error('piso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Estado</label>
                        <select class="form-select @error('estadoHab') is-invalid @enderror"
                                wire:model="estadoHab">
                            <option value="disponible">Disponible</option>
                            <option value="ocupada">Ocupada</option>
                            <option value="limpieza">Limpieza</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                        @error('estadoHab') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="cerrarModalHabitacion">Cancelar</button>
                <button type="button" class="btn btn-primary" wire:click="guardarHabitacion">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- MODAL TARIFAS                                                  --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
@if($mostrarTarifas)
<div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-tags me-2"></i>
                    Configurar Tarifas
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="cerrarTarifas"></button>
            </div>
            <div class="modal-body">
                @if($modalidades->isEmpty())
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    No hay modalidades de alquiler configuradas.
                    <a href="{{ route('modalidades') }}" class="alert-link">Ve al módulo de Modalidades</a>
                    y crea al menos una primero.
                </div>
                @else
                <p class="text-muted small mb-3">Define el precio para cada modalidad de alquiler.</p>

                <div class="row g-3">
                    @foreach($modalidades->where('activo', true) as $mod)
                    <div class="col-md-6">
                        <div class="card h-100 border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-primary">
                                            <i class="fa-solid fa-tag me-1"></i>
                                            {{ $mod->nombre }}
                                        </h6>
                                        <small class="text-muted">({{ number_format($mod->horas, 0) }} horas)</small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               wire:model="tarifasPorPersona.{{ $mod->nombre }}"
                                               id="pp_{{ $mod->id }}">
                                        <label class="form-check-label small" for="pp_{{ $mod->id }}">
                                            Por persona
                                        </label>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Bs.</span>
                                    <input type="number" step="0.5" min="0"
                                           class="form-control"
                                           wire:model="tarifasPrecios.{{ $mod->nombre }}"
                                           placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="cerrarTarifas">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                @if(!$modalidades->isEmpty())
                <button type="button" class="btn btn-primary" wire:click="guardarTodasTarifas">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar Todo
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<style>
.btn-xs { padding: .2rem .5rem; font-size: .75rem; }
.hab-tipo-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.18); transform: translateY(-1px); transition: transform .1s ease, box-shadow .1s ease; }
.hab-tipo-card { transition: transform .1s ease, box-shadow .1s ease; }
</style>
</div>

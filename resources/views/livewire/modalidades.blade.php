<div>
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fa-solid fa-tags me-2 text-primary"></i>Modalidades de Alquiler
            </h4>
            <small class="text-muted">Administra las modalidades disponibles (Hora, Noche, Semana, etc.)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tipos-habitacion') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-layer-group me-1"></i>Tipos
            </a>
            <button class="btn btn-sm btn-primary" wire:click="create">
                <i class="fa-solid fa-plus me-1"></i>Nueva Modalidad
            </button>
        </div>
    </div>

    @if($modalidades->isEmpty())
    <div class="alert alert-info">
        <i class="fa-solid fa-circle-info me-2"></i>
        No hay modalidades creadas. Crea la primera para poder configurar tarifas.
        <strong>Ejemplos:</strong> Hora (1h), Noche (12h), Día (24h), Semana (168h).
    </div>
    @else
    <div class="row g-3">
        @foreach($modalidades as $mod)
        <div class="col-md-6 col-lg-4">
            <div class="card border shadow-sm h-100 {{ $mod->activo ? '' : 'border-secondary' }}"
                 style="border-top: 4px solid {{ $mod->activo ? '#0d6efd' : '#6c757d' }} !important;">

                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1 fw-bold {{ $mod->activo ? 'text-dark' : 'text-muted text-decoration-line-through' }}">
                                {{ $mod->nombre ?: 'Sin nombre' }}
                            </h5>
                            @if(!$mod->activo)
                            <span class="badge bg-secondary mb-2">Inactiva</span>
                            @endif
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   wire:change="toggleActivo({{ $mod->id }})"
                                   {{ $mod->activo ? 'checked' : '' }}
                                   title="{{ $mod->activo ? 'Desactivar' : 'Activar' }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-clock text-primary fs-4"></i>
                            <div>
                                <span class="badge bg-primary fs-6 px-3 py-2">
                                    {{ number_format($mod->horas, 1) }} {{ $mod->horas == 1 ? 'hora' : 'horas' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($mod->tarifas_count > 0)
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fa-solid fa-tag me-1"></i>
                        Usada en <strong>{{ $mod->tarifas_count }}</strong> {{ $mod->tarifas_count == 1 ? 'tarifa' : 'tarifas' }}
                    </div>
                    @else
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Sin tarifas asociadas
                    </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary flex-grow-1" wire:click="edit({{ $mod->id }})">
                            <i class="fa-solid fa-pen me-1"></i>Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                                wire:click="eliminar({{ $mod->id }})"
                                wire:confirm="¿Eliminar modalidad '{{ $mod->nombre }}'?">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- MODAL CREAR/EDITAR                                             --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
@if($mostrarModal)
<div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-{{ $editMode ? 'pen' : 'plus' }} me-2"></i>
                    {{ $editMode ? 'Editar modalidad' : 'Nueva modalidad' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Nombre <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control @error('nombre') is-invalid @enderror"
                           wire:model="nombre"
                           placeholder="Ej: Noche, Hora, Semana, Momentáneo"
                           maxlength="80">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Nombre descriptivo de la modalidad de alquiler</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Horas <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.5" min="0.5" max="9999"
                           class="form-control @error('horas') is-invalid @enderror"
                           wire:model.live="horas"
                           placeholder="12">
                    @error('horas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">
                        @if($horas)
                        <strong>Duración:</strong> {{ number_format($horas, 1) }} hora(s)
                        @else
                        Ejemplos: 1 (hora), 3 (momentáneo), 12 (noche), 24 (día), 168 (semana)
                        @endif
                    </div>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox"
                           wire:model="activo" id="switch-activo">
                    <label class="form-check-label" for="switch-activo">
                        Modalidad activa
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="cerrarModal">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" wire:click="save">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
            </div>

        </div>
    </div>
</div>
@endif

</div>

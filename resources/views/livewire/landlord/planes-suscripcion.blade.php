<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">
                                <i class="fa-solid fa-layer-group me-2"></i>
                                Planes de Suscripción
                            </h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    <input type="text" class="form-control text-start" placeholder="Buscar planes"
                                        wire:model.live="search" style="min-width: 200px;">
                                    <button class="btn btn-primary" wire:click="create">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para móvil -->
                    <div class="card-header card-no-border d-md-none"
                        style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="input-group">
                            <input type="text" class="form-control text-start" placeholder="Buscar planes"
                                wire:model.live="search">
                            <button class="btn btn-primary" wire:click="create">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-2 pb-3">
                        <!-- Grid de tarjetas -->
                        <div class="row g-1">
                            @forelse ($planes as $plan)
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-3">
                                    <div class="card h-100 border-2 {{ $plan->activo ? 'border-primary' : 'border-secondary' }}">
                                        <!-- Header con botones -->
                                        <div class="card-header {{ $plan->activo ? 'bg-primary text-white' : 'bg-secondary text-white' }} py-1 px-2">
                                            <div class="d-flex justify-content-between align-items-center gap-1">
                                                <small class="mb-0 fw-bold text-truncate" style="font-size: 0.75rem;">{{ $plan->nombre }}</small>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-light btn-sm py-0 px-1 text-dark" style="font-size: 0.65rem;"
                                                        wire:click="edit({{ $plan->id }})" title="Editar">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    @if($plan->tenants_count == 0)
                                                        <button class="btn btn-danger btn-sm py-0 px-1" style="font-size: 0.65rem;"
                                                            onclick="confirm('¿Eliminar?') || event.stopImmediatePropagation()"
                                                            wire:click="delete({{ $plan->id }})" title="Eliminar">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Cuerpo compacto -->
                                        <div class="card-body py-1 px-2" style="font-size: 0.75rem;">
                                            <!-- Precio -->
                                            <div class="text-center mb-1">
                                                <h5 class="text-success mb-0" style="font-size: 1.2rem;">
                                                    Bs. {{ number_format($plan->precio, 2) }}
                                                </h5>
                                                @if($plan->duracion_meses == 0)
                                                    <span class="badge bg-info" style="font-size: 0.6rem;">15 días</span>
                                                @else
                                                    <span class="badge bg-primary" style="font-size: 0.6rem;">{{ $plan->duracion_texto }}</span>
                                                @endif
                                            </div>

                                            <!-- Slug -->
                                            <div class="text-center mb-1">
                                                <small class="text-muted" style="font-size: 0.65rem;">
                                                    <i class="fa-solid fa-tag"></i> {{ $plan->slug }}
                                                </small>
                                            </div>

                                            <!-- Estadísticas compactas -->
                                            <div class="row g-1 mb-1">
                                                <div class="col-6">
                                                    <div class="text-center p-1 bg-light rounded" style="font-size: 0.7rem;">
                                                        <small class="text-muted d-block" style="font-size: 0.6rem;">Tenants</small>
                                                        <strong class="text-primary">{{ $plan->tenants_count }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-center p-1 bg-light rounded" style="font-size: 0.7rem;">
                                                        <small class="text-muted d-block" style="font-size: 0.6rem;">Estado</small>
                                                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                            <input class="form-check-input" type="checkbox" style="font-size: 0.7rem;"
                                                                wire:click="toggleActivo({{ $plan->id }})"
                                                                {{ $plan->activo ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Características simplificadas -->
                                            @if($plan->caracteristicas && count($plan->caracteristicas) > 0)
                                                <div class="mb-1" style="font-size: 0.6rem;">
                                                    @foreach(array_slice($plan->caracteristicas, 0, 3) as $caracteristica)
                                                        <div class="mb-0">
                                                            <i class="fa-solid fa-check text-success"></i>
                                                            <small>{{ Str::limit($caracteristica, 25) }}</small>
                                                        </div>
                                                    @endforeach
                                                    @if(count($plan->caracteristicas) > 3)
                                                        <small class="text-muted">+{{ count($plan->caracteristicas) - 3 }} más</small>
                                                    @endif
                                                </div>
                                            @endif

                                            <!-- Indicador de QR -->
                                            @if($plan->qr_imagen)
                                                <div class="text-center mt-2 py-1 bg-light rounded">
                                                    <small class="text-success" style="font-size: 0.65rem;">
                                                        <i class="fa-solid fa-qrcode"></i> QR disponible
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa-solid fa-layer-group fa-5x mb-3 text-muted"></i>
                                    <p class="h5 text-muted mb-0">No se encontraron planes</p>
                                </div>
                            @endforelse
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
                <small class="text-muted d-none d-md-block">Created By <a href="https://dieguitosoft.com" target="_blank">DieguitoSoft.com</a></small>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" class="form-control form-control-sm text-center" style="width: 60px;"
                        wire:model.live="perPage" min="1" max="100" title="Registros por página">
                    {{ $planes->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal para crear/editar plan -->
    @if($modalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-layer-group me-2"></i>
                            {{ $planId ? 'Editar Plan' : 'Nuevo Plan' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save" id="planForm">
                            <div class="row">
                                <!-- Nombre -->
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Nombre del Plan *</label>
                                    <input type="text" wire:model.live="nombre" class="form-control @error('nombre') is-invalid @enderror">
                                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Orden -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Orden</label>
                                    <input type="number" wire:model="orden" class="form-control @error('orden') is-invalid @enderror">
                                    @error('orden') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Slug -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slug (identificador único) *</label>
                                    <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror">
                                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Ej: mensual, trimestral, anual</small>
                                </div>

                                <!-- Duración -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Duración (meses) *</label>
                                    <input type="number" wire:model="duracion_meses" class="form-control @error('duracion_meses') is-invalid @enderror" min="0">
                                    @error('duracion_meses') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">0 = Demo</small>
                                </div>

                                <!-- Precio -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Precio (Bs.) *</label>
                                    <input type="number" wire:model="precio" class="form-control @error('precio') is-invalid @enderror" step="0.01" min="0">
                                    @error('precio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Descripción -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea wire:model="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3"></textarea>
                                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- QR de Pago -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">QR para Pago</label>

                                    @if($qr_imagen_existente && !$eliminar_qr)
                                        <div class="card mb-2">
                                            <div class="card-body p-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ Storage::url($qr_imagen_existente) }}"
                                                         alt="QR de pago"
                                                         class="img-thumbnail"
                                                         style="max-width: 150px; max-height: 150px;">
                                                    <div class="flex-grow-1">
                                                        <p class="mb-1 text-success">
                                                            <i class="fa-solid fa-check-circle"></i> QR cargado
                                                        </p>
                                                        <button type="button" wire:click="eliminarQr" class="btn btn-sm btn-danger">
                                                            <i class="fa-solid fa-trash"></i> Eliminar QR
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!$qr_imagen_existente || $eliminar_qr)
                                        <input type="file"
                                               wire:model="qr_imagen"
                                               class="form-control @error('qr_imagen') is-invalid @enderror"
                                               accept="image/*">
                                        @error('qr_imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="text-muted">Sube una imagen del código QR para pagos (máximo 2MB)</small>
                                    @else
                                        <div class="mt-2">
                                            <input type="file"
                                                   wire:model="qr_imagen"
                                                   class="form-control @error('qr_imagen') is-invalid @enderror"
                                                   accept="image/*">
                                            @error('qr_imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            <small class="text-muted">Cargar un nuevo QR (reemplazará el actual)</small>
                                        </div>
                                    @endif

                                    @if($qr_imagen)
                                        <div class="mt-2">
                                            <p class="text-success mb-1">
                                                <i class="fa-solid fa-check-circle"></i> Nuevo QR seleccionado - Preview:
                                            </p>
                                            <img src="{{ $qr_imagen->temporaryUrl() }}"
                                                 alt="Preview QR"
                                                 class="img-thumbnail"
                                                 style="max-width: 150px; max-height: 150px;">
                                        </div>
                                    @endif
                                </div>

                                <!-- Características -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Características del Plan</label>

                                    <div class="input-group mb-2">
                                        <input type="text" wire:model="nuevaCaracteristica"
                                            wire:keydown.enter.prevent="agregarCaracteristica"
                                            class="form-control"
                                            placeholder="Escribe una característica y presiona Enter">
                                        <button type="button" wire:click="agregarCaracteristica" class="btn btn-outline-primary">
                                            <i class="fa-solid fa-plus"></i> Agregar
                                        </button>
                                    </div>

                                    @if(!empty($caracteristicas))
                                        <ul class="list-group">
                                            @foreach($caracteristicas as $index => $caracteristica)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><i class="fa-solid fa-check text-success me-2"></i>{{ $caracteristica }}</span>
                                                    <button type="button" wire:click="eliminarCaracteristica({{ $index }})"
                                                        class="btn btn-sm btn-danger">
                                                        <i class="fa-solid fa-times"></i>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <!-- Estado -->
                                <div class="col-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="activo" class="form-check-input" id="activo">
                                        <label class="form-check-label" for="activo">
                                            Plan activo (disponible para asignar a tenants)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="submit" form="planForm" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

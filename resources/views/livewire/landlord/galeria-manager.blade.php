<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">

                    <!-- Header escritorio -->
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">
                                <i class="fa-solid fa-images me-2"></i>
                                Galería de Imágenes
                            </h3>
                            <div class="d-flex gap-2 w-100 w-md-auto">
                                <div class="input-group" style="min-width: 220px;">
                                    <input type="text" class="form-control" placeholder="Buscar imagen..."
                                        wire:model.live="search">
                                </div>
                                <!-- Botón subir imagen -->
                                <label class="btn btn-primary mb-0" style="cursor:pointer;" title="Subir imagen">
                                    <i class="fa-solid fa-upload"></i>
                                    <input type="file" wire:model="nuevaImagen" accept="image/*" class="d-none">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para móvil -->
                    <div class="card-header card-no-border d-md-none"
                        style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control" placeholder="Buscar imagen..."
                                wire:model.live="search">
                            <label class="btn btn-primary mb-0" style="cursor:pointer;">
                                <i class="fa-solid fa-upload"></i>
                                <input type="file" wire:model="nuevaImagen" accept="image/*" class="d-none">
                            </label>
                        </div>
                    </div>

                    <div class="card-body pt-3 pb-4">

                        <!-- Indicador de carga al subir -->
                        <div wire:loading wire:target="nuevaImagen" class="text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Subiendo imagen...</p>
                        </div>

                        @error('nuevaImagen')
                            <div class="alert alert-danger py-2">{{ $message }}</div>
                        @enderror

                        <!-- Grid de imágenes -->
                        <div class="row g-3" wire:loading.remove wire:target="nuevaImagen">
                            @forelse($imagenes as $imagen)
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card h-100 shadow-sm position-relative">
                                        <!-- Imagen -->
                                        <img src="{{ asset('storage/' . $imagen->url) }}"
                                            alt="{{ $imagen->nombre ?? 'Imagen' }}"
                                            class="card-img-top"
                                            style="height: 120px; object-fit: cover;">

                                        <!-- Badge uso -->
                                        <span class="badge bg-secondary position-absolute top-0 start-0 m-1"
                                            title="Veces usado">
                                            <i class="fa-solid fa-box me-1"></i>{{ $imagen->veces_usado }}
                                        </span>

                                        <!-- Botón eliminar -->
                                        <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                            wire:click="confirmarEliminar({{ $imagen->id }})"
                                            title="Eliminar imagen">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                        <!-- Footer info -->
                                        <div class="card-body p-2">
                                            <p class="small text-muted mb-0 text-truncate">
                                                {{ $imagen->nombre ?? basename($imagen->url) }}
                                            </p>
                                            <p class="small text-muted mb-0">
                                                {{ $imagen->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa-solid fa-images fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No se encontraron imágenes en la galería.</p>
                                    <label class="btn btn-primary" style="cursor:pointer;">
                                        <i class="fa-solid fa-upload me-2"></i> Subir primera imagen
                                        <input type="file" wire:model="nuevaImagen" accept="image/*" class="d-none">
                                    </label>
                                </div>
                            @endforelse
                        </div>

                        <!-- Paginación -->
                        @if($imagenes->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $imagenes->links() }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

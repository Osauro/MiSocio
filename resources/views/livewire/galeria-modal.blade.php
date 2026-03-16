<div>
    @if ($mostrar)
        <div class="modal fade show d-block" tabindex="-1"
             style="background-color: rgba(0,0,0,0.75); z-index: 1060;">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-images me-2"></i> Galería de Imágenes
                        </h5>
                        <button type="button" class="btn-close" wire:click="cerrar"></button>
                    </div>

                    <div class="modal-body d-flex flex-column" style="overflow: hidden;">
                        <!-- Búsqueda -->
                        <div class="mb-3 flex-shrink-0">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text"
                                       class="form-control"
                                       wire:model.live.debounce.300ms="busqueda"
                                       placeholder="Buscar por nombre o etiqueta...">
                                @if ($busqueda)
                                    <button class="btn btn-outline-secondary" wire:click="$set('busqueda', '')">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Grid de imágenes -->
                        <div class="row g-3 flex-grow-1 overflow-auto pb-2">
                            @forelse($imagenes as $img)
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card h-100 border-0 shadow-sm"
                                         wire:click="seleccionar({{ $img->id }})"
                                         style="cursor: pointer; transition: transform .15s, box-shadow .15s;"
                                         onmouseover="this.style.transform='scale(1.03)'; this.style.boxShadow='0 6px 18px rgba(0,0,0,.3)';"
                                         onmouseout="this.style.transform=''; this.style.boxShadow='';">
                                        <div style="position: relative; background: #f8f9fa; border-radius: 6px 6px 0 0; display: flex; align-items: center; justify-content: center; height: 160px; overflow: hidden;">
                                            <img src="{{ $img->photo_url }}"
                                                 alt="{{ $img->nombre ?? 'Imagen' }}"
                                                 style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            @if ($img->veces_usado > 0)
                                                <span class="position-absolute top-0 end-0 badge bg-secondary m-1"
                                                      style="font-size: 0.65rem;" title="Veces usado">
                                                    {{ $img->veces_usado }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($img->nombre)
                                            <div class="card-body p-2">
                                                <small class="text-muted text-truncate d-block"
                                                       title="{{ $img->nombre }}">
                                                    {{ $img->nombre }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-5">
                                    <i class="fa-solid fa-image fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0">No hay imágenes en la galería todavía</p>
                                    <small>Sube la primera imagen abajo</small>
                                </div>
                            @endforelse
                        </div>

                        <hr class="my-3 flex-shrink-0">

                        <!-- Subir nueva imagen -->
                        <div class="flex-shrink-0">
                            <p class="fw-semibold mb-2">
                                <i class="fa-solid fa-upload me-1 text-primary"></i> Subir nueva imagen
                            </p>
                            <div class="row align-items-center g-2">
                                <div class="col-md-7">
                                    <input type="file"
                                           class="form-control @error('nuevaImagen') is-invalid @enderror"
                                           wire:model="nuevaImagen"
                                           accept="image/*">
                                    @error('nuevaImagen')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Máx. 10 MB. Se redimensionará a 512×512 px.</small>
                                </div>
                                <div class="col-md-5 d-flex align-items-center gap-2">
                                    @if ($nuevaImagen)
                                        <img src="{{ $nuevaImagen->temporaryUrl() }}"
                                             alt="Preview"
                                             style="height: 70px; width: 70px; object-fit: contain; background:#f8f9fa; border-radius: 6px; border: 1px solid #dee2e6;">
                                    @endif
                                    <button type="button"
                                            class="btn btn-primary"
                                            wire:click="subirImagen"
                                            wire:loading.attr="disabled"
                                            @disabled(!$nuevaImagen)>
                                        <span wire:loading.remove wire:target="subirImagen">
                                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Subir y usar
                                        </span>
                                        <span wire:loading wire:target="subirImagen">
                                            <i class="fa-solid fa-spinner fa-spin me-1"></i> Subiendo...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrar">
                            Cancelar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>

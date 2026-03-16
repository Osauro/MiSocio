<div>
    @if ($mostrar)
    @teleport('body')
        <div class="modal fade show d-block" tabindex="-1"
             style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.75); z-index: 99999;">
            <div class="modal-dialog modal-fullscreen" style="z-index: 100000;">
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

                        <!-- Grid: primera card = subir imagen, resto = galería -->
                        <div class="row g-3 flex-grow-1 overflow-auto pb-2">

                            <!-- Card subir nueva imagen (siempre primera) -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <label class="card h-100 border-2 border-dashed border-primary d-flex flex-column align-items-center justify-content-center text-primary"
                                       style="cursor: pointer; min-height: 160px; border-style: dashed !important; background: #f0f4ff; transition: background .15s;"
                                       onmouseover="this.style.background='#dde8ff'"
                                       onmouseout="this.style.background='#f0f4ff'">
                                    <span wire:loading.remove wire:target="nuevaImagen">
                                        <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2"></i>
                                        <span class="d-block small fw-semibold">Subir imagen</span>
                                        <span class="d-block" style="font-size: 0.7rem; opacity: .7;">Máx. 10 MB</span>
                                    </span>
                                    <span wire:loading wire:target="nuevaImagen">
                                        <i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i>
                                        <span class="d-block small fw-semibold">Subiendo...</span>
                                    </span>
                                    <input type="file"
                                           class="d-none"
                                           wire:model="nuevaImagen"
                                           accept="image/*">
                                </label>
                                @error('nuevaImagen')
                                    <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Imágenes de la galería -->
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
                                <div class="col text-center text-muted py-5">
                                    <i class="fa-solid fa-image fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0">No hay imágenes todavía</p>
                                    <small>Sube la primera usando la card de la izquierda</small>
                                </div>
                            @endforelse

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
    @endteleport
    @endif
</div>

<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Categorías</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    <input type="text" class="form-control text-start" placeholder="Buscar categorías"
                                        wire:model.live="search" style="min-width: 200px;" id="searchInput" autofocus>
                                    <button class="btn btn-primary" wire:click="create"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para móvil -->
                    <div class="card-header card-no-border d-md-none" style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="input-group">
                            <input type="text" class="form-control text-start" placeholder="Buscar categorías"
                                wire:model.live="search" id="searchInput" autofocus>
                            <button class="btn btn-primary" wire:click="create"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-4">
                        <div class="row g-2">
                            @forelse($categorias as $categoria)
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="card mb-0 shadow-sm producto-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start position-relative">
                                                <!-- Botones en esquina superior derecha -->
                                                <div class="position-absolute top-0 end-0 d-flex gap-1">
                                                    <button class="btn btn-sm btn-primary"
                                                        wire:click="edit({{ $categoria->id }})" title="Editar">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                        wire:click="confirmDeleteCategoria({{ $categoria->id }})"
                                                        title="Eliminar">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>

                                                <!-- Imagen de la categoría -->
                                                <div class="flex-shrink-0 me-3">
                                                    @if ($categoria->imagen)
                                                        <img src="{{ Storage::url($categoria->imagen) }}"
                                                            alt="{{ $categoria->nombre }}" class="rounded"
                                                            style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #e9ecef;">
                                                    @else
                                                        <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                                            style="width: 60px; height: 60px; border: 2px solid #e9ecef;">
                                                            <i class="fa-solid fa-layer-group fa-2x text-muted"></i>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Información de la categoría -->
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">{{ $categoria->nombre }}</h6>
                                                    <div class="small">
                                                        <i class="fa-solid fa-box text-primary me-1"></i>
                                                        <span class="text-muted">{{ $categoria->productos_count ?? 0 }} producto(s)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-folder-open fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No se encontraron resultados</p>
                                    </div>
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
                    <div x-data="{
                         init() {
                             const saved = localStorage.getItem('paginateCategorias') || document.cookie.split('; ').find(row => row.startsWith('paginateCategorias='))?.split('=')[1];
                             if (saved) {
                                 $wire.set('perPage', parseInt(saved));
                             }
                         }
                     }">
                        <input type="number"
                               class="form-control form-control-sm text-center"
                               style="width: 60px;"
                               wire:model.live="perPage"
                               min="1"
                               max="100"
                               title="Registros por página"
                               onfocus="this.select()"
                               @input="
                                   localStorage.setItem('paginateCategorias', $event.target.value);
                                   document.cookie = 'paginateCategorias=' + $event.target.value + '; path=/; max-age=31536000';
                               ">
                    </div>
                    {{ $categorias->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal para Crear/Editar Categoría -->
    @if ($mostrarModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="modalcrud"
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Editar Categoría' : 'Nueva Categoría' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row">
                                <!-- Columna Izquierda: Imagen -->
                                <div class="col-md-5">
                                    <div class="mb-3">
                                        <label class="form-label">Imagen</label>
                                        <div class="image-upload-area" onclick="document.getElementById('imagen').click()"
                                            ondrop="handleDrop(event)" ondragover="handleDragOver(event)"
                                            ondragleave="handleDragLeave(event)"
                                            style="border: 2px dashed #ccc; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; min-height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                            @if ($imagen)
                                                <img src="{{ $imagen->temporaryUrl() }}" alt="Preview"
                                                    style="max-width: 100%; max-height: 180px; object-fit: contain; border-radius: 4px;">
                                            @elseif ($editMode && isset($categoria_actual_imagen))
                                                <img src="{{ Storage::url($categoria_actual_imagen) }}" alt="Categoría"
                                                    style="max-width: 100%; max-height: 180px; object-fit: contain; border-radius: 4px;">
                                            @else
                                                <div class="text-muted">
                                                    <i class="fa-solid fa-cloud-arrow-up fa-3x mb-3 d-block"></i>
                                                    <p class="mb-1 fw-semibold">Arrastra una imagen aquí</p>
                                                    <p class="mb-0 small">o haz clic para seleccionar</p>
                                                </div>
                                            @endif
                                        </div>
                                        <input type="file" class="d-none @error('imagen') is-invalid @enderror"
                                            wire:model="imagen" id="imagen" accept="image/*">
                                        @error('imagen')
                                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Columna Derecha: Formulario -->
                                <div class="col-md-7">
                                    <!-- Nombre -->
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                            wire:model="nombre" id="nombre" placeholder="Ej: Bebidas, Snacks, Licores..." autofocus>
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="alert alert-info mb-0">
                                        <h6 class="alert-heading mb-2">
                                            <i class="fa-solid fa-lightbulb me-1"></i>
                                            Información
                                        </h6>
                                        <ul class="mb-0 ps-3 small">
                                            <li>El nombre es obligatorio</li>
                                            <li>La imagen es opcional pero recomendada</li>
                                            <li>Las imágenes se redimensionan automáticamente a 512x512 px en formato JPG optimizado</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <!-- Botones normales (ocultar durante procesamiento) -->
                        <div wire:loading.remove>
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                            <button type="button" class="btn btn-primary" wire:click="save">
                                {{ $editMode ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>

                        <!-- Botón de procesando (mostrar solo durante procesamiento) -->
                        <div wire:loading>
                            <button type="button" class="btn btn-primary" disabled>
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>
                                Procesando...
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>


<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Productos</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Buscar productos"
                                        wire:model.live="search" style="min-width: 200px;" id="searchInput" autofocus>
                                    <button class="btn btn-primary" wire:click="create"><i class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para móvil -->
                    <div class="card-header card-no-border d-md-none" style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar productos"
                                wire:model.live="search" id="searchInput" autofocus>
                            <button class="btn btn-primary" wire:click="create"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-2">
                        <div class="row g-2">
                            @forelse($productos as $producto)
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="card mb-0 shadow-sm producto-card {{ $producto->trashed() ? 'border-danger' : '' }}"
                                         style="{{ $producto->trashed() ? 'background-color: #f8d7da;' : '' }}">
                                        <div class="card-body p-2">
                                            <div class="d-flex align-items-start position-relative">
                                                <!-- Botones en esquina superior derecha -->
                                                <div class="position-absolute top-0 end-0 d-flex gap-1">
                                                    @if($producto->trashed())
                                                        <!-- Producto eliminado: solo mostrar botón restaurar -->
                                                        <button class="btn btn-sm btn-success"
                                                            wire:click="restaurar({{ $producto->id }})"
                                                            title="Restaurar Producto">
                                                            <i class="fa-solid fa-rotate-left"></i>
                                                        </button>
                                                    @else
                                                        <!-- Producto activo: botones normales -->
                                                        <button class="btn btn-sm btn-primary"
                                                            wire:click="edit({{ $producto->id }})" title="Editar">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger"
                                                            wire:click="confirmDeleteProduct({{ $producto->id }})"
                                                            title="Eliminar">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>

                                                <!-- Imagen del producto -->
                                                <div class="flex-shrink-0 me-3">
                                                    <img src="{{ $producto->photo_url }}"
                                                        alt="{{ $producto->nombre }}" class="rounded"
                                                        style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #e9ecef; cursor: pointer;"
                                                        x-on:click="$dispatch('mostrarKardex', { productoId: {{ $producto->id }} })"
                                                        title="Ver movimientos de Kardex">
                                                </div>

                                                <!-- Información del producto -->
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">
                                                        {{ $producto->nombre }}
                                                    </h6>
                                                    <div class="small mb-1">
                                                        <span class="badge bg-secondary">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</span>
                                                        @if ($producto->codigo)
                                                            <span class="text-muted ms-1">{{ $producto->codigo }}</span>
                                                        @endif
                                                        @if(!$producto->control)
                                                            <span class="badge bg-warning text-dark ms-1" title="Control de stock desactivado">
                                                                <i class="fa-solid fa-eye-slash"></i> Sin control
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <!-- Fila de tags - oculta en móviles -->
                                                    <div class="small mb-1 d-none d-md-block">
                                                        <i class="fa-solid fa-tags text-muted me-1" style="font-size: 0.7rem;"></i>
                                                        @if($producto->tags->count() > 0)
                                                            @foreach($producto->tags->take(3) as $tag)
                                                                <span class="badge bg-info text-white me-1" style="font-size: 0.65rem;">{{ $tag->nombre }}</span>
                                                            @endforeach
                                                            @if($producto->tags->count() > 3)
                                                                <span class="text-muted" style="font-size: 0.7rem;">+{{ $producto->tags->count() - 3 }}</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted" style="font-size: 0.7rem;">───────</span>
                                                        @endif
                                                    </div>
                                                    <div class="small text-muted mb-2">
                                                        <i class="fa-solid fa-box text-primary me-1"></i>
                                                        Stock: {{ $producto->stock_formateado }}
                                                        @if($producto->vencidos > 0)
                                                            <span class="text-danger ms-1" title="Stock vencido/pinchado">
                                                                <i class="fa-solid fa-triangle-exclamation"></i> {{ $producto->vencidos }}
                                                            </span>
                                                        @endif
                                                        <span class="ms-2">
                                                            <i class="fa-solid fa-ruler text-info me-1"></i>
                                                            {{ $producto->medida_formateada }} ({{ $producto->cantidad }})
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light py-1 px-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="badge bg-dark text-center py-2 px-2" style="min-width: 80px;">
                                                    <div style="font-weight: 600; line-height: 1.2;">
                                                        @php
                                                            $precio = number_format($producto->precio_de_compra, 2);
                                                            [$entero, $decimal] = explode('.', $precio);
                                                        @endphp
                                                        <span style="font-size: 1.1rem;">{{ $entero }}.</span><span style="font-size: 0.8rem; vertical-align: top;">{{ $decimal }}</span>
                                                    </div>
                                                    <small class="d-block" style="font-size: 0.65rem;">Compra</small>
                                                </div>
                                                <div class="badge bg-success text-center py-2 px-2" style="min-width: 80px;">
                                                    <div style="font-weight: 600; line-height: 1.2;">
                                                        @php
                                                            $precio = number_format($producto->precio_por_mayor, 2);
                                                            [$entero, $decimal] = explode('.', $precio);
                                                        @endphp
                                                        <span style="font-size: 1.1rem;">{{ $entero }}.</span><span style="font-size: 0.8rem; vertical-align: top;">{{ $decimal }}</span>
                                                    </div>
                                                    <small class="d-block" style="font-size: 0.65rem;">Mayor</small>
                                                </div>
                                                <div class="badge bg-danger text-center py-2 px-2" style="min-width: 80px;">
                                                    <div style="font-weight: 600; line-height: 1.2;">
                                                        @php
                                                            $precio = number_format($producto->precio_por_menor, 2);
                                                            [$entero, $decimal] = explode('.', $precio);
                                                        @endphp
                                                        <span style="font-size: 1.1rem;">{{ $entero }}.</span><span style="font-size: 0.8rem; vertical-align: top;">{{ $decimal }}</span>
                                                    </div>
                                                    <small class="d-block" style="font-size: 0.65rem;">Menor</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-box-open fa-5x mb-3 text-muted"></i>
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
                             const saved = localStorage.getItem('paginateProductos') || document.cookie.split('; ').find(row => row.startsWith('paginateProductos='))?.split('=')[1];
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
                                   localStorage.setItem('paginateProductos', $event.target.value);
                                   document.cookie = 'paginateProductos=' + $event.target.value + '; path=/; max-age=31536000';
                               ">
                    </div>
                    {{ $productos->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal para Crear/Editar Producto -->
    <div wire:ignore.self class="modal fade" id="crudModal" tabindex="-1" role="dialog" aria-labelledby="modalcrud">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Editar Producto' : 'Nuevo Producto' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="row">
                            <!-- Columna Izquierda: Imagen -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Imagen del Producto</label>
                                    <div class="image-upload-area" onclick="document.getElementById('imagen').click()"
                                        ondrop="handleDrop(event)" ondragover="handleDragOver(event)"
                                        ondragleave="handleDragLeave(event)"
                                        style="border: 2px dashed #ccc; border-radius: 8px; padding: 15px; text-align: center; cursor: pointer; height: 100%; min-height: 300px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                        @if ($imagen && is_object($imagen))
                                            <img src="{{ $imagen->temporaryUrl() }}" alt="Preview"
                                                style="max-width: 100%; max-height: 280px; object-fit: contain;">
                                        @elseif ($editMode && $producto_actual)
                                            <img src="{{ $producto_actual->photo_url }}" alt="Producto"
                                                style="max-width: 100%; max-height: 280px; object-fit: contain;">
                                        @else
                                            <div class="text-muted">
                                                <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2"></i>
                                                <p class="mb-0 small">Arrastra una imagen aquí</p>
                                                <p class="mb-0 small">o haz clic para seleccionar</p>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" class="d-none @error('imagen') is-invalid @enderror"
                                        wire:model="imagen" id="imagen" accept="image/*">
                                    @error('imagen')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Control de Stock debajo de la imagen -->
                                <div class="mb-3">
                                    <label class="form-label">Control de Stock</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="control" wire:model="control">
                                        <label class="form-check-label" for="control">
                                            {{ $control ? 'Activado' : 'Desactivado' }}
                                        </label>
                                    </div>
                                    <small class="text-muted">Si está desactivado, no se restará del stock al vender</small>
                                </div>
                            </div>

                            <!-- Columna Derecha: Formulario -->
                            <div class="col-md-8">
                                <div class="row">
                                    <!-- Nombre -->
                                    <div class="col-md-12 mb-3">
                                        <label for="nombre" class="form-label">Nombre <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('nombre') is-invalid @enderror"
                                            wire:model="nombre" id="nombre"
                                            placeholder="Ej: Cerveza Paceña 620ml">
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Categoría -->
                                    <div class="col-md-6 mb-3">
                                        <label for="categoria_id" class="form-label">Categoría <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            @if ($addingNewCategoria)
                                                <input type="text"
                                                    class="form-control @error('categoria_id') is-invalid @enderror"
                                                    wire:model="categoria_id" id="categoria_id"
                                                    placeholder="Ej: Bebidas">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    wire:click="toggleCategoriaInput" title="Volver al selector">
                                                    <i class="fa-solid fa-arrow-left"></i>
                                                </button>
                                            @else
                                                <select class="form-select @error('categoria_id') is-invalid @enderror"
                                                    wire:model="categoria_id" id="categoria_id">
                                                    <option value="">Seleccione una categoría</option>
                                                    @foreach ($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-outline-secondary" type="button"
                                                    wire:click="toggleCategoriaInput" title="Agregar nueva categoría">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @error('categoria_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Código -->
                                    <div class="col-md-6 mb-3">
                                        <label for="codigo" class="form-label">Código</label>
                                        <input type="text"
                                            class="form-control @error('codigo') is-invalid @enderror"
                                            wire:model="codigo" id="codigo" placeholder="Ej: CRV001">
                                        @error('codigo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Medida -->
                                    <div class="col-md-6 mb-3">
                                        <label for="medida" class="form-label">Medida <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            @if ($addingNewMedida)
                                                <input type="text"
                                                    class="form-control @error('medida') is-invalid @enderror"
                                                    wire:model="medida" id="medida" placeholder="Ej: six pack">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    wire:click="toggleMedidaInput" title="Volver al selector">
                                                    <i class="fa-solid fa-arrow-left"></i>
                                                </button>
                                            @else
                                                <select class="form-select @error('medida') is-invalid @enderror"
                                                    wire:model="medida" id="medida">
                                                    <option value="">Seleccione una medida</option>
                                                    @foreach ($medidas as $medida_item)
                                                        <option value="{{ $medida_item->nombre }}">
                                                            {{ ucfirst($medida_item->nombre) }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-outline-secondary" type="button"
                                                    wire:click="toggleMedidaInput" title="Agregar nueva medida">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @error('medida')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="col-md-6 mb-3">
                                        <label for="cantidad" class="form-label">Cantidad (unidades) <span
                                                class="text-danger">*</span></label>
                                        <input type="number"
                                            class="form-control @error('cantidad') is-invalid @enderror"
                                            wire:model="cantidad" id="cantidad" placeholder="Ej: 620">
                                        @error('cantidad')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Precio de Compra -->
                                    <div class="col-md-4 mb-3">
                                        <label for="precio_de_compra" class="form-label">Precio Compra (Bs.) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('precio_de_compra') is-invalid @enderror"
                                            wire:model="precio_de_compra" id="precio_de_compra" placeholder="0.00">
                                        @error('precio_de_compra')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Precio por Mayor -->
                                    <div class="col-md-4 mb-3">
                                        <label for="precio_por_mayor" class="form-label">Precio Mayor (Bs.) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('precio_por_mayor') is-invalid @enderror"
                                            wire:model="precio_por_mayor" id="precio_por_mayor" placeholder="0.00">
                                        @error('precio_por_mayor')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Precio por Menor -->
                                    <div class="col-md-4 mb-3">
                                        <label for="precio_por_menor" class="form-label">Precio Menor (Bs.) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('precio_por_menor') is-invalid @enderror"
                                            wire:model="precio_por_menor" id="precio_por_menor" placeholder="0.00">
                                        @error('precio_por_menor')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Tags / Nombres Alternativos -->
                                    <div class="col-md-12 mb-3" x-data="tagsInput()" wire:ignore>
                                        <label for="tags_input" class="form-label">
                                            <i class="fa-solid fa-tags me-1"></i>Tags / Nombres Alternativos
                                        </label>

                                        <!-- Área de tags como badges -->
                                        <div class="tags-container border rounded p-2 mb-2"
                                             style="min-height: 50px; cursor: text; background-color: #fff;"
                                             @click="$refs.tagInput.focus()">
                                            <template x-for="(tag, index) in tags" :key="index">
                                                <span class="badge bg-primary me-1 mb-1"
                                                      style="font-size: 0.875rem; padding: 0.4rem 0.6rem;">
                                                    <span x-text="tag"></span>
                                                    <button type="button" class="btn-close btn-close-white ms-2"
                                                            style="font-size: 0.6rem; padding: 0;"
                                                            @click.stop="removeTag(index)"
                                                            aria-label="Eliminar">
                                                    </button>
                                                </span>
                                            </template>
                                            <input type="text"
                                                   x-ref="tagInput"
                                                   x-model="currentTag"
                                                   @keydown.enter.prevent="addTag"
                                                   @keydown.comma.prevent="addTag"
                                                   class="border-0 outline-0"
                                                   style="outline: none; box-shadow: none; min-width: 200px;"
                                                   placeholder="Escribe y presiona coma o enter">
                                        </div>

                                        <!-- Input oculto para Livewire -->
                                        <input type="hidden" wire:model.defer="tags_input" x-ref="hiddenInput">

                                        @error('tags_input')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            Presiona <kbd>,</kbd> o <kbd>Enter</kbd> para agregar cada tag
                                        </small>
                                    </div>
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

    <!-- Componente anidado de Kardex Modal -->
    <livewire:kardex-modal />
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // Eventos de modal
        Livewire.on('showmodal', event => {
            $('#crudModal').modal('show')
            setTimeout(() => {
                document.getElementById('nombre').select()
            }, 500)
        })

        Livewire.on('closemodal', event => {
            $('#crudModal').modal('hide')
            document.getElementById('searchInput').focus()
        })

        Livewire.on('reset-tags', event => {
            // Disparar evento para limpiar tags en Alpine
            window.dispatchEvent(new CustomEvent('reset-tags-alpine'))
        })

        Livewire.on('load-tags', event => {
            // Disparar evento para cargar tags en Alpine
            window.dispatchEvent(new CustomEvent('load-tags-alpine'))
        })

        Livewire.on('medida-created', event => {
            Livewire.dispatch('$refresh')
        })
    })

    // Alpine.js component para tags profesionales
    function tagsInput() {
        return {
            tags: [],
            currentTag: '',

            init() {
                // Cargar tags iniciales desde Livewire
                this.loadTagsFromWire();

                // Escuchar evento para resetear tags
                window.addEventListener('reset-tags-alpine', () => {
                    this.resetTags();
                });

                // Escuchar evento para cargar tags (cuando se edita)
                window.addEventListener('load-tags-alpine', () => {
                    this.loadTagsFromWire();
                });

                // Escuchar eventos de Livewire para resetear o cargar tags
                window.addEventListener('tags-loaded', (event) => {
                    if (event.detail && event.detail.tags) {
                        this.$wire.tags_input = event.detail.tags;
                        this.loadTagsFromWire();
                    }
                });
            },

            loadTagsFromWire() {
                const tagsInput = this.$wire.tags_input;
                if (tagsInput && tagsInput.length > 0) {
                    this.tags = tagsInput.split(',').map(t => t.trim()).filter(t => t.length > 0);
                } else {
                    this.tags = [];
                }
            },

            resetTags() {
                this.tags = [];
                this.currentTag = '';
                this.$refs.hiddenInput.value = '';
            },

            addTag() {
                const tag = this.currentTag.trim();
                if (tag && !this.tags.includes(tag)) {
                    this.tags.push(tag);
                    this.currentTag = '';
                    this.updateHiddenInput();
                    // El foco se mantiene automáticamente porque no hay re-renderizado
                }
            },

            removeTag(index) {
                this.tags.splice(index, 1);
                this.updateHiddenInput();
            },

            updateHiddenInput() {
                // Actualizar directamente el valor del input hidden sin disparar Livewire
                const tagsString = this.tags.join(', ');
                this.$refs.hiddenInput.value = tagsString;
                this.$wire.tags_input = tagsString;
            }
        }
    }
</script>

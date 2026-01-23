<div>
    <div class="container-fluid">
        <div class="row starter-main">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header card-no-border pb-0">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Usuarios</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Buscar usuarios"
                                        wire:model.live="search" style="min-width: 200px;" id="searchInput" autofocus>
                                    <button class="btn btn-primary" wire:click="create">Agregar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3">
                        <div class="row g-2">
                            @forelse($usuarios as $usuario)
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="card mb-0 shadow-sm producto-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start position-relative">
                                                <!-- Botones en esquina superior derecha -->
                                                <div class="position-absolute top-0 end-0">
                                                    <button class="btn btn-link p-1 text-primary btn-zoom"
                                                        wire:click="edit({{ $usuario->id }})" title="Editar"
                                                        style="font-size: 1rem;">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-link p-1 text-danger btn-zoom"
                                                        wire:click="confirmDeleteUsuario({{ $usuario->id }})"
                                                        title="Eliminar" style="font-size: 1rem;">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>

                                                <!-- Imagen del usuario -->
                                                <div class="flex-shrink-0 me-3">
                                                    @if ($usuario->imagen)
                                                        <img src="{{ Storage::url($usuario->imagen) }}"
                                                            alt="{{ $usuario->name }}" class="rounded-circle"
                                                            style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #e9ecef;">
                                                    @else
                                                        <img src="{{ asset('assets/images/profile.png') }}"
                                                            alt="{{ $usuario->name }}" class="rounded-circle"
                                                            style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #e9ecef;">
                                                    @endif
                                                </div>

                                                <!-- Información del usuario -->
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">{{ $usuario->name }}</h6>
                                                    <div class="small">
                                                        <i class="fa-solid fa-phone text-primary me-1"></i>
                                                        <span class="text-muted">{{ $usuario->celular }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light py-2 px-3">
                                            <div class="d-flex justify-content-between align-items-center small">
                                                <div>
                                                    @if ($usuario->role === 'landlord')
                                                        <span class="badge bg-primary">
                                                            <i class="fa-solid fa-crown me-1"></i>Propietario
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fa-solid fa-user me-1"></i>Inquilino
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-muted">
                                                    <i class="fa-solid fa-calendar me-1"></i>{{ $usuario->created_at->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-user-slash fa-5x mb-3 text-muted"></i>
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
                <div class="w-100 d-flex justify-content-center justify-content-md-end">
                    {{ $usuarios->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal para Crear/Editar Usuario -->
    <div wire:ignore.self class="modal fade" id="crudModal" tabindex="-1" role="dialog" aria-labelledby="modalcrud">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Editar Usuario' : 'Nuevo Usuario' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="row">
                            <!-- Columna Izquierda: Imagen -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Foto de Perfil</label>
                                    <div class="image-upload-area" onclick="document.getElementById('imagen').click()"
                                        ondrop="handleDrop(event)" ondragover="handleDragOver(event)"
                                        ondragleave="handleDragLeave(event)"
                                        style="border: 2px dashed #ccc; border-radius: 8px; padding: 15px; text-align: center; cursor: pointer; height: 100%; min-height: 300px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                        @if ($imagen)
                                            <img src="{{ $imagen->temporaryUrl() }}" alt="Preview"
                                                style="max-width: 100%; max-height: 280px; object-fit: contain; border-radius: 8px;">
                                        @elseif ($editMode && isset($usuario_actual_imagen))
                                            <img src="{{ Storage::url($usuario_actual_imagen) }}" alt="Usuario"
                                                style="max-width: 100%; max-height: 280px; object-fit: contain; border-radius: 8px;">
                                        @else
                                            <div class="text-muted">
                                                <i class="fa-solid fa-user-circle fa-4x mb-2"></i>
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
                            </div>

                            <!-- Columna Derecha: Formulario -->
                            <div class="col-md-8">
                                <!-- Nombre -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" id="name" placeholder="Ej: Juan Pérez García">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Celular -->
                                <div class="mb-3">
                                    <label for="celular" class="form-label">Celular (8 dígitos) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('celular') is-invalid @enderror"
                                        wire:model="celular" id="celular" placeholder="Ej: 71234567" maxlength="8">
                                    @error('celular')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Rol -->
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror" wire:model="role" id="role">
                                        <option value="">Seleccione un rol</option>
                                        <option value="landlord">Propietario</option>
                                        <option value="tenant">Inquilino</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <!-- PIN -->
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">
                                            PIN (4 dígitos)
                                            @if(!$editMode)<span class="text-danger">*</span>@endif
                                        </label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            wire:model="password" id="password" placeholder="****" maxlength="4" inputmode="numeric">
                                        @if($editMode)
                                            <small class="form-text text-muted">Dejar en blanco para mantener el PIN actual</small>
                                        @endif
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Confirmar PIN -->
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">
                                            Confirmar PIN
                                            @if(!$editMode)<span class="text-danger">*</span>@endif
                                        </label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                            wire:model="password_confirmation" id="password_confirmation" placeholder="****" maxlength="4" inputmode="numeric">
                                        @error('password_confirmation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // Eventos de modal
        Livewire.on('showmodal', event => {
            $('#crudModal').modal('show')
            setTimeout(() => {
                document.getElementById('name').select()
            }, 500)
        })

        Livewire.on('closemodal', event => {
            $('#crudModal').modal('hide')
            document.getElementById('searchInput').focus()
        })
    })
</script>

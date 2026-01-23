<div>
    <div class="container-fluid">
        <div class="row starter-main">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header card-no-border pb-0">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Clientes</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Buscar clientes"
                                        wire:model.live="search" style="min-width: 200px;" id="searchInput" autofocus>
                                    <button class="btn btn-primary" wire:click="create">Agregar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-4">
                        <div class="row g-2">
                            @forelse($clientes as $cliente)
                                <div class="col-lg-4 col-md-6 col-sm-12" wire:key="cliente-{{ $cliente->id }}">
                                    <div class="card mb-0 shadow-sm producto-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="mb-0 fw-semibold">{{ $cliente->nombre }}</h5>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-link p-0 text-primary btn-zoom"
                                                        wire:click="edit({{ $cliente->id }})" title="Editar"
                                                        style="font-size: 1.1rem;">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-link p-0 text-danger btn-zoom"
                                                        wire:click="confirmDeleteCliente({{ $cliente->id }})"
                                                        title="Eliminar" style="font-size: 1.1rem;">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-3 text-muted small">
                                                <span>
                                                    <i class="fa-solid fa-phone me-1"></i>{{ $cliente->celular ?? '—' }}
                                                </span>
                                                <span>
                                                    <i class="fa-solid fa-id-card me-1"></i>{{ $cliente->nit ?? '—' }}
                                                </span>
                                                <span>
                                                    <i class="fa-solid fa-envelope me-1"></i>{{ $cliente->correo ?? '—' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-users fa-5x mb-3 text-muted"></i>
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

    <footer class="fixed-footer shadow-sm py-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted d-none d-md-block">Created By <a href="https://dieguitosoft.com"
                        target="_blank">DieguitoSoft.com</a></small>
                <div class="w-100 d-flex justify-content-center justify-content-md-end">
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal para Crear/Editar Cliente -->
    <div wire:ignore.self class="modal fade" id="crudModal" tabindex="-1" role="dialog" aria-labelledby="modalcrud">
        <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Editar Cliente' : 'Nuevo Cliente' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <!-- Nombre -->
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                    wire:model="nombre" id="nombre"
                                    placeholder="Ej: Juan Pérez García" autofocus>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <!-- Celular -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="celular" class="form-label">Celular <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('celular') is-invalid @enderror"
                                            wire:model="celular" id="celular"
                                            placeholder="Ej: 71234567">
                                        @error('celular')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- NIT -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nit" class="form-label">NIT / CI</label>
                                        <input type="text" class="form-control @error('nit') is-invalid @enderror"
                                            wire:model="nit" id="nit"
                                            placeholder="Ej: 1234567-1A">
                                        @error('nit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Correo -->
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control @error('correo') is-invalid @enderror"
                                    wire:model="correo" id="correo"
                                    placeholder="Ej: cliente@ejemplo.com">
                                @error('correo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
    })
</script>

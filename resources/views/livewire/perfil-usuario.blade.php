<div>
    <!-- Spinner overlay mientras se sube la imagen -->
    <div wire:loading.delay wire:target="imagen" class="loading-overlay" style="display: none;">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Subiendo imagen...</span>
            </div>
            <p class="mt-3 text-white fw-bold">Subiendo imagen...</p>
        </div>
    </div>

    <!-- Overlay para cerrar el sidebar al hacer click fuera -->
    @if ($mostrar)
        <div class="profile-sidebar-overlay" wire:click="toggleSidebar"></div>
    @endif

    <!-- Sidebar derecho del perfil -->
    <div class="profile-sidebar {{ $mostrar ? 'active' : '' }}">
        <div class="profile-sidebar-header">
            <h5 class="mb-0 fw-bold">Mi Perfil</h5>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('logout') }}" class="btn-logout"
                    onclick="event.preventDefault(); document.getElementById('logout-form-profile').submit();"
                    title="Cerrar Sesión">
                    <i class="fa-solid fa-power-off"></i>
                </a>
                <button type="button" class="btn-close-sidebar" wire:click="toggleSidebar">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <form id="logout-form-profile" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>

        <div class="profile-sidebar-body">
            <!-- Botones de acción (solo visible en móvil) -->
            <div class="profile-action-buttons d-md-none mb-3">
                <!-- Botón selector de tenant -->
                @php
                    $currentTenant = currentTenant();
                    $tenantColor = getThemeColor();
                @endphp
                <div class="btn-group" role="group" aria-label="Button group">
                <button class="btn btn-tenant-selector-mobile" onclick="Livewire.dispatch('openTenantSelector')"
                    title="{{ $currentTenant?->name ?? 'Cambiar tienda' }}" style="background: {{ $tenantColor }};">
                    <i class="fa-solid fa-store"></i>
                    <span>Cambiar Tienda</span>
                </button>

                <!-- Botón de modo Landlord (solo para Super Admins) -->
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.home') }}" class="btn btn-mode-switch-mobile"
                        title="Ir a modo Landlord (Gestión del Sistema)">
                        <span>Cambiar Modo</span>
                        <i class="fa-solid fa-crown"></i>
                    </a>
                @endif
                </div>
            </div>

            <!-- Nombre del tenant activo -->
            @if (currentTenant())
                <div class="tenant-badge-profile text-center mb-3">
                    <div class="badge"
                        style="background: {{ getThemeColor() }}; color: white; padding: 8px 16px; font-size: 0.875rem; border-radius: 20px;">
                        <i class="fa-solid fa-store me-1"></i>
                        {{ currentTenant()->name }}
                    </div>
                </div>
            @endif

            <!-- Foto de perfil -->
            <div class="profile-avatar-section text-center mb-4" x-data>
                <div class="profile-avatar-large" @click="$refs.imagenInput.click()"
                    style="cursor: pointer; position: relative;">
                    @if ($imagen)
                        <img src="{{ $imagen->temporaryUrl() }}" alt="user" />
                    @else
                        <img src="{{ Auth::user()->photo_url }}" alt="user" />
                    @endif
                    <div class="avatar-overlay">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>
                <input type="file" x-ref="imagenInput" wire:model="imagen" accept="image/*" style="display: none;">
                @if (!$editando)
                    <h6 class="mt-3 mb-1 fw-bold">{{ Auth::user()->name }}</h6>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <small
                            class="text-muted">{{ ucfirst(Auth::user()->roleInCurrentTenant() ?? 'usuario') }}</small>
                        <button type="button" class="btn btn-link p-0 text-primary" wire:click="toggleEditar"
                            title="Editar perfil">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>
                @endif
                @error('imagen')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>

            <!-- Formulario de perfil -->
            <div class="profile-form">
                @if ($editando)
                    <!-- Modo edición -->
                    <form wire:submit.prevent="guardar">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                wire:model="nombre" placeholder="Nombre completo">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Celular</label>
                            <input type="text" class="form-control @error('celular') is-invalid @enderror"
                                wire:model="celular" placeholder="Número de celular" autocomplete="tel">
                            @error('celular')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nuevo PIN (4 dígitos, opcional)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                wire:model="password" placeholder="••••" maxlength="4" autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmar PIN</label>
                            <input type="password" class="form-control" wire:model="password_confirmation"
                                placeholder="••••" maxlength="4" autocomplete="new-password">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fa-solid fa-save me-1"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-secondary flex-fill" wire:click="cancelar">
                                <i class="fa-solid fa-times me-1"></i> Cancelar
                            </button>
                        </div>
                    </form>
                @else
                    <!-- Modo visualización -->
                    <div class="profile-info">
                        <div class="info-item mb-3">
                            <label class="text-muted small mb-1">Nombre</label>
                            <div class="fw-semibold">{{ Auth::user()->name }}</div>
                        </div>

                        <div class="info-item mb-3">
                            <label class="text-muted small mb-1">Celular</label>
                            <div class="fw-semibold">{{ Auth::user()->celular }}</div>
                        </div>

                        <div class="info-item mb-3">
                            <label class="text-muted small mb-1">PIN</label>
                            <div class="fw-semibold">••••</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Botones de acción en sidebar del perfil (solo móvil) */
        .profile-action-buttons {
            display: flex;
            flex-direction: row;
            gap: 10px;
            padding: 0 15px;
        }

        .btn-tenant-selector-mobile,
        .btn-mode-switch-mobile {
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 25px;
            border: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            flex: 1;
        }

        .btn-tenant-selector-mobile {
            background: var(--theme-default);
        }

        .btn-mode-switch-mobile {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-tenant-selector-mobile:hover,
        .btn-mode-switch-mobile:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .btn-tenant-selector-mobile i,
        .btn-mode-switch-mobile i {
            font-size: 18px;
        }
    </style>
</div>

@script
    <script>
        Livewire.on('togglePerfilSidebar', () => {
            @this.call('toggleSidebar');
        });
    </script>
@endscript

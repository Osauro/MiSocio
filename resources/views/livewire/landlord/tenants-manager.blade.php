<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">
                                <i class="fa-solid fa-store me-2"></i>
                                Gestión de Tenants
                            </h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    <input type="text" class="form-control text-start" placeholder="Buscar tenants"
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
                            <input type="text" class="form-control text-start" placeholder="Buscar tenants"
                                wire:model.live="search">
                            <button class="btn btn-primary" wire:click="create">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-2 pb-3">
                        <!-- Grid de tarjetas -->
                        <div class="row g-1">
                            @forelse($tenants as $tenant)
                                @php
                                    $diasRestantes = $tenant->bill_date ? \Carbon\Carbon::now()->diffInDays($tenant->bill_date, false) : null;
                                    $badgeColor = $diasRestantes === null ? 'secondary' : ($diasRestantes < 0 ? 'danger' : ($diasRestantes <= 7 ? 'warning' : 'success'));
                                @endphp
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-3">
                                    <div class="card h-100 border-2 {{ $tenant->status ? 'border-success' : 'border-danger' }}">
                                        <!-- Header con botones -->
                                        <div class="card-header {{ $tenant->status ? 'bg-success' : 'bg-danger' }} text-white py-1 px-2">
                                            <div class="d-flex justify-content-between align-items-center gap-1">
                                                <div class="d-flex align-items-center gap-1">
                                                    <div class="rounded-circle"
                                                        style="width: 20px; height: 20px; background: linear-gradient(135deg, {{ getThemeColor($tenant->theme_number) }}, {{ getThemeColor($tenant->theme_number) }}80);">
                                                    </div>
                                                    <small class="mb-0 fw-bold text-truncate" style="font-size: 0.72rem;">{{ $tenant->name }}</small>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-light btn-sm py-0 px-1 text-dark" style="font-size: 0.65rem;"
                                                        wire:click="edit({{ $tenant->id }})" title="Editar">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm py-0 px-1" style="font-size: 0.65rem;"
                                                        onclick="confirm('¿Eliminar?') || event.stopImmediatePropagation()"
                                                        wire:click="delete({{ $tenant->id }})" title="Eliminar">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Cuerpo compacto -->
                                        <div class="card-body py-1 px-2" style="font-size: 0.75rem;">
                                            <!-- ID -->
                                            <div class="mb-1">
                                                <small class="text-muted" style="font-size: 0.65rem;">ID: #{{ $tenant->id }}</small>
                                            </div>

                                            <!-- Dominio -->
                                            @if($tenant->domain)
                                                <div class="mb-1">
                                                    <span class="badge bg-info" style="font-size: 0.6rem;">
                                                        <i class="fa-solid fa-globe"></i> {{ Str::limit($tenant->domain, 15) }}
                                                    </span>
                                                </div>
                                            @endif

                                            <!-- Plan y Monto -->
                                            <div class="row g-1 mb-1">
                                                <div class="col-6">
                                                    <div class="text-center p-1 bg-light rounded" style="font-size: 0.7rem;">
                                                        <small class="text-muted d-block" style="font-size: 0.6rem;">Plan</small>
                                                        <span class="badge bg-primary" style="font-size: 0.6rem;">{{ ucfirst($tenant->subscription_type) }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-center p-1 bg-light rounded" style="font-size: 0.7rem;">
                                                        <small class="text-muted d-block" style="font-size: 0.6rem;">Monto</small>
                                                        <strong class="text-success" style="font-size: 0.75rem;">Bs. {{ number_format($tenant->amount, 0) }}</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Próximo Pago -->
                                            @if($tenant->bill_date)
                                                <div class="mb-1">
                                                    <span class="badge bg-{{ $badgeColor }}" style="font-size: 0.6rem;">
                                                        <i class="fa-solid fa-calendar-days"></i>
                                                        @if($diasRestantes < 0)
                                                            Vencido
                                                        @elseif($diasRestantes == 0)
                                                            Hoy
                                                        @else
                                                            {{ $diasRestantes }}d
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif

                                            <!-- Usuarios y Estado -->
                                            <div class="row g-1">
                                                <div class="col-6">
                                                    <div class="text-center p-1 bg-light rounded" style="font-size: 0.7rem;">
                                                        <small class="text-muted d-block" style="font-size: 0.6rem;">Usuarios</small>
                                                        <strong class="text-dark">{{ $tenant->users_count }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-center p-1 bg-light rounded" style="font-size: 0.7rem;">
                                                        <small class="text-muted d-block" style="font-size: 0.6rem;">Estado</small>
                                                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                            <input class="form-check-input" type="checkbox" style="font-size: 0.7rem;"
                                                                wire:click="toggleStatus({{ $tenant->id }})"
                                                                {{ $tenant->status ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa-solid fa-store-slash fa-5x mb-3 text-muted"></i>
                                    <p class="h5 text-muted mb-0">No se encontraron tenants</p>
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
                <small class="text-muted d-none d-md-block">Created By <a href="https://dieguitosoft.com"
                        target="_blank">DieguitoSoft.com</a></small>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" class="form-control form-control-sm text-center" style="width: 60px;"
                        wire:model.live="perPage" min="1" max="100" title="Registros por página">
                    {{ $tenants->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal para Crear/Editar -->
    @if ($modalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-store me-2"></i>
                            {{ $tenantId ? 'Editar Tenant' : 'Nuevo Tenant' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Información del Tenant -->
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">
                                    <i class="fa-solid fa-circle-info me-2"></i>
                                    Información del Tenant
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="name">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Dominio</label>
                                <input type="text" class="form-control" wire:model="domain"
                                    placeholder="ejemplo.com">
                                @error('domain') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tema <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="theme_number">
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}">Tema {{ $i }}</option>
                                    @endfor
                                </select>
                                @error('theme_number') <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="status">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <!-- Suscripción -->
                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2">
                                    <i class="fa-solid fa-credit-card me-2"></i>
                                    Suscripción
                                </h6>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Seleccionar Plan (Opcional)</label>
                                <select class="form-select" wire:model.live="plan_suscripcion_id">
                                    <option value="">-- Configuración manual --</option>
                                    @foreach($planes as $plan)
                                        <option value="{{ $plan->id }}">
                                            {{ $plan->nombre }} - Bs. {{ number_format($plan->precio, 2) }}
                                            @if($plan->duracion_meses > 0)
                                                ({{ $plan->duracion_texto }})
                                            @else
                                                (Demo)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_suscripcion_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                <small class="text-muted">
                                    <i class="fa-solid fa-info-circle"></i>
                                    Selecciona un plan predefinido o configura manualmente los campos de abajo
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tipo de Suscripción <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="subscription_type">
                                    <option value="demo">Demo</option>
                                    <option value="mensual">Mensual</option>
                                    <option value="trimestral">Trimestral</option>
                                    <option value="semestral">Semestral</option>
                                    <option value="anual">Anual</option>
                                </select>
                                @error('subscription_type') <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Monto (Bs.) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" wire:model="amount" step="0.01" min="0">
                                @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Fecha de Próximo Pago</label>
                                <input type="date" class="form-control" wire:model="bill_date">
                                @error('bill_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <!-- Usuario Administrador (solo al crear) -->
                            @if(!$tenantId)
                                <div class="col-12 mt-4">
                                    <h6 class="border-bottom pb-2">
                                        <i class="fa-solid fa-user-shield me-2"></i>
                                        Administrador del Tenant
                                    </h6>
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model.live="create_admin"
                                            id="createAdmin">
                                        <label class="form-check-label" for="createAdmin">
                                            Crear nuevo usuario administrador
                                        </label>
                                    </div>
                                </div>

                                @if($create_admin)
                                    <div class="col-md-12">
                                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" wire:model="admin_name">
                                        @error('admin_name') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" wire:model="admin_email">
                                        @error('admin_email') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" wire:model="admin_password">
                                        @error('admin_password') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @else
                                    <div class="col-12">
                                        <label class="form-label">Seleccionar usuario existente <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" wire:model="selected_admin_id">
                                            <option value="">Seleccione un usuario</option>
                                            @foreach($availableAdmins as $admin)
                                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('selected_admin_id') <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="fa-solid fa-save me-1"></i>
                            {{ $tenantId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

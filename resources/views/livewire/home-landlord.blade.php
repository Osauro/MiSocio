<div>
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Panel de Administración</a></li>
            </ol>
        </div>

        <!-- Tarjeta de bienvenida -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-crown fa-4x opacity-50"></i>
                            </div>
                            <div class="flex-grow-1 ms-4">
                                <h3 class="mb-2">Bienvenido al Panel de Administración</h3>
                                <p class="mb-0 opacity-75">
                                    Modo Super Admin - Gestión completa del sistema
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas generales -->
        <div class="row">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Total Tenants</h6>
                                <h3 class="mb-0">{{ \App\Models\Tenant::count() }}</h3>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                    <i class="fa-solid fa-store"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Total Usuarios</h6>
                                <h3 class="mb-0">{{ \App\Models\User::count() }}</h3>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                    <i class="fa-solid fa-users"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Tenants Activos</h6>
                                <h3 class="mb-0">{{ \App\Models\Tenant::where('status', 'active')->count() }}</h3>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                                    <i class="fa-solid fa-check-circle"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Super Admins</h6>
                                <h3 class="mb-0">{{ \App\Models\User::where('is_super_admin', true)->count() }}</h3>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-3">
                                    <i class="fa-solid fa-crown"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Funcionalidades del Sistema</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fa-solid fa-info-circle me-2"></i>
                            Módulos en desarrollo. Próximamente podrás gestionar:
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <i class="fa-solid fa-money-bill-wave fa-2x text-primary mb-2"></i>
                                    <h6>Gestión de Suscripciones</h6>
                                    <small class="text-muted">Control de pagos y planes</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <i class="fa-solid fa-building fa-2x text-success mb-2"></i>
                                    <h6>Administración de Tenants</h6>
                                    <small class="text-muted">CRUD completo de tiendas</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <i class="fa-solid fa-chart-line fa-2x text-info mb-2"></i>
                                    <h6>Reportes Globales</h6>
                                    <small class="text-muted">Estadísticas del sistema</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="text-muted mb-2">¿Quieres trabajar en un tenant específico?</p>
                            <button onclick="Livewire.dispatch('openTenantSelector')" class="btn btn-primary">
                                <i class="fa-solid fa-store me-2"></i>Seleccionar Tenant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

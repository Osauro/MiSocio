<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <!-- Header con botón de crear -->
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="mb-0">
                                <i class="fa-solid fa-credit-card me-2"></i>
                                Mis Suscripciones
                            </h3>
                            <button wire:click="crearTenant" class="btn btn-primary">
                                <i class="fa-solid fa-plus me-1"></i>
                                Nueva Tienda
                            </button>
                        </div>
                    </div>

                    <div class="card-body pt-3">
                        @if($yaExisteDemo)
                            <div class="alert alert-warning mb-3">
                                <i class="fa-solid fa-info-circle me-2"></i>
                                <strong>Nota:</strong> El plan Demo solo está disponible para nuevos usuarios sin tiendas activas. Puedes crear tiendas adicionales con planes de pago.
                            </div>
                        @else
                            <div class="alert alert-success mb-3">
                                <i class="fa-solid fa-gift me-2"></i>
                                <strong>¡Tienes disponible 1 tienda Demo gratuita!</strong> Créala para probar el sistema por 15 días.
                            </div>
                        @endif

                        <!-- Mis Tiendas -->
                        <div class="card border shadow-sm mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="fa-solid fa-building me-2"></i>
                                    Todas Mis Tiendas ({{ $misTenants->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($misTenants->count() > 0)
                                    <div class="row">
                                        @foreach($misTenants as $t)
                                            @php
                                                $diasRestantesTenant = $t->bill_date ? now()->diffInDays($t->bill_date, false) : null;
                                                $estadoTenant = $diasRestantesTenant < 0 ? 'vencida' :
                                                    ($diasRestantesTenant <= 7 ? 'por-vencer' : 'activa');
                                                $colorTenant = $estadoTenant === 'activa' ? 'success' :
                                                    ($estadoTenant === 'por-vencer' ? 'warning' : 'danger');
                                                $esActual = $tenant && $tenant->id === $t->id;
                                            @endphp

                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100 {{ $esActual ? 'border-primary border-3' : '' }} shadow-sm">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                                    <i class="fa-solid fa-store" style="color: {{ getThemeColor($t->theme_number) }}; font-size: 1.5rem;"></i>
                                                                    <h5 class="mb-0">{{ $t->name }}</h5>
                                                                </div>
                                                                @if($esActual)
                                                                    <span class="badge bg-primary mt-1">
                                                                        <i class="fa-solid fa-circle-check"></i> Tienda Activa
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <span class="badge bg-{{ $colorTenant }}">
                                                                {{ ucfirst($estadoTenant) }}
                                                            </span>
                                                        </div>

                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="fa-solid fa-tag"></i>
                                                                Plan: <strong>{{ ucfirst($t->subscription_type) }}</strong>
                                                            </small>
                                                        </div>

                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="fa-solid fa-coins"></i>
                                                                <strong>Bs. {{ number_format($t->amount, 2) }}</strong>
                                                            </small>
                                                        </div>

                                                        @if($t->bill_date)
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <i class="fa-solid fa-calendar"></i>
                                                                    Vence: {{ $t->bill_date->format('d/m/Y') }}
                                                                    @if($diasRestantesTenant >= 0)
                                                                        <span class="badge bg-{{ $colorTenant }} ms-1">
                                                                            {{ $diasRestantesTenant }} días
                                                                        </span>
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        @endif

                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="fa-solid fa-users"></i>
                                                                {{ $t->users_count }} usuario(s)
                                                            </small>
                                                        </div>

                                                        <div class="mt-3">
                                                            @if(!$esActual)
                                                                <button wire:click="cambiarTenant({{ $t->id }})"
                                                                    class="btn btn-sm btn-outline-primary w-100">
                                                                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                                                    Cambiar a esta tienda
                                                                </button>
                                                            @else
                                                                <a href="{{ route('home') }}" class="btn btn-sm btn-primary w-100">
                                                                    <i class="fa-solid fa-house"></i>
                                                                    Ir al Dashboard
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fa-solid fa-store-slash fa-5x mb-3 text-muted"></i>
                                        <h5 class="text-muted">No tienes tiendas creadas</h5>
                                        <p class="text-muted">Crea tu primera tienda para comenzar</p>
                                        <button wire:click="crearTenant" class="btn btn-primary">
                                            <i class="fa-solid fa-plus me-1"></i>
                                            Crear Mi Primera Tienda
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Información -->
                        <div class="alert alert-light border">
                            <h6 class="alert-heading">
                                <i class="fa-solid fa-lightbulb me-2"></i>
                                Información Importante
                            </h6>
                            <hr>
                            <ul class="mb-0">
                                <li><strong>Tienda Demo:</strong> Puedes crear 1 tienda gratis por 15 días para probar todas las funcionalidades.</li>
                                <li><strong>Planes de Pago:</strong> Crea tiendas ilimitadas eligiendo un plan mensual, trimestral, semestral o anual.</li>
                                <li><strong>Cambio de Tienda:</strong> Puedes cambiar entre tus tiendas en cualquier momento desde aquí.</li>
                                <li><strong>Renovación:</strong> Las tiendas de pago se renuevan automáticamente si el pago está verificado.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Tenant -->
    @if($modalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-plus-circle me-2"></i>
                            Crear Nueva Tienda
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="guardarTenant">
                            <div class="row g-3">
                                <!-- Nombre -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">
                                        <i class="fa-solid fa-store me-1"></i>
                                        Nombre de la Tienda <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" wire:model="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="Ej: Mi Tienda de Ropa">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Tema -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">
                                        <i class="fa-solid fa-palette me-1"></i>
                                        Tema de Color <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="theme_number" class="form-select @error('theme_number') is-invalid @enderror">
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}">Tema {{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('theme_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Dominio -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">
                                        <i class="fa-solid fa-globe me-1"></i>
                                        Dominio (Opcional)
                                    </label>
                                    <input type="text" wire:model="domain"
                                        class="form-control @error('domain') is-invalid @enderror"
                                        placeholder="mitienda.com">
                                    @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Puedes configurar esto después</small>
                                </div>

                                <!-- Separador -->
                                <div class="col-12">
                                    <hr>
                                    <h6 class="text-primary">
                                        <i class="fa-solid fa-credit-card me-2"></i>
                                        Selecciona tu Plan
                                    </h6>
                                </div>

                                <!-- Seleccionar Plan -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">Plan de Suscripción</label>
                                    <select wire:model.live="plan_suscripcion_id" class="form-select">
                                        <option value="">-- Configuración Manual --</option>
                                        @foreach($planes as $plan)
                                            <option value="{{ $plan->id }}">
                                                {{ $plan->nombre }} - Bs. {{ number_format($plan->precio, 2) }}
                                                @if($plan->duracion_meses > 0)
                                                    ({{ $plan->duracion_texto }})
                                                @else
                                                    (15 días de prueba)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tipo de Suscripción Manual -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">
                                        <i class="fa-solid fa-tag me-1"></i>
                                        Tipo de Suscripción <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="subscription_type" class="form-select @error('subscription_type') is-invalid @enderror">
                                        @if(!$yaExisteDemo)
                                        <option value="demo">
                                            Demo (Gratis - 15 días)
                                        </option>
                                        @endif
                                        <option value="mensual">Mensual (Bs. 120/mes)</option>
                                        <option value="trimestral">Trimestral (Bs. 330/3 meses)</option>
                                        <option value="semestral">Semestral (Bs. 630/6 meses)</option>
                                        <option value="anual">Anual (Bs. 1,200/año)</option>
                                    </select>
                                    @error('subscription_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Info del Plan Seleccionado -->
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fa-solid fa-info-circle me-2"></i>
                                        @if($subscription_type === 'demo')
                                            <strong>Plan Demo:</strong> Tu tienda estará activa por 15 días con todas las funcionalidades. Solo puedes tener 1 tienda demo.
                                        @elseif($subscription_type === 'mensual')
                                            <strong>Plan Mensual:</strong> Pago de Bs. 120 cada mes.
                                        @elseif($subscription_type === 'trimestral')
                                            <strong>Plan Trimestral:</strong> Pago de Bs. 330 cada 3 meses. ¡Ahorra 8%!
                                        @elseif($subscription_type === 'semestral')
                                            <strong>Plan Semestral:</strong> Pago de Bs. 630 cada 6 meses. ¡Ahorra 12%!
                                        @elseif($subscription_type === 'anual')
                                            <strong>Plan Anual:</strong> Pago de Bs. 1,200 por año. ¡Ahorra 17%!
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i>
                            Cancelar
                        </button>
                        <button type="button" wire:click="guardarTenant" class="btn btn-primary">
                            <i class="fa-solid fa-check me-1"></i>
                            Crear Tienda
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

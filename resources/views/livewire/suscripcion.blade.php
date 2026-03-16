<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <!-- Header con bot├│n de crear -->
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
                        @if($misTenants->count() > 0)
                                    <div class="row">
                                        @foreach($misTenants as $t)
                                            @php
                                                $diasRestantesTenant = $t->bill_date ? (int) now()->diffInDays($t->bill_date, false) : null;
                                                $estadoTenant = $diasRestantesTenant < 0 ? 'vencida' :
                                                    ($diasRestantesTenant <= 7 ? 'por-vencer' : 'activa');
                                                $colorTenant = $estadoTenant === 'activa' ? 'success' :
                                                    ($estadoTenant === 'por-vencer' ? 'warning' : 'danger');
                                                $esActual = $tenant && $tenant->id === $t->id;
                                            @endphp

                                            <div class="col-md-6 col-lg-4 mb-3">
                                                @php
                                                    $themeColor = getThemeColor($t->theme_number);
                                                    $cardStyle = $esActual
                                                        ? "border: 1.5px solid {$themeColor}; background-color: {$themeColor}18;"
                                                        : "border: 1px solid #dee2e6;";
                                                @endphp
                                                <div class="card h-100 shadow-sm" style="{{ $cardStyle }}">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                                    <i class="fa-solid fa-store" style="color: {{ getThemeColor($t->theme_number) }}; font-size: 1.5rem;"></i>
                                                                    <h5 class="mb-0">{{ $t->name }}</h5>
                                                                </div>

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
                                                                <strong>Bs. {{ number_format($t->planSuscripcion->precio ?? $t->amount, 2) }}</strong>
                                                                @if($t->planSuscripcion)
                                                                    <span class="text-muted">/ {{ $t->subscription_type }}</span>
                                                                @endif
                                                            </small>
                                                        </div>

                                                        @if($t->bill_date)
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <i class="fa-solid fa-calendar"></i>
                                                                    Vence: {{ $t->bill_date->format('d/m/Y') }}
                                                                    @if($diasRestantesTenant >= 0)
                                                                        <span class="badge bg-{{ $colorTenant }} ms-1">
                                                                            {{ (int) $diasRestantesTenant }} d├¡as
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

                                                        <div class="mt-3 d-flex flex-column gap-2">
                                                            @if(in_array($estadoTenant, ['vencida', 'por-vencer']))
                                                                <button wire:click="abrirModalRenovar({{ $t->id }})"
                                                                    class="btn btn-sm {{ $estadoTenant === 'vencida' ? 'btn-danger' : 'btn-warning' }} w-100">
                                                                    <i class="fa-solid fa-rotate me-1"></i>
                                                                    Renovar Suscripci├│n
                                                                </button>
                                                            @endif
                                                            @if($estadoTenant !== 'vencida')
                                                                @if(!$esActual)
                                                                    <button wire:click="cambiarTenant({{ $t->id }})"
                                                                        class="btn btn-sm btn-outline-primary w-100">
                                                                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                                                        Cambiar a esta tienda
                                                                    </button>
                                                                @else
                                                                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary w-100">
                                                                        <i class="fa-solid fa-house"></i>
                                                                        Ir al Dashboard
                                                                    </a>
                                                                @endif
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
                                    <small class="text-muted">Puedes configurar esto despu├®s</small>
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
                                    <label class="form-label fw-bold">Plan de Suscripci├│n</label>
                                    <select wire:model.live="plan_suscripcion_id" class="form-select">
                                        <option value="">-- Configuraci├│n Manual --</option>
                                        @foreach($planes as $plan)
                                            <option value="{{ $plan->id }}">
                                                {{ $plan->nombre }} - Bs. {{ number_format($plan->precio, 2) }}
                                                @if($plan->duracion_meses > 0)
                                                    ({{ $plan->duracion_texto }})
                                                @else
                                                    (15 d├¡as de prueba)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tipo de Suscripci├│n Manual -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">
                                        <i class="fa-solid fa-tag me-1"></i>
                                        Tipo de Suscripci├│n <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="subscription_type" class="form-select @error('subscription_type') is-invalid @enderror">
                                        @if(!$yaExisteDemo)
                                        <option value="demo">
                                            Demo (Gratis - 15 d├¡as)
                                        </option>
                                        @endif
                                        <option value="mensual">Mensual (Bs. 120/mes)</option>
                                        <option value="trimestral">Trimestral (Bs. 330/3 meses)</option>
                                        <option value="semestral">Semestral (Bs. 630/6 meses)</option>
                                        <option value="anual">Anual (Bs. 1,200/a├▒o)</option>
                                    </select>
                                    @error('subscription_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Info del Plan Seleccionado -->
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fa-solid fa-info-circle me-2"></i>
                                        @if($subscription_type === 'demo')
                                            <strong>Plan Demo:</strong> Tu tienda estar├í activa por 15 d├¡as con todas las funcionalidades. Solo puedes tener 1 tienda demo.
                                        @elseif($subscription_type === 'mensual')
                                            <strong>Plan Mensual:</strong> Pago de Bs. 120 cada mes.
                                        @elseif($subscription_type === 'trimestral')
                                            <strong>Plan Trimestral:</strong> Pago de Bs. 330 cada 3 meses. ┬íAhorra 8%!
                                        @elseif($subscription_type === 'semestral')
                                            <strong>Plan Semestral:</strong> Pago de Bs. 630 cada 6 meses. ┬íAhorra 12%!
                                        @elseif($subscription_type === 'anual')
                                            <strong>Plan Anual:</strong> Pago de Bs. 1,200 por a├▒o. ┬íAhorra 17%!
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

    {{-- ÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉ
         OVERLAY WIZARD: RENOVAR SUSCRIPCI├ôN (2 PASOS)
         ÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉÔòÉ --}}
    @if($renovarModalOpen)
        @php $tenantRenovar = $misTenants->firstWhere('id', $renovarTenantId); @endphp

        <div style="position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.72);"
             class="d-flex align-items-center justify-content-center p-2">

            <div style="background:#fff; border-radius:18px; width:100%; max-width:960px;
                        max-height:92vh; overflow-y:auto;
                        box-shadow:0 24px 70px rgba(0,0,0,0.45);">

                {{-- ÔöÇÔöÇ HEADER ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ --}}
                <div class="d-flex align-items-center justify-content-between px-4 py-3"
                     style="background:linear-gradient(135deg,#f5a623,#e07b00); border-radius:18px 18px 0 0;">
                    <div>
                        <h4 class="text-white fw-bold mb-0">
                            <i class="fa-solid fa-rotate me-2"></i>Renovar Suscripci├│n
                        </h4>
                        @if($tenantRenovar)
                            <small class="text-white" style="opacity:.8;">{{ $tenantRenovar->name }}</small>
                        @endif
                    </div>
                    <button class="btn btn-light btn-sm px-3" wire:click="closeRenovarModal">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                {{-- ÔöÇÔöÇ STEP INDICATOR ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ --}}
                <div class="px-4 pt-3">
                    <div class="d-flex align-items-center">

                        {{-- Paso 1 --}}
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                 style="width:34px; height:34px; font-size:.85rem;
                                        background:{{ $renovarPaso > 1 ? '#198754' : '#0d6efd' }};
                                        color:#fff;">
                                @if($renovarPaso > 1)
                                    <i class="fa-solid fa-check" style="font-size:.75rem;"></i>
                                @else
                                    1
                                @endif
                            </div>
                            <span class="fw-semibold {{ $renovarPaso === 1 ? 'text-primary' : 'text-success' }}"
                                  style="font-size:.9rem;">Seleccionar Plan</span>
                        </div>

                        {{-- L├¡nea conectora --}}
                        <div class="flex-grow-1 mx-3"
                             style="height:3px; border-radius:2px;
                                    background:{{ $renovarPaso >= 2 ? '#0d6efd' : '#dee2e6' }};"></div>

                        {{-- Paso 2 --}}
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                 style="width:34px; height:34px; font-size:.85rem;
                                        background:{{ $renovarPaso === 2 ? '#0d6efd' : '#dee2e6' }};
                                        color:{{ $renovarPaso === 2 ? '#fff' : '#6c757d' }};">
                                2
                            </div>
                            <span class="fw-semibold {{ $renovarPaso === 2 ? 'text-primary' : 'text-muted' }}"
                                  style="font-size:.9rem;">Pago y Comprobante</span>
                        </div>

                    </div>
                    <hr class="mt-3 mb-0">
                </div>

                {{-- ÔöÇÔöÇ BODY ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ --}}
                <div class="p-4">

                    {{-- ÔòÉÔòÉÔòÉÔòÉ PASO 1: Seleccionar Plan ÔòÉÔòÉÔòÉÔòÉ --}}
                    @if($renovarPaso === 1)

                        <p class="text-muted mb-4">
                            <i class="fa-solid fa-hand-pointer me-1"></i>
                            Elige el plan con el que deseas renovar. Puedes cambiar a cualquier plan disponible.
                        </p>

                        <div class="row g-3 mb-3">
                            @foreach($planes as $plan)
                                @php $sel = ($renovarPlanId == $plan->id); @endphp
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card h-100 {{ $sel ? 'border-primary border-3 shadow' : 'border' }}"
                                         wire:click="$set('renovarPlanId', {{ $plan->id }})"
                                         style="cursor:pointer; transition:all .15s;
                                                {{ $sel ? 'box-shadow:0 0 0 3px rgba(13,110,253,.25) !important;' : '' }}">
                                        <div class="card-body text-center py-3 px-2">
                                            <div style="height:24px;" class="mb-1">
                                                @if($sel)
                                                    <span class="badge bg-primary small">
                                                        <i class="fa-solid fa-check me-1"></i>Seleccionado
                                                    </span>
                                                @endif
                                            </div>
                                            <h6 class="fw-bold mb-1">{{ $plan->nombre }}</h6>
                                            <h4 class="fw-bold mb-0" style="color:var(--theme-default,#0d6efd);">
                                                Bs. {{ number_format($plan->precio, 2) }}
                                            </h4>
                                            <small class="text-muted">{{ $plan->duracion_texto }}</small>
                                            @if($plan->descripcion)
                                                <p class="text-muted mt-2 mb-0" style="font-size:.76rem;">{{ $plan->descripcion }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('renovarPlanId')
                            <div class="alert alert-danger py-2 mb-3">
                                <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                            </div>
                        @enderror

                    {{-- ÔòÉÔòÉÔòÉÔòÉ PASO 2: QR + Comprobante ÔòÉÔòÉÔòÉÔòÉ --}}
                    @elseif($renovarPaso === 2)

                        {{-- Resumen del plan elegido --}}
                        @if($planRenovar)
                            <div class="alert alert-success d-flex align-items-center gap-3 py-2 mb-4">
                                <i class="fa-solid fa-circle-check fa-xl"></i>
                                <div>
                                    <span class="fw-bold">{{ $planRenovar->nombre }}</span>
                                    &nbsp;&mdash;&nbsp;Bs. <strong>{{ number_format($planRenovar->precio, 2) }}</strong>
                                    &nbsp;&mdash;&nbsp;{{ $planRenovar->duracion_texto }}
                                </div>
                            </div>
                        @endif

                        <div class="row g-4">

                            {{-- QR de pago --}}
                            <div class="col-md-5">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header text-white fw-bold"
                                         style="background:#0d6efd; border-radius:8px 8px 0 0;">
                                        <i class="fa-solid fa-qrcode me-2"></i>Escanea para Pagar
                                    </div>
                                    <div class="card-body d-flex flex-column align-items-center
                                                justify-content-center text-center py-4">
                                        @if($planRenovar && $planRenovar->qr_imagen)
                                            <img src="{{ Storage::url($planRenovar->qr_imagen) }}"
                                                 alt="QR de Pago"
                                                 class="img-fluid"
                                                 style="max-width:260px; border:3px solid #0d6efd;
                                                        border-radius:12px; padding:8px; background:#fff;">
                                        @else
                                            <div style="width:220px; height:220px; border:3px dashed #0d6efd;
                                                        border-radius:12px; display:flex; align-items:center;
                                                        justify-content:center; background:#f8f9fa;">
                                                <div class="text-center p-3">
                                                    <i class="fa-solid fa-qrcode fa-4x text-primary mb-2"></i>
                                                    <p class="text-muted small mb-0">Sin QR configurado</p>
                                                </div>
                                            </div>
                                        @endif
                                        <small class="text-muted mt-3">
                                            <i class="fa-solid fa-mobile-screen me-1"></i>
                                            Escanea con tu app bancaria
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Comprobante + notas --}}
                            <div class="col-md-7">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-success text-white fw-bold"
                                         style="border-radius:8px 8px 0 0;">
                                        <i class="fa-solid fa-file-arrow-up me-2"></i>Sube tu Comprobante
                                    </div>
                                    <div class="card-body">

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Comprobante de Pago <span class="text-danger">*</span>
                                            </label>
                                            <input type="file"
                                                   class="form-control @error('renovarComprobante') is-invalid @enderror"
                                                   wire:model="renovarComprobante"
                                                   accept="image/*">
                                            @error('renovarComprobante')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">JPG, PNG. M├íximo 2 MB.</small>
                                        </div>

                                        {{-- Preview comprobante --}}
                                        @if($renovarComprobante)
                                            <div class="mb-3 text-center">
                                                <img src="{{ $renovarComprobante->temporaryUrl() }}"
                                                     class="img-fluid rounded shadow-sm"
                                                     style="max-height:160px; border:2px solid #198754;">
                                            </div>
                                        @endif

                                        {{-- Spinner upload --}}
                                        <div wire:loading wire:target="renovarComprobante" class="text-center mb-3">
                                            <div class="spinner-border spinner-border-sm text-primary me-1"></div>
                                            <small class="text-muted">Subiendo imagen...</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Referencia / Notas
                                                <span class="text-muted fw-normal">(opcional)</span>
                                            </label>
                                            <textarea wire:model="renovarNotas" class="form-control" rows="2"
                                                placeholder="Ej: Transferencia #12345, banco XYZ, 08/03/2026..."></textarea>
                                        </div>

                                        <div class="alert alert-warning py-2 mb-0">
                                            <small>
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                                Tu solicitud ser├í revisada. La suscripci├│n se activar├í una vez que el administrador verifique el pago.
                                            </small>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif

                </div>

                {{-- ÔöÇÔöÇ FOOTER / NAVEGACI├ôN ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ --}}
                <div class="d-flex justify-content-between align-items-center px-4 pb-4 pt-2"
                     style="border-top:1px solid #dee2e6;">

                    {{-- Bot├│n izquierdo: Cancelar (paso 1) o Volver (paso 2) --}}
                    @if($renovarPaso === 1)
                        <button type="button" class="btn btn-secondary" wire:click="closeRenovarModal">
                            <i class="fa-solid fa-times me-1"></i>Cancelar
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-secondary"
                                wire:click="$set('renovarPaso', 1)">
                            <i class="fa-solid fa-arrow-left me-1"></i>Volver
                        </button>
                    @endif

                    {{-- Bot├│n derecho: Continuar (paso 1) o Enviar (paso 2) --}}
                    @if($renovarPaso === 1)
                        <button type="button" class="btn btn-primary btn-lg fw-bold"
                                wire:click="renovarAvanzarPaso"
                                {{ !$renovarPlanId ? 'disabled' : '' }}>
                            Continuar
                            <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    @else
                        <button type="button" class="btn btn-success btn-lg fw-bold"
                                wire:click="confirmarRenovacion"
                                wire:loading.attr="disabled"
                                wire:target="confirmarRenovacion">
                            <span wire:loading.remove wire:target="confirmarRenovacion">
                                <i class="fa-solid fa-paper-plane me-2"></i>Enviar Solicitud
                            </span>
                            <span wire:loading wire:target="confirmarRenovacion">
                                <span class="spinner-border spinner-border-sm me-2"></span>Enviando...
                            </span>
                        </button>
                    @endif

                </div>

            </div>
        </div>
    @endif
</div>

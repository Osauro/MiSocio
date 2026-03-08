<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <!-- Estadísticas modernas -->
                <div class="row mt-3 mb-3 g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Pendientes</div>
                                <div class="stat-value">{{ $estadisticas['pendientes'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="fa-solid fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Verificados</div>
                                <div class="stat-value">{{ $estadisticas['verificados'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card stat-card-danger">
                            <div class="stat-icon">
                                <i class="fa-solid fa-times-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Rechazados</div>
                                <div class="stat-value">{{ $estadisticas['rechazados'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="fa-solid fa-dollar-sign"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Total del Mes</div>
                                <div class="stat-value">Bs. {{ number_format($estadisticas['total_mes'], 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .stat-card {
                        background: white;
                        border-radius: 12px;
                        padding: 20px;
                        display: flex;
                        align-items: center;
                        gap: 15px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                        border-left: 4px solid;
                        transition: all 0.3s ease;
                        cursor: default;
                        position: relative;
                        overflow: hidden;
                    }

                    .stat-card::before {
                        content: '';
                        position: absolute;
                        top: 0;
                        right: 0;
                        width: 80px;
                        height: 80px;
                        border-radius: 50%;
                        opacity: 0.05;
                        transition: all 0.3s ease;
                    }

                    .stat-card:hover {
                        transform: translateY(-4px);
                        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
                    }

                    .stat-card:hover::before {
                        width: 120px;
                        height: 120px;
                        opacity: 0.1;
                    }

                    .stat-card-warning {
                        border-left-color: #ffc107;
                    }

                    .stat-card-warning::before {
                        background: #ffc107;
                    }

                    .stat-card-warning .stat-icon {
                        background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
                    }

                    .stat-card-success {
                        border-left-color: #28a745;
                    }

                    .stat-card-success::before {
                        background: #28a745;
                    }

                    .stat-card-success .stat-icon {
                        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                    }

                    .stat-card-danger {
                        border-left-color: #dc3545;
                    }

                    .stat-card-danger::before {
                        background: #dc3545;
                    }

                    .stat-card-danger .stat-icon {
                        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                    }

                    .stat-card-primary {
                        border-left-color: #007bff;
                    }

                    .stat-card-primary::before {
                        background: #007bff;
                    }

                    .stat-card-primary .stat-icon {
                        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                    }

                    .stat-icon {
                        width: 50px;
                        height: 50px;
                        border-radius: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-size: 22px;
                        flex-shrink: 0;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    }

                    .stat-content {
                        flex: 1;
                        min-width: 0;
                    }

                    .stat-label {
                        font-size: 0.75rem;
                        font-weight: 600;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        color: #6c757d;
                        margin-bottom: 4px;
                    }

                    .stat-value {
                        font-size: 1.75rem;
                        font-weight: 700;
                        color: #2c3e50;
                        line-height: 1;
                    }

                    @media (max-width: 768px) {
                        .stat-card {
                            padding: 15px;
                        }

                        .stat-icon {
                            width: 40px;
                            height: 40px;
                            font-size: 18px;
                        }

                        .stat-value {
                            font-size: 1.5rem;
                        }
                    }
                </style>

                <!-- Gestión de Pagos -->
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="mb-0">
                                <i class="fa-solid fa-money-check-dollar me-2"></i>
                                Gestión de Pagos
                            </h3>
                            <div class="input-group" style="max-width: 300px;">
                                <input type="text" class="form-control" placeholder="Buscar..." wire:model.live="search" autofocus>
                                <button class="btn {{ $soloPendientes ? 'btn-warning text-dark' : 'btn-light text-dark' }}"
                                    type="button" wire:click="$toggle('soloPendientes')"
                                    title="{{ $soloPendientes ? 'Mostrando solo pendientes' : 'Mostrando todos' }}">
                                    <i class="fa-solid {{ $soloPendientes ? 'fa-filter' : 'fa-list' }}"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-2 pb-3">
                        <div class="row g-2">
                            @forelse($pagos as $pago)
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                                    <div class="card h-100 shadow-sm border-{{ $pago->estado_pago === 'verificado' ? 'success' : ($pago->estado_pago === 'rechazado' ? 'danger' : 'warning') }}">
                                        <!-- Header con color según estado -->
                                        <div class="card-header bg-{{ $pago->estado_pago === 'verificado' ? 'success' : ($pago->estado_pago === 'rechazado' ? 'danger' : 'warning') }} text-white py-0 px-2">
                                            <div class="d-flex justify-content-between align-items-center" style="height: 28px;">
                                                <small class="mb-0 fw-bold" style="font-size: 0.65rem;">
                                                    #{{ $pago->id }}
                                                </small>
                                                <div class="d-flex gap-1">
                                                    @if($pago->comprobante_url)
                                                        <a href="{{ Storage::url($pago->comprobante_url) }}"
                                                           target="_blank"
                                                           class="btn btn-light btn-sm p-0 text-dark"
                                                           style="font-size: 0.6rem; width: 22px; height: 22px;"
                                                           title="Ver comprobante">
                                                            <i class="fa-solid fa-image"></i>
                                                        </a>
                                                    @endif
                                                    <button class="btn btn-light btn-sm p-0 text-dark"
                                                            style="font-size: 0.6rem; width: 22px; height: 22px;"
                                                            wire:click="verComprobante({{ $pago->id }})"
                                                            title="Ver detalles">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Body compacto -->
                                        <div class="card-body p-2" style="font-size: 0.7rem;">
                                            <!-- Tenant + Monto en una línea -->
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong class="text-truncate" style="font-size: 0.75rem; max-width: 60%;">{{ $pago->tenant?->name ?? '(sin tienda)' }}</strong>
                                                <span class="text-success fw-bold" style="font-size: 0.85rem;">{{ number_format($pago->monto, 0) }}</span>
                                            </div>

                                            <!-- Plan + Duración en badges pequeños -->
                                            <div class="mb-1">
                                                <span class="badge bg-info" style="font-size: 0.6rem; padding: 2px 6px;">{{ ucfirst($pago->plan_nombre) }}</span>
                                                @if($pago->duracion_meses > 1)
                                                    <span class="badge bg-secondary" style="font-size: 0.55rem; padding: 2px 4px;">{{ $pago->duracion_meses }}m</span>
                                                @endif
                                            </div>

                                            <!-- Periodo compacto -->
                                            @if($pago->fecha_inicio && $pago->fecha_fin)
                                                <div class="text-muted mb-1" style="font-size: 0.65rem;">
                                                    <i class="fa-solid fa-calendar-days" style="font-size: 0.6rem;"></i>
                                                    {{ $pago->fecha_inicio->format('d/m') }} - {{ $pago->fecha_fin->format('d/m/y') }}
                                                </div>
                                            @endif

                                            <!-- Estado badge pequeño -->
                                            <div>
                                                @if($pago->estado_pago === 'pendiente')
                                                    <span class="badge bg-warning text-dark" style="font-size: 0.55rem; padding: 2px 5px;">
                                                        <i class="fa-solid fa-clock" style="font-size: 0.5rem;"></i> Pendiente
                                                    </span>
                                                @elseif($pago->estado_pago === 'verificado')
                                                    <span class="badge bg-success" style="font-size: 0.55rem; padding: 2px 5px;">
                                                        <i class="fa-solid fa-check" style="font-size: 0.5rem;"></i> OK
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger" style="font-size: 0.55rem; padding: 2px 5px;">
                                                        <i class="fa-solid fa-times" style="font-size: 0.5rem;"></i> Rechazado
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa-solid fa-receipt fa-5x mb-3 text-muted"></i>
                                    <p class="h5 text-muted mb-0">No se encontraron pagos</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @include('partials.paginate-bar', ['results' => $pagos, 'storageKey' => 'pagos'])

    <!-- Modal de Verificación -->
    @if ($modalOpen && $pago)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-{{ $pago->estado_pago === 'verificado' ? 'success' : ($pago->estado_pago === 'rechazado' ? 'danger' : 'warning') }} text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-receipt me-2"></i>
                            Detalles del Pago #{{ $pago->id }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Información del Tenant -->
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">
                                    <i class="fa-solid fa-store me-2"></i>
                                    Información del Tenant
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <strong>Tenant:</strong> {{ $pago->tenant?->name ?? '(sin tienda)' }}
                            </div>
                            <div class="col-md-6">
                                <strong>Dominio:</strong> {{ $pago->tenant?->domain ?? 'Sin dominio' }}
                            </div>

                            <!-- Información del Pago -->
                            <div class="col-12 mt-3">
                                <h6 class="border-bottom pb-2">
                                    <i class="fa-solid fa-credit-card me-2"></i>
                                    Información del Pago
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <strong>Plan:</strong> {{ ucfirst($pago->plan_nombre) }}
                            </div>
                            <div class="col-md-6">
                                <strong>Duración:</strong> {{ $pago->duracion_meses }} {{ $pago->duracion_meses == 1 ? 'mes' : 'meses' }}
                            </div>
                            <div class="col-md-6">
                                <strong>Monto:</strong> <span class="text-success fw-bold">Bs. {{ number_format($pago->monto, 2) }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Estado:</strong>
                                @if($pago->estado_pago === 'pendiente')
                                    <span class="badge bg-warning">Pendiente</span>
                                @elseif($pago->estado_pago === 'verificado')
                                    <span class="badge bg-success">Verificado</span>
                                @else
                                    <span class="badge bg-danger">Rechazado</span>
                                @endif
                            </div>

                            @if($pago->fecha_inicio || $pago->fecha_fin)
                                <div class="col-md-6">
                                    <strong>Fecha Inicio:</strong> {{ $pago->fecha_inicio?->format('d/m/Y') ?? '-' }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Fecha Fin:</strong> {{ $pago->fecha_fin?->format('d/m/Y') ?? '-' }}
                                </div>
                            @endif

                            <!-- Comprobante -->
                            @if($pago->comprobante_url)
                                <div class="col-12 mt-3">
                                    <h6 class="border-bottom pb-2">
                                        <i class="fa-solid fa-image me-2"></i>
                                        Comprobante de Pago
                                    </h6>

                                    <!-- Botones de acción para el comprobante -->
                                    <div class="mb-3 d-flex gap-2">
                                        <a href="{{ Storage::url($pago->comprobante_url) }}"
                                           target="_blank"
                                           class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-external-link-alt me-1"></i>
                                            Ver en Nueva Pestaña
                                        </a>
                                        <a href="{{ Storage::url($pago->comprobante_url) }}"
                                           download="comprobante-pago-{{ $pago->id }}.jpg"
                                           class="btn btn-success btn-sm">
                                            <i class="fa-solid fa-download me-1"></i>
                                            Descargar
                                        </a>
                                    </div>

                                    <div class="text-center">
                                        <img src="{{ Storage::url($pago->comprobante_url) }}"
                                             alt="Comprobante"
                                             class="img-fluid rounded border"
                                             style="max-height: 400px; cursor: pointer;"
                                             onclick="window.open('{{ Storage::url($pago->comprobante_url) }}', '_blank')">
                                        <p class="text-muted small mt-2">
                                            <i class="fa-solid fa-info-circle me-1"></i>
                                            Haz clic en la imagen para verla en tamaño completo
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="col-12 mt-3">
                                    <div class="alert alert-warning">
                                        <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                        No se ha subido ningún comprobante de pago
                                    </div>
                                </div>
                            @endif

                            <!-- Información de Verificación -->
                            @if($pago->verificado_por)
                                <div class="col-12 mt-3">
                                    <h6 class="border-bottom pb-2">
                                        <i class="fa-solid fa-user-check me-2"></i>
                                        Información de Verificación
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <strong>Verificado por:</strong> {{ $pago->verificadoPor->name }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Fecha:</strong> {{ $pago->verificado_at->format('d/m/Y H:i') }}
                                </div>
                                @if($pago->notas_verificacion)
                                    <div class="col-12">
                                        <strong>Notas:</strong>
                                        <p class="mt-1 p-2 bg-light rounded text-dark">{{ $pago->notas_verificacion }}</p>
                                    </div>
                                @endif
                            @endif

                            <!-- Acciones de Verificación -->
                            @if($pago->estado_pago === 'pendiente' && !$accion)
                                <div class="col-12 mt-3">
                                    <h6 class="border-bottom pb-2">
                                        <i class="fa-solid fa-tasks me-2"></i>
                                        Acciones
                                    </h6>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-success btn-lg flex-fill" wire:click="verificarPago">
                                            <i class="fa-solid fa-check me-2"></i>Verificar Pago
                                        </button>
                                        <button class="btn btn-danger btn-lg flex-fill" wire:click="rechazarPago">
                                            <i class="fa-solid fa-times me-2"></i>Rechazar Pago
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Formulario de Notas -->
                            @if($accion)
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-bold">
                                        Notas {{ $accion === 'verificar' ? 'de Verificación' : 'de Rechazo' }}:
                                    </label>
                                    <textarea class="form-control" wire:model="notas" rows="3"
                                        placeholder="Ingrese comentarios o notas..."></textarea>
                                    @error('notas') <span class="text-danger small">{{ $message }}</span> @enderror

                                    <div class="mt-3 d-flex gap-2">
                                        <button class="btn btn-{{ $accion === 'verificar' ? 'success' : 'danger' }} btn-lg flex-fill"
                                            wire:click="confirmarAccion">
                                            <i class="fa-solid fa-{{ $accion === 'verificar' ? 'check-circle' : 'times-circle' }} me-2"></i>
                                            Confirmar {{ $accion === 'verificar' ? 'Verificación' : 'Rechazo' }}
                                        </button>
                                        <button class="btn btn-secondary btn-lg" wire:click="$set('accion', null)">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0">
                        <div class="header-top">
                            <h3 class="mb-0">
                                <i class="fa-solid fa-store me-2"></i>
                                @if($paso === 1)
                                    Selecciona tu Plan de Suscripción
                                @elseif($paso === 2)
                                    Completa los Datos de tu Tienda
                                @else
                                    Confirma tu Pago
                                @endif
                            </h3>
                            <p class="text-muted mt-2">
                                @if($paso === 1)
                                    Elige el plan que mejor se adapte a tus necesidades
                                @elseif($paso === 2)
                                    Estás a un paso de comenzar con LicoPOS
                                @else
                                    Realiza el pago y sube tu comprobante para activar tu tienda
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="card-body pt-3">
                        @if($paso === 1)
                            <!-- PASO 1: Selección de Plan -->
                            <div class="row g-4 justify-content-center">
                                <!-- Todos los planes desde la base de datos -->
                                @foreach($planes as $plan)
                                <div class="col-md-3">
                                    <div class="plan-card {{ $plan->precio == 0 ? 'plan-demo' : 'plan-paid' }}">
                                        @if($plan->precio == 0)
                                        <div class="plan-badge">
                                            <i class="fa-solid fa-gift me-1"></i> ¡Gratis!
                                        </div>
                                        @elseif($plan->duracion_meses == 12)
                                        <div class="plan-badge plan-badge-popular">
                                            <i class="fa-solid fa-star me-1"></i> Más Popular
                                        </div>
                                        @endif
                                        <div class="plan-header">
                                            <h4>{{ $plan->nombre }}</h4>
                                            <div class="plan-price">
                                                <span class="currency">Bs.</span>
                                                <span class="amount">{{ number_format($plan->precio, 0) }}</span>
                                            </div>
                                            <div class="plan-duration">
                                                @if($plan->duracion_meses == 0)
                                                    15 días de prueba
                                                @else
                                                    {{ $plan->duracion_texto }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="plan-features">
                                            <ul>
                                                @if($plan->caracteristicas)
                                                    @foreach($plan->caracteristicas as $caracteristica)
                                                    <li><i class="fa-solid fa-check {{ $plan->precio == 0 ? 'text-success' : 'text-primary' }}"></i> {{ $caracteristica }}</li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>
                                        <button type="button" class="btn {{ $plan->precio == 0 ? 'btn-success' : 'btn-primary' }} btn-lg w-100"
                                                wire:click="seleccionarPlan({{ $plan->id }})">
                                            <i class="fa-solid fa-{{ $plan->precio == 0 ? 'rocket' : 'arrow-right' }} me-2"></i>
                                            {{ $plan->precio == 0 ? 'Comenzar Gratis' : 'Seleccionar Plan' }}
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                        @elseif($paso === 2)
                            <!-- PASO 2: Formulario de Tienda -->
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <!-- Botón volver -->
                                    <button type="button" class="btn btn-light btn-sm mb-3" wire:click="volverAPlanes">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Volver a planes
                                    </button>

                                    <!-- Alerta del plan seleccionado -->
                                    <div class="alert alert-info mb-4">
                                        <i class="fa-solid fa-info-circle me-2"></i>
                                        <strong>Plan seleccionado:</strong>
                                        @php
                                            $planSeleccionadoObj = $planes->find($planSeleccionado);
                                        @endphp
                                        @if($planSeleccionadoObj)
                                            {{ $planSeleccionadoObj->nombre }} -
                                            @if($planSeleccionadoObj->precio == 0)
                                                15 días gratis
                                            @else
                                                Bs. {{ number_format($planSeleccionadoObj->precio, 2) }} ({{ $planSeleccionadoObj->duracion_texto }})
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Formulario -->
                                    <form wire:submit.prevent="crearTenant">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre de la Tienda <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                wire:model="name" placeholder="Ej: Mi Tienda Principal" autofocus>
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Dominio (Opcional)</label>
                                            <input type="text" class="form-control @error('domain') is-invalid @enderror"
                                                wire:model="domain" placeholder="Dejar vacío para generar automáticamente">
                                            @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            <small class="text-muted">Si no especificas uno, se generará automáticamente basado en el nombre</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tema de Colores</label>
                                            <select class="form-select" wire:model="theme_number">
                                                <option value="1">Tema 1 - Azul</option>
                                                <option value="2">Tema 2 - Verde</option>
                                                <option value="3">Tema 3 - Rojo</option>
                                                <option value="4">Tema 4 - Naranja</option>
                                                <option value="5">Tema 5 - Púrpura</option>
                                                <option value="6">Tema 6 - Turquesa</option>
                                                <option value="7">Tema 7 - Rosa</option>
                                                <option value="8">Tema 8 - Amarillo</option>
                                                <option value="9">Tema 9 - Gris</option>
                                                <option value="10">Tema 10 - Negro</option>
                                            </select>
                                        </div>

                                        <div class="d-grid gap-2 mt-4">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fa-solid fa-check me-2"></i>
                                                @if($tipoPlan === 'demo')
                                                    Crear Tienda Demo
                                                @else
                                                    Continuar al Pago
                                                @endif
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        @else
                            <!-- PASO 3: Pago y Comprobante -->
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <!-- Botón volver -->
                                    <button type="button" class="btn btn-light btn-sm mb-3" wire:click="volverAlFormulario">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Volver al formulario
                                    </button>

                                    <!-- Información del plan -->
                                    <div class="alert alert-success mb-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fa-solid fa-circle-check fa-2x me-3"></i>
                                            <div>
                                                <h5 class="mb-1">Plan seleccionado: {{ $planData?->nombre }}</h5>
                                                <p class="mb-0">Monto a pagar: <strong>Bs. {{ number_format($planData?->precio ?? 0, 2) }}</strong> - Duración: {{ $planData?->duracion_texto }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Columna del QR -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="mb-0"><i class="fa-solid fa-qrcode me-2"></i>Escanea para Pagar</h5>
                                                </div>
                                                <div class="card-body text-center">
                                                    <!-- QR Code -->
                                                    <div class="qr-container mb-3">
                                                        @if($planData && $planData->qr_imagen)
                                                            <img src="{{ Storage::url($planData->qr_imagen) }}"
                                                                 alt="QR de Pago"
                                                                 class="img-fluid"
                                                                 style="max-width: 300px; border: 3px solid #007bff; border-radius: 12px; padding: 10px; background: white;">
                                                        @else
                                                            <div class="qr-placeholder" style="display: flex; width: 300px; height: 300px; border: 3px dashed #007bff; border-radius: 12px; align-items: center; justify-content: center; background: #f8f9fa; margin: 0 auto;">
                                                                <div class="text-center p-4">
                                                                    <i class="fa-solid fa-qrcode fa-5x text-primary mb-3"></i>
                                                                    <p class="text-muted"><strong>QR de Pago</strong></p>
                                                                    <small class="text-muted">Escanea con tu app de banco</small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columna del comprobante -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100">
                                                <div class="card-header bg-success text-white">
                                                    <h5 class="mb-0"><i class="fa-solid fa-file-upload me-2"></i>Sube tu Comprobante</h5>
                                                </div>
                                                <div class="card-body">
                                                    <form wire:submit.prevent="procesarPago">
                                                        <div class="mb-3">
                                                            <label class="form-label">Comprobante de Pago <span class="text-danger">*</span></label>
                                                            <input type="file"
                                                                   class="form-control @error('comprobante') is-invalid @enderror"
                                                                   wire:model="comprobante"
                                                                   accept="image/*">
                                                            @error('comprobante')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                            <small class="text-muted">Formatos: JPG, PNG. Máximo 2MB</small>
                                                        </div>

                                                        <!-- Preview del comprobante -->
                                                        @if($comprobante)
                                                            <div class="mb-3">
                                                                <label class="form-label">Vista Previa:</label>
                                                                <div class="text-center">
                                                                    <img src="{{ $comprobante->temporaryUrl() }}"
                                                                         alt="Comprobante"
                                                                         class="img-fluid rounded"
                                                                         style="max-height: 300px; border: 2px solid #28a745;">
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <!-- Loading indicator -->
                                                        <div wire:loading wire:target="comprobante" class="text-center mb-3">
                                                            <div class="spinner-border text-primary" role="status">
                                                                <span class="visually-hidden">Cargando...</span>
                                                            </div>
                                                            <p class="mt-2 text-muted">Subiendo imagen...</p>
                                                        </div>

                                                        <div class="alert alert-warning">
                                                            <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                                            <small>
                                                                <strong>Importante:</strong> Tu pago será verificado por nuestro equipo.
                                                                Una vez aprobado, recibirás un correo y podrás acceder a tu tienda.
                                                            </small>
                                                        </div>

                                                        <div class="d-grid gap-2 mt-4">
                                                            <button type="submit"
                                                                    class="btn btn-success btn-lg"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="procesarPago">
                                                                <span wire:loading.remove wire:target="procesarPago">
                                                                    <i class="fa-solid fa-paper-plane me-2"></i>
                                                                    Enviar para Verificación
                                                                </span>
                                                                <span wire:loading wire:target="procesarPago">
                                                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                                                    Procesando...
                                                                </span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .plan-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            border: 2px solid #e9ecef;
        }

        .plan-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .plan-demo {
            border-color: #28a745;
            background: linear-gradient(135deg, #f0f9f4 0%, #ffffff 100%);
        }

        .plan-paid:hover {
            border-color: #007bff;
        }

        .plan-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .plan-badge-popular {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }

        .plan-header {
            text-align: center;
            margin-bottom: 30px;
            padding-top: 15px;
        }

        .plan-header h4 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .plan-price {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 5px;
            margin-bottom: 8px;
        }

        .plan-price .currency {
            font-size: 1.2rem;
            color: #6c757d;
            font-weight: 600;
        }

        .plan-price .amount {
            font-size: 3rem;
            font-weight: 800;
            color: #2c3e50;
            line-height: 1;
        }

        .plan-duration {
            color: #6c757d;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .plan-features {
            margin-bottom: 30px;
        }

        .plan-features ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .plan-features li {
            padding: 12px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            color: #495057;
            border-bottom: 1px solid #f1f3f5;
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-features i {
            font-size: 1.1rem;
        }
    </style>
</div>

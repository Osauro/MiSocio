<div x-data="{ mostrarCarritoMovil: false }">
    <style>
        /* Control de visibilidad en móvil - Cargado inmediatamente */
        @media (max-width: 767.98px) {
            .compra-items-wrapper {
                display: none !important;
            }
            .compra-items-wrapper.show-mobile-items {
                display: block !important;
            }
            .compra-search-wrapper.hide-mobile-search {
                display: none !important;
            }
        }

        /* En desktop siempre mostrar todo */
        @media (min-width: 768px) {
            .compra-items-wrapper,
            .compra-search-wrapper {
                display: block !important;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="row starter-main">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header card-no-border pb-0 d-none d-md-block" style="position: sticky; top: 0; z-index: 1050; background-color: white;">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Compra #{{ $compra->numero_folio }}</h3>
                            <div class="d-flex gap-2">`
                                <button wire:click="cancelarCompra" class="btn btn-secondary">
                                    <i class="fa-solid fa-times me-1"></i>
                                    <span class="d-none d-md-inline">Cancelar</span>
                                </button>
                                @if(count($items) > 0)
                                    <button type="button" wire:click="iniciarCompletarCompra" class="btn btn-success">
                                        <i class="fa-solid fa-check me-1"></i>
                                        <span class="d-none d-md-inline">Completar</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-2">
                        <div class="row">
                            <!-- Columna de Items (Izquierda) - Oculta en móvil por defecto -->
                            <div class="col-md-8 col-lg-9 d-md-block" :class="{ 'd-none': !mostrarCarritoMovil }">
                                <div class="row g-2">
                                    @forelse($items as $index => $item)
                                        <div class="col-md-6 col-lg-4 col-xl-4" wire:key="item-{{ $item['id'] }}">
                                            <div class="card mb-0 shadow-sm h-100">
                                                <div class="card-body p-2">
                                                    <!-- Nombre y Botón -->
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div class="flex-grow-1">
                                                            <h5 class="mb-0 text-truncate fw-bold">{{ $item['nombre'] }}</h5>
                                                        </div>
                                                        <a href="javascript:void(0)" class="text-danger ms-1"
                                                            wire:click="confirmEliminarItem({{ $index }})"
                                                            title="Eliminar"
                                                            style="font-size: 1.1rem;">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </div>

                                                    <!-- Imagen e Inputs -->
                                                    <div class="row g-2">
                                                        <!-- Imagen (Izquierda) -->
                                                        <div class="col-5">
                                                            <img src="{{ $item['imagen'] }}" alt="{{ $item['nombre'] }}"
                                                                class="rounded w-100"
                                                                style="aspect-ratio: 1/1; object-fit: cover;">
                                                        </div>

                                                        <!-- Inputs (Derecha) -->
                                                        <div class="col-7">
                                                            <div class="row g-1">
                                                                <!-- Fila 1: Enteros y Unidades -->
                                                                @if($item['cantidad_por_medida'] > 1)
                                                                <div class="col-6">
                                                                    <label class="form-label mb-1 small fw-bold">Enteros</label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm text-end"
                                                                        wire:model.blur="items.{{ $index }}.enteros"
                                                                        wire:change="actualizarItem({{ $index }})"
                                                                        @keydown.enter.prevent="abrirModalSuma({{ $index }}, 'enteros', $event.target.value)"
                                                                        onclick="this.select()"
                                                                        inputmode="numeric"
                                                                        step="1"
                                                                        min="0"
                                                                        placeholder="0">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label mb-1 small fw-bold">Unidades</label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm text-end"
                                                                        wire:model.blur="items.{{ $index }}.unidades"
                                                                        wire:change="actualizarItem({{ $index }})"
                                                                        @keydown.enter.prevent="abrirModalSuma({{ $index }}, 'unidades', $event.target.value)"
                                                                        onclick="this.select()"
                                                                        inputmode="numeric"
                                                                        step="1"
                                                                        min="0"
                                                                        max="{{ $item['cantidad_por_medida'] - 1 }}"
                                                                        placeholder="0">
                                                                </div>
                                                                @else
                                                                <div class="col-12">
                                                                    <label class="form-label mb-1 small fw-bold">Cantidad</label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm text-end"
                                                                        wire:model.blur="items.{{ $index }}.unidades"
                                                                        wire:change="actualizarItem({{ $index }})"
                                                                        @keydown.enter.prevent="abrirModalSuma({{ $index }}, 'unidades', $event.target.value)"
                                                                        onclick="this.select()"
                                                                        inputmode="numeric"
                                                                        step="1"
                                                                        min="0"
                                                                        placeholder="0">
                                                                </div>
                                                                @endif

                                                                <!-- Fila 2: Precio y Subtotal -->
                                                                <div class="col-6">
                                                                    <label class="form-label mb-1 small fw-bold">Precio</label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm text-end"
                                                                        wire:model.live="items.{{ $index }}.precio"
                                                                        wire:change="actualizarItem({{ $index }})"
                                                                        onclick="this.select()"
                                                                        step="1"
                                                                        min="0"
                                                                        placeholder="0">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label mb-1 small fw-bold">Subtotal</label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm text-end fw-bold"
                                                                        wire:model.live="items.{{ $index }}.subtotal"
                                                                        wire:change="actualizarSubtotal({{ $index }})"
                                                                        onclick="this.select()"
                                                                        step="1"
                                                                        min="0"
                                                                        placeholder="0">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                                    <div class="text-center py-5">
                                                <i class="fa-solid fa-basket-shopping fa-5x text-muted mb-3"></i>
                                                <p class="h5 text-muted mb-0">No hay productos agregados</p>
                                                <small class="text-muted">Usa el buscador para agregar productos</small>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                <!-- Espaciador para vista móvil -->
                                <div class="d-md-none" style="min-height: 80px;"></div>
                            </div>

                            <!-- Columna de Buscador (Derecha) - Oculta cuando se muestra carrito en móvil -->
                            <div class="col-md-4 col-lg-3 d-md-block" :class="{ 'd-none': mostrarCarritoMovil }">
                                <div class="card shadow-sm" style="position: sticky; top: 10px; z-index: 1;">
                                    <div class="card-body p-2">
                                        <!-- Input de Búsqueda con icono -->
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-primary text-white">
                                                <i class="fa-solid fa-search"></i>
                                            </span>
                                            <input type="text"
                                                id="buscadorCompra"
                                                class="form-control text-end"
                                                wire:model.live.debounce.300ms="buscar"
                                                placeholder="Nombre o código..."
                                                autofocus>
                                        </div>

                                        <!-- Resultados -->
                                        <div class="search-results" style="max-height: calc(100vh - 300px); overflow-y: auto;">
                                            @if(strlen($buscar) >= 2)
                                                @forelse($productosEncontrados as $producto)
                                                    @php
                                                        $yaAgregado = collect($items)->firstWhere('producto_id', $producto['id']);
                                                    @endphp
                                                    <div class="card mb-2 border-0 shadow-sm producto-result {{ $yaAgregado ? 'disabled' : '' }}"
                                                        wire:key="producto-{{ $producto['id'] }}"
                                                        @if(!$yaAgregado)
                                                            wire:click="agregarProducto({{ $producto['id'] }})"
                                                            style="cursor: pointer;"
                                                        @else
                                                            style="cursor: not-allowed; opacity: 0.5; background-color: #f8f9fa;"
                                                        @endif>
                                                        <div class="card-body p-2">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <img src="{{ $producto['photo_url'] }}"
                                                                    alt="{{ $producto['nombre'] }}"
                                                                    class="rounded"
                                                                    style="width: 40px; height: 40px; object-fit: cover;">
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold small">{{ $producto['nombre'] }}</div>
                                                                    <div class="d-flex gap-1 mt-1">
                                                                        <span class="badge bg-info text-dark">
                                                                            Stock: {{ $producto['stock_formateado'] ?? $producto['stock'] }}
                                                                        </span>
                                                                        <span class="badge bg-secondary">
                                                                            {{ $producto['medida'] }} ({{ $producto['cantidad'] ?? 1 }}u)
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                @if($yaAgregado)
                                                                    <i class="fa-solid fa-check text-success"></i>
                                                                @else
                                                                    <i class="fa-solid fa-plus text-primary"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center text-muted py-3">
                                                        <i class="fa-solid fa-search fa-2x mb-2"></i>
                                                        <p class="mb-0 small">No se encontraron productos</p>
                                                    </div>
                                                @endforelse
                                            @else
                                                <div class="text-center text-muted py-3">
                                                    <i class="fa-solid fa-keyboard fa-2x mb-2"></i>
                                                    <p class="mb-0 small">Escribe al menos 2 caracteres</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Paso 1: Fecha de Compra -->
    @if($pasoActual === 1)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-calendar me-2"></i>
                        Paso 1: Fecha de Compra
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fechaCompra" class="form-label fw-bold">Fecha de la compra</label>
                        <input type="date"
                            id="fechaCompra"
                            class="form-control form-control-lg text-end"
                            wire:model="fechaCompra"
                            max="{{ date('Y-m-d') }}">
                        <small class="text-muted">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Presiona Enter para continuar
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelarPagoEnProceso">
                        <i class="fa-solid fa-times me-1"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="avanzarPaso1">
                        <i class="fa-solid fa-arrow-right me-1"></i>
                        Siguiente <span class="badge bg-white text-primary ms-1">Enter</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Paso 2: Selección de Proveedor -->
    @if($pasoActual === 2)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-user-tie me-2"></i>
                        Paso 2: Seleccionar Proveedor
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="buscarProveedor" class="form-label fw-bold">Buscar proveedor</label>
                        <input type="text"
                            id="buscarProveedor"
                            class="form-control form-control-lg text-end"
                            wire:model.live.debounce.300ms="buscarProveedor"
                            placeholder="Celular (8 dígitos) o nombre..."
                            x-init="$nextTick(() => $el.focus())">
                        <small class="text-muted">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Ingresa 8 dígitos para buscar por celular o el nombre del proveedor
                        </small>
                    </div>

                    <!-- Resultados de búsqueda -->
                    @if(count($proveedoresEncontrados) > 0)
                        <div class="list-group mb-3" style="max-height: 300px; overflow-y: auto;">
                            @foreach($proveedoresEncontrados as $proveedor)
                                <button type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    wire:click="seleccionarProveedor({{ $proveedor['id'] }})">
                                    <div>
                                        <h6 class="mb-0">{{ $proveedor['nombre'] }}</h6>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-phone me-1"></i>
                                            {{ $proveedor['celular'] }}
                                        </small>
                                    </div>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Formulario para nuevo proveedor -->
                    @if($mostrarFormNuevoProveedor)
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombre</label>
                                <input type="text"
                                    id="nuevoProveedorNombre"
                                    class="form-control text-end"
                                    wire:model="nuevoProveedor.nombre"
                                    x-init="$nextTick(() => $el.focus())"
                                    @keydown.enter="if($el.value.trim() !== '') { $wire.call('crearYSeleccionarProveedor') } else { $wire.call('avanzarPaso2SinProveedor') }">
                                @error('nuevoProveedor.nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Celular</label>
                                <input type="text"
                                    class="form-control text-end"
                                    wire:model="nuevoProveedor.celular"
                                    @keydown.enter="if($wire.nuevoProveedor.nombre && $wire.nuevoProveedor.nombre.trim() !== '') { $wire.call('crearYSeleccionarProveedor') } else { $wire.call('avanzarPaso2SinProveedor') }">
                                @error('nuevoProveedor.celular') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Dirección</label>
                                <input type="text"
                                    class="form-control text-end"
                                    wire:model="nuevoProveedor.direccion"
                                    @keydown.enter="if($wire.nuevoProveedor.nombre && $wire.nuevoProveedor.nombre.trim() !== '') { $wire.call('crearYSeleccionarProveedor') } else { $wire.call('avanzarPaso2SinProveedor') }">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NIT</label>
                                <input type="text"
                                    class="form-control text-end"
                                    wire:model="nuevoProveedor.nit"
                                    @keydown.enter="if($wire.nuevoProveedor.nombre && $wire.nuevoProveedor.nombre.trim() !== '') { $wire.call('crearYSeleccionarProveedor') } else { $wire.call('avanzarPaso2SinProveedor') }">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelarPagoEnProceso">
                        <i class="fa-solid fa-times me-1"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" wire:click="avanzarPaso2SinProveedor">
                        <i class="fa-solid fa-forward me-1"></i>
                        Continuar sin Proveedor
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Paso 3: Añadir Saldo a Caja -->
    @if($pasoActual === 3)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-wallet me-2"></i>
                        Paso 3: Añadir Saldo a Caja
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        @php
                            $totalCompra = collect($items)->sum('subtotal');
                            $faltante = max(0, $totalCompra - $saldoCaja);
                        @endphp

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="alert alert-info mb-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><i class="fa-solid fa-wallet me-1"></i> Saldo en caja:</span>
                                        <strong>Bs. {{ number_format($saldoCaja, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><i class="fa-solid fa-shopping-cart me-1"></i> Total compra:</span>
                                        <strong>Bs. {{ number_format($totalCompra, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($faltante > 0)
                            <div class="alert alert-danger mb-3">
                                <i class="fa-solid fa-exclamation-triangle me-1"></i>
                                Faltante: <strong>Bs. {{ number_format($faltante, 2) }}</strong>
                            </div>
                        @endif

                        <label for="montoAnadirCaja" class="form-label fw-bold">Monto a añadir (opcional)</label>
                        <input type="number"
                            id="montoAnadirCaja"
                            class="form-control form-control-lg text-end"
                            wire:model.live="montoAñadirCaja"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            wire:keydown.enter="avanzarPaso3"
                            x-init="$nextTick(() => { $el.focus(); $el.select(); })">
                        <small class="text-muted">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Por defecto 0. Presione Enter para continuar
                        </small>
                    </div>
                    @if($montoAñadirCaja > 0)
                        <div class="alert alert-success">
                            <i class="fa-solid fa-check-circle me-1"></i>
                            Se añadirá Bs. {{ number_format($montoAñadirCaja, 2) }} a caja
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="retrocederPaso">
                        <i class="fa-solid fa-arrow-left me-1"></i>
                        Atrás
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="avanzarPaso3">
                        <i class="fa-solid fa-arrow-right me-1"></i>
                        Continuar <span class="badge bg-white text-primary ms-1">Enter</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Paso 4: Procesar Pago -->
    @if($pasoActual === 4 && !$procesandoPago)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-money-bill me-2"></i>
                        Paso 4: Procesar Pago
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h5 class="text-center mb-3">
                            Total: <span class="text-primary">Bs. {{ number_format(collect($items)->sum('subtotal'), 2) }}</span>
                        </h5>
                        <div class="alert alert-info mb-3">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Saldo en caja: <strong>Bs. {{ number_format($saldoCaja, 2) }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="montoPago" class="form-label fw-bold">Monto a pagar en efectivo</label>
                        <input type="number"
                            id="montoPago"
                            class="form-control form-control-lg text-end"
                            wire:model.live="montoPago"
                            min="0"
                            max="{{ collect($items)->sum('subtotal') }}"
                            step="0.01"
                            wire:keydown.enter="procesarPago"
                            x-init="$nextTick(() => { $el.focus(); $el.select(); })">

                        @php
                            $total = collect($items)->sum('subtotal');
                            $credito = $total - $montoPago;
                        @endphp

                        @if($montoPago < $total && $proveedorSeleccionado !== null)
                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="fa-solid fa-exclamation-triangle me-1"></i>
                                Efectivo: Bs. {{ number_format($montoPago, 2) }} |
                                Crédito: Bs. {{ number_format($credito, 2) }}
                            </div>
                        @elseif($montoPago == $total)
                            <div class="alert alert-success mt-2 mb-0">
                                <i class="fa-solid fa-check-circle me-1"></i>
                                Pago completo en efectivo
                            </div>
                        @endif

                        <small class="text-muted d-block mt-2">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Presione Enter para procesar el pago
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="retrocederPaso">
                        <i class="fa-solid fa-arrow-left me-1"></i>
                        Atrás
                    </button>
                    <button type="button" class="btn btn-success" wire:click="procesarPago">
                        <i class="fa-solid fa-check me-1"></i>
                        Procesar Pago <span class="badge bg-white text-success ms-1">Enter</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal: Procesando Pago -->
    @if($procesandoPago)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-body py-5">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-4" role="status" style="width: 4rem; height: 4rem;">
                            <span class="visually-hidden">Procesando...</span>
                        </div>
                        <h4 class="text-primary mb-3">
                            <i class="fa-solid fa-clock me-2"></i>
                            Procesando pago...
                        </h4>
                        <p class="text-muted mb-0">
                            Por favor espere mientras se completa la transacción
                        </p>
                        <div class="progress mt-4" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 role="progressbar"
                                 style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

<!-- Footer fijo con totales - Oculto en móvil -->
    <footer class="fixed-footer shadow-sm py-2 d-none d-md-block">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <small class="text-muted d-block">Productos</small>
                        <strong>{{ count($items) }}</strong>
                    </div>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">Total</small>
                    <h4 class="mb-0 text-primary">Bs. {{ number_format($compra->efectivo + $compra->credito, 2) }}</h4>
                </div>
            </div>
        </div>
    </footer>

    <!-- Barra inferior fija para móvil -->
    <div class="mobile-bottom-bar d-md-none fixed-bottom bg-white shadow-lg" style="z-index: 1040; border-top: 1px solid rgba(0,0,0,0.1); padding: 8px;">
        <div class="d-flex justify-content-between align-items-center gap-2" style="height: 50px;">
            <!-- Botón Cancelar -->
            <button wire:click="cancelarCompra" class="btn btn-outline-danger h-100" style="flex: 0 0 60px; padding: 4px;">
                <i class="fa-solid fa-times d-block" style="font-size: 0.9rem;"></i>
                <small style="font-size: 0.65rem;">Cancelar</small>
            </button>

            <!-- Botón Carrito -->
            <button @click="mostrarCarritoMovil = !mostrarCarritoMovil" class="btn btn-outline-primary position-relative h-100" style="flex: 0 0 60px; padding: 4px;">
                <i class="fa-solid fa-shopping-cart d-block" style="font-size: 0.9rem;"></i>
                <small style="font-size: 0.65rem;" x-text="mostrarCarritoMovil ? 'Buscador' : 'Carrito'"></small>
                @if(count($items) > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        {{ count($items) }}
                    </span>
                @endif
            </button>

            <!-- Botón Pagar -->
            @if(count($items) > 0)
                <button wire:click="iniciarCompletarCompra" class="btn btn-success flex-fill h-100 d-flex align-items-center justify-content-center" style="padding: 4px;">
                    <i class="fa-solid fa-check me-2" style="font-size: 1.2rem;"></i>
                    <strong style="font-size: 1.2rem;">Bs {{ number_format($this->total(), 2) }}</strong>
                </button>
            @else
                <button disabled class="btn btn-secondary flex-fill h-100 d-flex align-items-center justify-content-center opacity-50" style="padding: 4px;">
                    <i class="fa-solid fa-check me-2" style="font-size: 1.2rem;"></i>
                    <strong style="font-size: 1.2rem;">Bs 0.00</strong>
                </button>
            @endif
        </div>
    </div>

    @script
        <script>
            // Hover effect para resultados de búsqueda
            document.addEventListener('DOMContentLoaded', function() {
                const style = document.createElement('style');
                style.textContent = `
                    .producto-result:hover {
                        transform: translateX(3px);
                        transition: all 0.2s;
                        background-color: #f8f9fa !important;
                    }
                `;
                document.head.appendChild(style);
            });

            // Devolver foco al buscador después de agregar producto
            $wire.on('focusBuscador', () => {
                setTimeout(() => {
                    const buscador = document.getElementById('buscadorCompra');
                    if (buscador) {
                        buscador.focus();
                    }
                }, 100);
            });

            // Modal para sumar cantidad con SweetAlert
            window.abrirModalSuma = function(index, campo, valorActual) {
                Swal.fire({
                    title: 'Agregar cantidad',
                    input: 'number',
                    inputValue: '',
                    inputAttributes: {
                        min: 0,
                        step: 1,
                        autocomplete: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Sumar',
                    cancelButtonText: 'Cancelar',
                    didOpen: () => {
                        const input = Swal.getInput();
                        input.focus();
                        input.select();
                    },
                    preConfirm: (cantidad) => {
                        if (!cantidad || cantidad < 0) {
                            Swal.showValidationMessage('Ingrese una cantidad válida');
                            return false;
                        }
                        return cantidad;
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        const cantidadASumar = parseInt(result.value);
                        const valorActualNum = parseInt(valorActual) || 0;
                        const nuevoValor = valorActualNum + cantidadASumar;

                        // Actualizar el valor en Livewire
                        $wire.set('items.' + index + '.' + campo, nuevoValor);
                        $wire.call('actualizarItem', index);
                    }
                });
            }

            // Manejar atajos de teclado en modales
            document.addEventListener('keydown', function(e) {
                // ESC para retroceder en cualquier paso
                if (e.key === 'Escape' && $wire.pasoActual > 0) {
                    e.preventDefault();
                    $wire.call('retrocederPaso');
                    return;
                }

                // Paso 1: Fecha
                if ($wire.pasoActual === 1) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $wire.call('avanzarPaso1');
                    }
                }

                // Paso 2: Proveedor (Enter para avanzar sin proveedor si no hay búsqueda activa)
                if ($wire.pasoActual === 2) {
                    const buscarInput = document.getElementById('buscarProveedor');
                    if (e.key === 'Enter' && buscarInput && buscarInput.value === '') {
                        e.preventDefault();
                        $wire.call('avanzarPaso2SinProveedor');
                    }
                }

                // Paso 3: Añadir saldo a caja (Enter para continuar)
                if ($wire.pasoActual === 3) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $wire.call('avanzarPaso3');
                    }
                }

                // Paso 4: Procesar pago (Enter para procesar)
                if ($wire.pasoActual === 4 && !$wire.procesandoPago) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $wire.call('procesarPago');
                    }
                }

                // Atajo global Ctrl+Enter para iniciar la secuencia de pago
                if (e.key === 'Enter' && e.ctrlKey && $wire.pasoActual === 0) {
                    e.preventDefault();
                    $wire.call('iniciarCompletarCompra');
                }
            });

            // Observador de cambios para mantener el foco en los inputs correctos
            Livewire.hook('morph.updated', ({ el, component }) => {
                setTimeout(() => {
                    // Enfocar input de monto cuando aparece (método de pago a crédito)
                    const montoPagoInput = document.getElementById('montoPago');
                    if (montoPagoInput && document.activeElement !== montoPagoInput) {
                        montoPagoInput.focus();
                        montoPagoInput.select();
                    }

                    // Enfocar input de monto efectivo cuando aparece
                    const montoPagoEfectivoInput = document.getElementById('montoPagoEfectivo');
                    if (montoPagoEfectivoInput && document.activeElement !== montoPagoEfectivoInput) {
                        montoPagoEfectivoInput.focus();
                        montoPagoEfectivoInput.select();
                    }

                    // Enfocar campo nombre cuando aparece formulario nuevo proveedor
                    const nuevoProveedorNombre = document.getElementById('nuevoProveedorNombre');
                    if (nuevoProveedorNombre && document.activeElement !== nuevoProveedorNombre) {
                        nuevoProveedorNombre.focus();
                    }
                }, 50);
            });
        </script>
    @endscript
</div>

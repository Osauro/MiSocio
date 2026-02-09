<div x-data="{ mostrarCarritoMovil: false }">
    <style>
        /* Control de visibilidad en móvil - Cargado inmediatamente */
        @media (max-width: 767.98px) {
            .venta-items-wrapper {
                display: none !important;
            }
            .venta-items-wrapper.show-mobile-items {
                display: block !important;
            }
            .venta-search-wrapper.hide-mobile-search {
                display: none !important;
            }
        }

        /* En desktop siempre mostrar todo */
        @media (min-width: 768px) {
            .venta-items-wrapper,
            .venta-search-wrapper {
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
                            <h3 class="d-none d-md-block mb-0">Venta #{{ $venta->numero_folio }}</h3>
                            <div class="d-flex gap-2">
                                <button wire:click="cancelarVenta" class="btn btn-secondary">
                                    <i class="fa-solid fa-times me-1"></i>
                                    <span class="d-none d-md-inline">Cancelar</span>
                                </button>
                                @if(count($items) > 0)
                                    <button type="button" wire:click="iniciarCompletarVenta" class="btn btn-success">
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
                                                                        step="0.01"
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
                                                                        step="0.01"
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
                                                <i class="fa-solid fa-shopping-cart fa-5x text-muted mb-3"></i>
                                                <p class="h5 text-muted mb-0">No hay productos agregados</p>
                                                <small class="text-muted">Usa el buscador para agregar productos</small>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                <!-- Espaciador para vista móvil -->
                                <div class="d-md-none" style="min-height: 70px;"></div>
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
                                                id="buscadorVenta"
                                                class="form-control"
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
                                                        $sinStock = $producto['stock'] <= 0;
                                                        $deshabilitado = $yaAgregado || $sinStock;
                                                    @endphp
                                                    <div class="card mb-2 border-0 shadow-sm producto-result {{ $deshabilitado ? 'disabled' : '' }}"
                                                        wire:key="producto-{{ $producto['id'] }}"
                                                        @if(!$deshabilitado)
                                                            wire:click="agregarProducto({{ $producto['id'] }})"
                                                            style="cursor: pointer;"
                                                        @else
                                                            style="cursor: not-allowed; opacity: 0.5; background-color: #f8f9fa;"
                                                        @endif>
                                                        <div class="card-body p-2">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <img src="{{ $producto['imagen'] }}"
                                                                    alt="{{ $producto['nombre'] }}"
                                                                    class="rounded"
                                                                    style="width: 40px; height: 40px; object-fit: cover;">
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold small">{{ $producto['nombre'] }}</div>
                                                                    <div class="d-flex gap-1 mt-1">
                                                                        <span class="badge {{ $sinStock ? 'bg-danger' : 'bg-info text-dark' }}">
                                                                            Stock: {{ $producto['stock_formateado'] }}
                                                                        </span>
                                                                        <span class="badge bg-secondary">
                                                                            {{ $producto['medida'] }} ({{ $producto['cantidad'] }}u)
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                @if($yaAgregado)
                                                                    <i class="fa-solid fa-check text-success"></i>
                                                                @elseif($sinStock)
                                                                    <i class="fa-solid fa-ban text-danger"></i>
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

    <!-- Modal Paso 1: Fecha de Venta -->
    @if($pasoActual === 1)
    <div class="modal fade show d-block" tabindex="-1" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.98); overflow-y: auto; z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-calendar me-2"></i>
                        Paso 1: Fecha de Venta
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fechaVenta" class="form-label fw-bold">Fecha de la venta</label>
                        <input type="date"
                            id="fechaVenta"
                            class="form-control form-control-lg text-center"
                            wire:model="fechaVenta"
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

    <!-- Modal Paso 2: Selección de Cliente -->
    @if($pasoActual === 2)
    <div class="modal fade show d-block" tabindex="-1" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.98); overflow-y: auto; z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-user me-2"></i>
                        Paso 2: Seleccionar Cliente
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="buscarCliente" class="form-label fw-bold">Buscar cliente</label>
                        <input type="text"
                            id="buscarCliente"
                            class="form-control form-control-lg text-center"
                            wire:model.live.debounce.300ms="buscarCliente"
                            placeholder="Celular (8 dígitos) o nombre..."
                            x-init="$nextTick(() => $el.focus())">
                        <small class="text-muted">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Ingresa 8 dígitos para buscar por celular o el nombre del cliente
                        </small>
                    </div>

                    <!-- Resultados de búsqueda -->
                    @if(count($clientesEncontrados) > 0)
                        <div class="list-group mb-3" style="max-height: 300px; overflow-y: auto;">
                            @foreach($clientesEncontrados as $cliente)
                                <button type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    wire:click="seleccionarCliente({{ $cliente['id'] }})">
                                    <div>
                                        <h6 class="mb-0">{{ $cliente['nombre'] }}</h6>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-phone me-1"></i>
                                            {{ $cliente['celular'] }}
                                        </small>
                                    </div>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Formulario para nuevo cliente -->
                    @if($mostrarFormNuevoCliente)
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombre</label>
                                <input type="text"
                                    id="nuevoClienteNombre"
                                    class="form-control text-center"
                                    wire:model="nuevoCliente.nombre"
                                    x-init="$nextTick(() => $el.focus())"
                                    @keydown.enter="if($el.value.trim() !== '') { $wire.call('crearYSeleccionarCliente') } else { $wire.call('avanzarPaso2SinCliente') }">
                                @error('nuevoCliente.nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Celular</label>
                                <input type="text"
                                    class="form-control text-center"
                                    wire:model="nuevoCliente.celular"
                                    @keydown.enter="if($wire.nuevoCliente.nombre && $wire.nuevoCliente.nombre.trim() !== '') { $wire.call('crearYSeleccionarCliente') } else { $wire.call('avanzarPaso2SinCliente') }">
                                @error('nuevoCliente.celular') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Dirección</label>
                                <input type="text"
                                    class="form-control text-center"
                                    wire:model="nuevoCliente.direccion"
                                    @keydown.enter="if($wire.nuevoCliente.nombre && $wire.nuevoCliente.nombre.trim() !== '') { $wire.call('crearYSeleccionarCliente') } else { $wire.call('avanzarPaso2SinCliente') }">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NIT</label>
                                <input type="text"
                                    class="form-control text-center"
                                    wire:model="nuevoCliente.nit"
                                    @keydown.enter="if($wire.nuevoCliente.nombre && $wire.nuevoCliente.nombre.trim() !== '') { $wire.call('crearYSeleccionarCliente') } else { $wire.call('avanzarPaso2SinCliente') }">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelarPagoEnProceso">
                        <i class="fa-solid fa-times me-1"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" wire:click="avanzarPaso2SinCliente">
                        <i class="fa-solid fa-forward me-1"></i>
                        Continuar sin Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Paso 3: Procesar Pago -->
    @if($pasoActual === 3 && !$procesandoPago)
    <div class="modal fade show d-block" tabindex="-1" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.98); overflow-y: auto; z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-money-bill me-2"></i>
                        Paso 3: Procesar Pago
                    </h5>
                </div>
                <div class="modal-body">
                    @php
                        $total = collect($items)->sum('subtotal');
                        $efectivoNum = floatval($montoPagoEfectivo ?? 0);
                        $onlineNum = floatval($montoPagoOnline ?? 0);
                        $totalPagado = $efectivoNum + $onlineNum;
                        $montoCredito = max(0, $total - $totalPagado);
                        $cambio = max(0, $totalPagado - $total);
                    @endphp

                    <!-- Inputs en línea: Total, Efectivo, Online, Crédito -->
                    <div class="row g-3 mb-4">
                        <!-- Primera fila: Total y Efectivo -->
                        <div class="col-6">
                            <label class="form-label fw-bold">
                                <i class="fa-solid fa-shopping-cart text-primary me-1"></i>
                                Total
                            </label>
                            <input type="text"
                                class="form-control form-control-lg text-center fw-bold"
                                value="Bs. {{ number_format($total, 2) }}"
                                disabled
                                readonly
                                style="background-color: #e3f2fd; border-color: #2196f3;">
                        </div>
                        <div class="col-6">
                            <label for="montoPagoEfectivo" class="form-label fw-bold">
                                <i class="fa-solid fa-money-bill text-success me-1"></i>
                                Efectivo
                            </label>
                            <input type="number"
                                id="montoPagoEfectivo"
                                class="form-control form-control-lg text-center"
                                wire:model.live.debounce.500ms="montoPagoEfectivo"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                x-init="$nextTick(() => { $el.focus(); $el.select(); })">
                        </div>

                        <!-- Segunda fila: Online y Crédito/Cambio -->
                        <div class="col-6">
                            <label for="montoPagoOnline" class="form-label fw-bold">
                                <i class="fa-solid fa-qrcode text-info me-1"></i>
                                Online
                            </label>
                            <input type="number"
                                id="montoPagoOnline"
                                class="form-control form-control-lg text-center"
                                wire:model.live.debounce.500ms="montoPagoOnline"
                                wire:keydown.enter="procesarPago"
                                min="0"
                                step="0.01"
                                placeholder="0.00">
                        </div>
                        <div class="col-6">
                            @if($cambio > 0)
                                <label class="form-label fw-bold">
                                    <i class="fa-solid fa-coins text-warning me-1"></i>
                                    Cambio
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg text-center fw-bold"
                                    value="Bs. {{ number_format($cambio, 2) }}"
                                    disabled
                                    readonly
                                    style="background-color: #fff3e0; border-color: #ff9800;">
                            @else
                                <label class="form-label fw-bold">
                                    <i class="fa-solid fa-credit-card text-danger me-1"></i>
                                    Crédito
                                    @if($clienteSeleccionado === null && $montoCredito > 0)
                                        <i class="fa-solid fa-exclamation-triangle text-danger ms-1" title="Requiere cliente"></i>
                                    @endif
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg text-center fw-bold"
                                    value="Bs. {{ number_format($montoCredito, 2) }}"
                                    disabled
                                    readonly
                                    style="background-color: {{ $montoCredito > 0 ? '#ffebee' : '#f8f9fa' }}; border-color: {{ $montoCredito > 0 ? '#f44336' : '#dee2e6' }};">
                            @endif
                        </div>
                    </div>

                    <!-- Resumen en línea -->
                    <div class="d-flex justify-content-around align-items-center p-3 bg-light rounded mb-3">
                        <div class="text-center">
                            <small class="text-muted d-block">Efectivo</small>
                            <strong class="text-success fs-5">Bs. {{ number_format($efectivoNum, 2) }}</strong>
                        </div>
                        <div class="text-center">
                            <small class="text-muted d-block">Online</small>
                            <strong class="text-info fs-5">Bs. {{ number_format($onlineNum, 2) }}</strong>
                        </div>
                        @if($cambio > 0)
                            <div class="text-center">
                                <small class="text-muted d-block">Cambio</small>
                                <strong class="text-warning fs-5">Bs. {{ number_format($cambio, 2) }}</strong>
                            </div>
                        @else
                            <div class="text-center">
                                <small class="text-muted d-block">Crédito</small>
                                <strong class="text-danger fs-5">Bs. {{ number_format($montoCredito, 2) }}</strong>
                            </div>
                        @endif
                    </div>

                    <!-- Alertas según el estado del pago -->
                    @if($montoCredito > 0 && $clienteSeleccionado === null)
                        <div class="alert alert-danger mb-0">
                            <i class="fa-solid fa-exclamation-triangle me-1"></i>
                            <strong>No se puede vender a crédito sin cliente.</strong><br>
                            Complete el pago en efectivo/online o vuelva al Paso 2 para seleccionar un cliente.
                        </div>
                    @elseif($montoCredito > 0 && $clienteSeleccionado !== null)
                        <div class="alert alert-warning mb-0">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Se registrará un crédito de <strong>Bs. {{ number_format($montoCredito, 2) }}</strong>
                        </div>
                    @elseif($cambio > 0)
                        <div class="alert alert-info mb-0">
                            <i class="fa-solid fa-coins me-1"></i>
                            Cambio a entregar: <strong>Bs. {{ number_format($cambio, 2) }}</strong>
                        </div>
                    @elseif($totalPagado == $total)
                        <div class="alert alert-success mb-0">
                            <i class="fa-solid fa-check-circle me-1"></i>
                            Pago completo
                        </div>
                    @endif
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
    <div class="modal fade show d-block" tabindex="-1" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.98); overflow-y: auto; z-index: 9999;">
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
                    <h4 class="mb-0 text-primary">Bs. {{ number_format($this->total(), 2) }}</h4>
                </div>
            </div>
        </div>
    </footer>

    <!-- Barra inferior fija para móvil -->
    <div class="mobile-bottom-bar d-md-none fixed-bottom bg-white shadow-lg" style="z-index: 1040; border-top: 1px solid rgba(0,0,0,0.1); padding: 8px;">
        <div class="d-flex justify-content-between align-items-center gap-2" style="height: 50px;">
            <!-- Botón Cancelar -->
            <button wire:click="cancelarVenta" class="btn btn-outline-danger h-100" style="flex: 0 0 60px; padding: 4px;">
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
                <button wire:click="iniciarCompletarVenta" class="btn btn-success flex-fill h-100 d-flex align-items-center justify-content-center" style="padding: 4px;">
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
                    const buscador = document.getElementById('buscadorVenta');
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

                // Paso 2: Cliente (Enter para avanzar sin cliente si no hay búsqueda activa)
                if ($wire.pasoActual === 2) {
                    const buscarInput = document.getElementById('buscarCliente');
                    if (e.key === 'Enter' && buscarInput && buscarInput.value === '') {
                        e.preventDefault();
                        $wire.call('avanzarPaso2SinCliente');
                    }
                }

                // Paso 3: Procesar pago (Enter para procesar)
                if ($wire.pasoActual === 3 && !$wire.procesandoPago) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $wire.call('procesarPago');
                    }
                }

                // Atajo global Ctrl+Enter para iniciar la secuencia de pago
                if (e.key === 'Enter' && e.ctrlKey && $wire.pasoActual === 0) {
                    e.preventDefault();
                    $wire.call('iniciarCompletarVenta');
                }
            });

            // Observador de cambios para mantener el foco en los inputs correctos
            Livewire.hook('morph.updated', ({ el, component }) => {
                setTimeout(() => {
                    // Enfocar input de monto efectivo cuando aparece
                    const montoPagoEfectivoInput = document.getElementById('montoPagoEfectivo');
                    if (montoPagoEfectivoInput && document.activeElement !== montoPagoEfectivoInput && document.activeElement.id !== 'montoPagoOnline') {
                        montoPagoEfectivoInput.focus();
                        montoPagoEfectivoInput.select();
                    }

                    // Enfocar campo nombre cuando aparece formulario nuevo cliente
                    const nuevoClienteNombre = document.getElementById('nuevoClienteNombre');
                    if (nuevoClienteNombre && document.activeElement !== nuevoClienteNombre) {
                        nuevoClienteNombre.focus();
                    }
                }, 50);
            });

            // ========== IMPRESIÓN DE TICKET DE VENTA ==========
            $wire.on('imprimir-ticket-venta', async (data) => {
                const ticket = data;

                // Si no hay impresora configurada, solo redirigir
                if (!ticket.impresora) {
                    Swal.fire({
                        icon: 'info',
                        title: '¿Imprimir ticket?',
                        text: 'No hay impresora configurada. ¿Deseas imprimir por navegador?',
                        showCancelButton: true,
                        confirmButtonText: 'Imprimir',
                        cancelButtonText: 'Solo salir'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            imprimirTicketNavegador(ticket);
                        }
                        window.location.href = '{{ route("ventas") }}';
                    });
                    return;
                }

                // Intentar imprimir con QZ Tray
                if (typeof qz !== 'undefined') {
                    try {
                        if (!qz.websocket.isActive()) {
                            await qz.websocket.connect();
                        }

                        const printerConfig = qz.configs.create(ticket.impresora);
                        const comandos = generarComandosTicketVenta(ticket);

                        await qz.print(printerConfig, [{
                            type: 'raw',
                            format: 'plain',
                            data: comandos
                        }]);

                        console.log('Ticket impreso exitosamente');
                    } catch (e) {
                        console.error('Error imprimiendo:', e);
                        // Fallback a navegador
                        imprimirTicketNavegador(ticket);
                    }
                } else {
                    // Sin QZ Tray, usar navegador
                    imprimirTicketNavegador(ticket);
                }

                // Redirigir después de imprimir
                setTimeout(() => {
                    window.location.href = '{{ route("ventas") }}';
                }, 500);
            });

            // Generar comandos ESC/POS para ticket de venta
            function generarComandosTicketVenta(ticket) {
                const ESC = '\x1B';
                const GS = '\x1D';
                const ancho = ticket.ancho || 48;
                const linea = '='.repeat(ancho);
                const lineaSimple = '-'.repeat(ancho);

                let cmd = '';

                // Inicializar
                cmd += ESC + '@';

                // Centrar y negrita para título
                cmd += ESC + 'a\x01';  // Centrar
                cmd += ESC + 'E\x01';  // Negrita ON
                cmd += (ticket.nombre_tienda || 'MI TIENDA').toUpperCase() + '\n';
                cmd += ESC + 'E\x00';  // Negrita OFF

                // Dirección y teléfono
                if (ticket.direccion) cmd += ticket.direccion + '\n';
                if (ticket.telefono) cmd += 'Tel: ' + ticket.telefono + '\n';
                if (ticket.nit) cmd += 'NIT: ' + ticket.nit + '\n';

                cmd += linea + '\n';
                cmd += ESC + 'E\x01';
                cmd += '**** TICKET DE VENTA ****\n';
                cmd += ESC + 'E\x00';
                cmd += linea + '\n';

                // Alinear izquierda para datos
                cmd += ESC + 'a\x00';
                cmd += 'FECHA:   ' + ticket.fecha + '\n';
                cmd += 'USUARIO: ' + ticket.usuario + '\n';
                cmd += 'CLIENTE: ' + ticket.cliente + '\n';
                cmd += 'FOLIO:   #' + ticket.folio + '\n';

                cmd += lineaSimple + '\n';
                cmd += ESC + 'a\x01';  // Centrar
                cmd += 'D E T A L L E\n';
                cmd += lineaSimple + '\n';
                cmd += ESC + 'a\x00';  // Izquierda

                // Items
                ticket.items.forEach(item => {
                    const cantStr = item.cantidad + 'u';
                    const precioStr = item.subtotal.toFixed(2);
                    const nombre = item.nombre.substring(0, ancho - cantStr.length - precioStr.length - 4);

                    // Línea: cantidad + nombre truncado + precio alineado a la derecha
                    const espacios = ancho - cantStr.length - nombre.length - precioStr.length - 1;
                    cmd += cantStr + ' ' + nombre + '.'.repeat(Math.max(1, espacios)) + precioStr + '\n';
                });

                cmd += lineaSimple + '\n';

                // Totales alineados a la derecha
                const formatTotal = (label, valor) => {
                    const valorStr = valor.toFixed(2);
                    const espacios = ancho - label.length - valorStr.length;
                    return label + ' '.repeat(Math.max(1, espacios)) + valorStr + '\n';
                };

                cmd += ESC + 'E\x01';  // Negrita
                cmd += formatTotal('TOTAL:', ticket.total);
                cmd += ESC + 'E\x00';  // Negrita OFF

                if (ticket.efectivo > 0) cmd += formatTotal('EFECTIVO:', ticket.efectivo);
                if (ticket.online > 0) cmd += formatTotal('ONLINE:', ticket.online);
                if (ticket.credito > 0) cmd += formatTotal('CREDITO:', ticket.credito);
                if (ticket.cambio > 0) cmd += formatTotal('CAMBIO:', ticket.cambio);

                cmd += linea + '\n';

                // Mensaje final centrado
                cmd += ESC + 'a\x01';
                cmd += '\n';
                cmd += 'GRACIAS POR SU COMPRA\n';
                cmd += '\n\n';

                // Corte de papel
                if (ticket.corte) {
                    cmd += GS + 'V\x00';
                }

                // Abrir cajón
                if (ticket.abrir_cajon) {
                    cmd += ESC + 'p\x00\x19\xFA';
                }

                return cmd;
            }

            // Fallback: imprimir ticket por navegador
            function imprimirTicketNavegador(ticket) {
                const ancho = ticket.papel === '58mm' ? '58mm' : '80mm';

                let itemsHtml = ticket.items.map(item =>
                    `<div class="item">
                        <span>${item.cantidad}u ${item.nombre}</span>
                        <span>${item.subtotal.toFixed(2)}</span>
                    </div>`
                ).join('');

                const html = `
                    <html>
                    <head>
                        <title>Ticket #${ticket.folio}</title>
                        <style>
                            body { font-family: 'Courier New', monospace; font-size: 12px; margin: 0; padding: 10px; width: ${ancho}; }
                            .center { text-align: center; }
                            .bold { font-weight: bold; }
                            .linea { border-top: 1px dashed #000; margin: 5px 0; }
                            .item { display: flex; justify-content: space-between; }
                            .total-row { display: flex; justify-content: space-between; }
                            @media print { @page { margin: 0; } body { width: 100%; } }
                        </style>
                    </head>
                    <body>
                        <div class="center bold">${ticket.nombre_tienda || 'MI TIENDA'}</div>
                        ${ticket.direccion ? '<div class="center">' + ticket.direccion + '</div>' : ''}
                        ${ticket.telefono ? '<div class="center">Tel: ' + ticket.telefono + '</div>' : ''}
                        <div class="linea"></div>
                        <div class="center bold">TICKET DE VENTA #${ticket.folio}</div>
                        <div class="linea"></div>
                        <div>Fecha: ${ticket.fecha}</div>
                        <div>Usuario: ${ticket.usuario}</div>
                        <div>Cliente: ${ticket.cliente}</div>
                        <div class="linea"></div>
                        <div class="center">DETALLE</div>
                        <div class="linea"></div>
                        ${itemsHtml}
                        <div class="linea"></div>
                        <div class="total-row bold"><span>TOTAL:</span><span>${ticket.total.toFixed(2)}</span></div>
                        ${ticket.efectivo > 0 ? '<div class="total-row"><span>Efectivo:</span><span>' + ticket.efectivo.toFixed(2) + '</span></div>' : ''}
                        ${ticket.online > 0 ? '<div class="total-row"><span>Online:</span><span>' + ticket.online.toFixed(2) + '</span></div>' : ''}
                        ${ticket.credito > 0 ? '<div class="total-row"><span>Crédito:</span><span>' + ticket.credito.toFixed(2) + '</span></div>' : ''}
                        ${ticket.cambio > 0 ? '<div class="total-row"><span>Cambio:</span><span>' + ticket.cambio.toFixed(2) + '</span></div>' : ''}
                        <div class="linea"></div>
                        <div class="center">GRACIAS POR SU COMPRA</div>
                    </body>
                    </html>
                `;

                const printWindow = window.open('', '_blank', 'width=400,height=600');
                printWindow.document.write(html);
                printWindow.document.close();
                printWindow.onload = function() {
                    printWindow.print();
                    printWindow.onafterprint = function() { printWindow.close(); };
                };
            }
        </script>
    @endscript
</div>

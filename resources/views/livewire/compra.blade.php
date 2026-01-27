<div>
    <div class="container-fluid">
        <div class="row starter-main">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header card-no-border pb-0">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Compra #{{ $compra->id }}</h3>
                            <div class="d-flex gap-2">
                                <a href="{{ route('tenant.compras') }}" class="btn btn-secondary">
                                    <i class="fa-solid fa-times me-1"></i>
                                    <span class="d-none d-md-inline">Cancelar</span>
                                </a>
                                @if(count($items) > 0)
                                    <button type="button" class="btn btn-success">
                                        <i class="fa-solid fa-check me-1"></i>
                                        <span class="d-none d-md-inline">Completar</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-2">
                        <div class="row">
                            <!-- Columna de Items (Izquierda) -->
                            <div class="col-md-8 col-lg-9">
                                <div class="row g-2">
                                    @forelse($items as $index => $item)
                                        <div class="col-md-6 col-lg-4 col-xl-4" wire:key="item-{{ $item['id'] }}">
                                            <div class="card mb-0 shadow-sm h-100">
                                                <div class="card-body p-2">
                                                    <!-- Nombre y Botón -->
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-0 text-truncate">{{ $item['nombre'] }}</h6>
                                                        </div>
                                                        <a href="javascript:void(0)" class="text-danger ms-1"
                                                            wire:click="eliminarItem({{ $index }})"
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
                                                                <div class="col-6">
                                                                    <label class="form-label mb-1 small fw-bold">Enteros</label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm text-center"
                                                                        wire:model.live="items.{{ $index }}.enteros"
                                                                        wire:change="actualizarItem({{ $index }})"
                                                                        min="0"
                                                                        placeholder="0">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label mb-1 small fw-bold">Unidades</label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm text-center"
                                                                        wire:model.live="items.{{ $index }}.unidades"
                                                                        wire:change="actualizarItem({{ $index }})"
                                                                        min="0"
                                                                        max="{{ $item['cantidad_por_medida'] - 1 }}"
                                                                        placeholder="0">
                                                                </div>

                                                                <!-- Fila 2: Precio y Subtotal -->
                                                                <div class="col-6">
                                                                    <label class="form-label mb-1 small fw-bold">Precio</label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm text-end"
                                                                        wire:model.live="items.{{ $index }}.precio"
                                                                        wire:change="actualizarItem({{ $index }})"
                                                                        step="0.01"
                                                                        min="0"
                                                                        placeholder="0.00">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label mb-1 small fw-bold">Subtotal</label>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm text-end fw-bold"
                                                                        value="Bs. {{ number_format($item['subtotal'], 2) }}"
                                                                        readonly
                                                                        style="background-color: #e9ecef;">
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
                            </div>

                            <!-- Columna de Buscador (Derecha) -->
                            <div class="col-md-4 col-lg-3">
                                <div class="card sticky-top shadow-sm" style="top: 80px;">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="fa-solid fa-search me-2"></i>
                                            Buscar Productos
                                        </h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <!-- Input de Búsqueda -->
                                        <input type="text"
                                            class="form-control mb-2"
                                            wire:model.live.debounce.300ms="buscar"
                                            placeholder="Nombre o código..."
                                            autofocus>

                                        <!-- Resultados -->
                                        <div class="search-results" style="max-height: 500px; overflow-y: auto;">
                                            @if(strlen($buscar) >= 2)
                                                @forelse($productosEncontrados as $producto)
                                                    <div class="card mb-2 border-0 shadow-sm producto-result"
                                                        wire:key="producto-{{ $producto['id'] }}"
                                                        wire:click="agregarProducto({{ $producto['id'] }})"
                                                        style="cursor: pointer;">
                                                        <div class="card-body p-2">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <img src="{{ $producto['photo_url'] }}"
                                                                    alt="{{ $producto['nombre'] }}"
                                                                    class="rounded"
                                                                    style="width: 40px; height: 40px; object-fit: cover;">
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold small">{{ $producto['nombre'] }}</div>
                                                                    <small class="text-muted">
                                                                        Stock: {{ $producto['stock'] }} {{ $producto['medida'] ?? 'u' }}
                                                                    </small>
                                                                </div>
                                                                <i class="fa-solid fa-plus text-primary"></i>
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

    <!-- Footer fijo con totales -->
    <footer class="fixed-footer shadow-sm py-2">
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
        </script>
    @endscript
</div>

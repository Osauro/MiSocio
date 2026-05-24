<div
    x-data="{
        scanner: null,
        scanning: false,

        startScanner() {
            if (this.scanning) return;
            this.scanning = true;
            const html5QrCode = new Html5Qrcode(''qr-reader'');
            this.scanner = html5QrCode;
            html5QrCode.start(
                { facingMode: ''environment'' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    this.stopScanner();
                    $wire.procesarUrl(decodedText);
                },
                () => {}
            ).catch(() => { this.scanning = false; });
        },

        stopScanner() {
            if (this.scanner) { this.scanner.stop().catch(() => {}); this.scanner = null; }
            this.scanning = false;
        }
    }"
    x-on:abrir-importar-qr.window="$wire.abrir()"
    x-on:qr-modal-abierto.window="
        if (typeof Html5Qrcode === ''undefined'') {
            const s = document.createElement(''script'');
            s.src = ''https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js'';
            s.onload = () => $nextTick(() => startScanner());
            document.head.appendChild(s);
        } else { $nextTick(() => startScanner()); }
    "
    x-on:qr-modal-cerrado.window="stopScanner()"
    x-on:compra-qr-completada.window="$dispatch(''actualizar-lista-compras'')"
    x-on:procesar-siguiente.window="$wire.procesarSiguiente()"
>

@if($abierto)
<div class="modal-backdrop fade show" style="z-index:1055;"></div>
<div class="modal fade show d-block" tabindex="-1" style="z-index:1060; overflow-y:auto;">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content shadow-lg border-0">

    {{-- Header --}}
    <div class="modal-header text-white" style="background-color:var(--theme-default,#7366ff);">
        <h5 class="modal-title mb-0">
            <i class="fa-solid fa-qrcode me-2"></i>Importar Compra por QR
        </h5>
        <button type="button" class="btn-close btn-close-white" wire:click="cerrar"></button>
    </div>

    {{-- Body --}}
    <div class="modal-body p-3">

        @if($fase === ''scanner'')
        <div class="text-center">
            <p class="text-muted mb-3">Apunta la cámara al código QR de la factura.</p>
            @if($errorUrl)
                <div class="alert alert-danger py-2 mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $errorUrl }}
                </div>
            @endif
            <div id="qr-reader" class="mx-auto rounded overflow-hidden" style="max-width:320px;"></div>
            <p class="text-muted mt-3 small">O pega la URL directamente:</p>
            <div class="input-group mt-1" style="max-width:420px;margin:0 auto;">
                <input type="url" class="form-control" placeholder="https://..." wire:model="urlEscaneada">
                <button class="btn btn-primary" wire:click="procesarUrl(urlEscaneada)"
                    wire:loading.attr="disabled" wire:target="procesarUrl">
                    <span wire:loading wire:target="procesarUrl" class="spinner-border spinner-border-sm"></span>
                    <i wire:loading.remove wire:target="procesarUrl" class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>
        @endif

        @if($fase === ''procesando'')
        @php $total = count($productosQueue); @endphp
        <div class="mb-3">
            {{-- Barra de progreso --}}
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">Procesando productos...</small>
                <small class="text-muted fw-bold">{{ $productoIndex }} / {{ $total }}</small>
            </div>
            <div class="progress mb-3" style="height:6px;">
                <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                    style="width:{{ $total > 0 ? round(($productoIndex / $total) * 100) : 0 }}%"></div>
            </div>

            {{-- Producto actual (spinner animado) --}}
            @if($productoActual)
            <div class="text-center py-3">
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                <span class="fw-semibold text-primary">{{ $productoActual }}</span>
            </div>
            @endif

            {{-- Lista de ya procesados --}}
            <div class="overflow-auto" style="max-height:260px;" id="qr-log">
                @foreach(array_reverse($logItems) as $item)
                <div class="d-flex align-items-center gap-2 py-1 border-bottom"
                    style="animation: fadeInDown .25s ease;">
                    @if($item[''error''] ?? false)
                        <i class="fa-solid fa-circle-xmark text-danger"></i>
                    @elseif($item[''nuevo''] ?? false)
                        <i class="fa-solid fa-circle-plus text-warning"></i>
                    @else
                        <i class="fa-solid fa-circle-check text-success"></i>
                    @endif
                    <span class="flex-grow-1 small text-truncate">{{ $item[''nombre''] }}</span>
                    @if($item[''nuevo''] ?? false)
                        <span class="badge bg-warning text-dark">Nuevo</span>
                    @endif
                    <span class="small text-muted text-nowrap">Bs. {{ number_format($item[''subtotal''], 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($fase === ''fondos'')
        <div class="mb-2">
            {{-- Lista resumida --}}
            <div class="overflow-auto mb-3" style="max-height:180px;" id="qr-log">
                @foreach(array_reverse($logItems) as $item)
                <div class="d-flex align-items-center gap-2 py-1 border-bottom">
                    @if($item[''error''] ?? false)
                        <i class="fa-solid fa-circle-xmark text-danger"></i>
                    @elseif($item[''nuevo''] ?? false)
                        <i class="fa-solid fa-circle-plus text-warning"></i>
                    @else
                        <i class="fa-solid fa-circle-check text-success"></i>
                    @endif
                    <span class="flex-grow-1 small text-truncate">{{ $item[''nombre''] }}</span>
                    @if($item[''nuevo''] ?? false)
                        <span class="badge bg-warning text-dark">Nuevo</span>
                    @endif
                    <span class="small text-muted text-nowrap">Bs. {{ number_format($item[''subtotal''], 2) }}</span>
                </div>
                @endforeach
            </div>

            <div class="alert alert-warning py-2 mb-2">
                <div class="row text-center g-0">
                    <div class="col-4">
                        <div class="small text-muted">Saldo en caja</div>
                        <div class="fw-bold">Bs. {{ number_format($saldoCaja, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Total compra</div>
                        <div class="fw-bold">Bs. {{ number_format($totalCompra, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Faltante</div>
                        <div class="fw-bold text-danger">Bs. {{ number_format(max(0, $totalCompra - $saldoCaja), 2) }}</div>
                    </div>
                </div>
            </div>

            <label class="form-label fw-semibold small">Añadir fondos a caja</label>
            <div class="input-group">
                <span class="input-group-text">Bs.</span>
                <input type="number" class="form-control" min="0" step="0.5"
                    wire:model="montoAnadir" placeholder="0.00">
                <button class="btn btn-success" wire:click="anadirFondosYContinuar"
                    wire:loading.attr="disabled" wire:target="anadirFondosYContinuar">
                    <span wire:loading wire:target="anadirFondosYContinuar" class="spinner-border spinner-border-sm me-1"></span>
                    <i wire:loading.remove wire:target="anadirFondosYContinuar" class="fa-solid fa-plus me-1"></i>
                    Añadir y continuar
                </button>
            </div>
            @if($errorFondos)
                <div class="text-danger small mt-1">{{ $errorFondos }}</div>
            @endif
            <div class="mt-2">
                <button class="btn btn-outline-secondary btn-sm" wire:click="omitirFondos">
                    Continuar sin añadir (resto a crédito)
                </button>
            </div>
        </div>
        @endif

        @if($fase === ''confirmar'')
        @php
            $efectivoAplicar = min($totalCompra, $saldoCaja);
            $creditoAplicar  = max(0, $totalCompra - $saldoCaja);
        @endphp
        <div class="mb-2">
            {{-- Lista resumida --}}
            <div class="overflow-auto mb-3" style="max-height:200px;">
                @foreach(array_reverse($logItems) as $item)
                <div class="d-flex align-items-center gap-2 py-1 border-bottom">
                    @if($item[''error''] ?? false)
                        <i class="fa-solid fa-circle-xmark text-danger"></i>
                    @elseif($item[''nuevo''] ?? false)
                        <i class="fa-solid fa-circle-plus text-warning"></i>
                    @else
                        <i class="fa-solid fa-circle-check text-success"></i>
                    @endif
                    <span class="flex-grow-1 small text-truncate">{{ $item[''nombre''] }}</span>
                    @if($item[''nuevo''] ?? false)
                        <span class="badge bg-warning text-dark">Nuevo</span>
                    @endif
                    <span class="small text-muted text-nowrap">Bs. {{ number_format($item[''subtotal''], 2) }}</span>
                </div>
                @endforeach
            </div>

            <div class="alert alert-info py-2 mb-2">
                <div class="row text-center g-0">
                    <div class="col-4">
                        <div class="fw-bold fs-5">{{ $productosBuscados }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-warning">{{ $productosCreados }}</div>
                        <small class="text-muted">Nuevos</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-success">Bs. {{ number_format($totalCompra, 2) }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>

            @if($creditoAplicar > 0)
                <div class="alert alert-warning py-2 small mb-0">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    Efectivo: <strong>Bs. {{ number_format($efectivoAplicar, 2) }}</strong>
                    &nbsp;+&nbsp;Crédito: <strong>Bs. {{ number_format($creditoAplicar, 2) }}</strong>
                </div>
            @else
                <div class="alert alert-success py-2 small mb-0">
                    <i class="fa-solid fa-check me-1"></i>
                    Pago total en efectivo: <strong>Bs. {{ number_format($efectivoAplicar, 2) }}</strong>
                </div>
            @endif
        </div>
        @endif

        @if($fase === ''resumen'')
        <div class="text-center py-3">
            <i class="fa-solid fa-circle-check fa-4x text-success mb-3"></i>
            <h4 class="text-success mb-3">¡Compra importada exitosamente!</h4>
            <div class="row text-center g-0 mb-3">
                <div class="col-4">
                    <div class="fw-bold fs-4">{{ $productosBuscados }}</div>
                    <small class="text-muted">Productos</small>
                </div>
                <div class="col-4">
                    <div class="fw-bold fs-4 text-warning">{{ $productosCreados }}</div>
                    <small class="text-muted">Nuevos</small>
                </div>
                <div class="col-4">
                    <div class="fw-bold fs-4 text-success">Bs. {{ number_format($totalCompra, 2) }}</div>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /modal-body --}}

    {{-- Footer --}}
    <div class="modal-footer py-2">
        @if($fase === ''scanner'')
            <button class="btn btn-secondary" wire:click="cerrar">Cancelar</button>
        @elseif($fase === ''procesando'')
            <span class="text-muted small">
                <span class="spinner-border spinner-border-sm me-1"></span>
                Procesando {{ $productoIndex }}/{{ count($productosQueue) }}...
            </span>
        @elseif($fase === ''fondos'')
            <button class="btn btn-secondary" wire:click="cerrar">Cancelar</button>
        @elseif($fase === ''confirmar'')
            <button class="btn btn-secondary" wire:click="cerrar">Cancelar</button>
            <button class="btn btn-success" wire:click="finalizarCompra"
                wire:loading.attr="disabled" wire:target="finalizarCompra">
                <span wire:loading wire:target="finalizarCompra" class="spinner-border spinner-border-sm me-1"></span>
                <i wire:loading.remove wire:target="finalizarCompra" class="fa-solid fa-check me-1"></i>
                Confirmar y Finalizar
            </button>
        @elseif($fase === ''resumen'')
            <button class="btn btn-primary" wire:click="irACompra">
                <i class="fa-solid fa-list me-1"></i>Ver Compras
            </button>
            <button class="btn btn-outline-secondary" wire:click="cerrar">Cerrar</button>
        @endif
    </div>

</div></div></div>
@endif

<style>
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
</div>

<div
    x-data="{
        scanner: null,
        scanning: false,
        startScanner() {
            if (this.scanning) return;
            this.scanning = true;
            const html5QrCode = new Html5Qrcode('qr-reader');
            this.scanner = html5QrCode;
            html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => { this.stopScanner(); $wire.procesarUrl(decodedText); },
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
        if (typeof Html5Qrcode === 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
            s.onload = () => $nextTick(() => startScanner());
            document.head.appendChild(s);
        } else { $nextTick(() => startScanner()); }
    "
    x-on:qr-modal-cerrado.window="stopScanner()"
    x-on:procesar-siguiente.window="$wire.procesarSiguiente()"
>

@if($abierto)
<div class="modal-backdrop fade show" style="z-index:1055;"></div>
<div class="modal fade show d-block" tabindex="-1" style="z-index:1060; overflow-y:auto;">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content shadow-lg border-0">

    <div class="modal-header text-white" style="background-color:var(--theme-default,#7366ff);">
        <h5 class="modal-title mb-0">
            <i class="fa-solid fa-qrcode me-2"></i>Importar Compra por QR
        </h5>
        <button type="button" class="btn-close btn-close-white" wire:click="cerrar"></button>
    </div>

    <div class="modal-body p-3">

        @if($fase === 'scanner')
        <div class="text-center">
            <p class="text-muted mb-3">Apunta la camara al codigo QR de la factura.</p>
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

        @if($fase === 'procesando')
        @php $total = count($productosQueue); @endphp
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">Procesando productos...</small>
                <small class="text-muted fw-bold">{{ $productoIndex }} / {{ $total }}</small>
            </div>
            <div class="progress mb-3" style="height:6px;">
                <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                    style="width:{{ $total > 0 ? round(($productoIndex / $total) * 100) : 0 }}%"></div>
            </div>

            @if($productoActual)
            <div class="text-center py-3">
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                <span class="fw-semibold text-primary">{{ $productoActual }}</span>
            </div>
            @endif

            <div class="overflow-auto" style="max-height:260px;">
                @foreach(array_reverse($logItems) as $item)
                <div class="d-flex align-items-center gap-2 py-1 border-bottom"
                    style="animation: fadeInDown .25s ease;">
                    @if($item['error'] ?? false)
                        <i class="fa-solid fa-circle-xmark text-danger"></i>
                    @elseif($item['nuevo'] ?? false)
                        <i class="fa-solid fa-circle-plus text-warning"></i>
                    @else
                        <i class="fa-solid fa-circle-check text-success"></i>
                    @endif
                    <span class="flex-grow-1 small text-truncate">{{ $item['nombre'] }}</span>
                    @if($item['nuevo'] ?? false)
                        <span class="badge bg-warning text-dark">Nuevo</span>
                    @endif
                    <span class="small text-muted text-nowrap">Bs. {{ number_format($item['subtotal'], 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    <div class="modal-footer py-2">
        @if($fase === 'scanner')
            <button class="btn btn-secondary" wire:click="cerrar">Cancelar</button>
        @elseif($fase === 'procesando')
            <span class="text-muted small">
                <span class="spinner-border spinner-border-sm me-1"></span>
                Procesando {{ $productoIndex }}/{{ count($productosQueue) }}...
            </span>
            <button class="btn btn-outline-danger btn-sm" wire:click="cerrar">Cancelar</button>
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

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
                (decodedText) => {
                    this.stopScanner();
                    $wire.procesarUrl(decodedText);
                },
                (err) => {}
            ).catch(err => {
                console.error('QR start error', err);
                this.scanning = false;
            });
        },

        stopScanner() {
            if (this.scanner) {
                this.scanner.stop().catch(() => {});
                this.scanner = null;
            }
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
        } else {
            $nextTick(() => startScanner());
        }
    "
    x-on:qr-modal-cerrado.window="stopScanner()"
    x-on:compra-qr-completada.window="$dispatch('actualizar-lista-compras')"
    x-on:log-actualizado.window="$nextTick(() => {
        const el = document.getElementById('qr-log');
        if (el) el.scrollTop = el.scrollHeight;
    })"
>

    @if($abierto)
    {{-- Backdrop --}}
    <div class="modal-backdrop fade show" style="z-index: 1055;"></div>

    {{-- Modal --}}
    <div class="modal fade show d-block" tabindex="-1" style="z-index: 1060; overflow-y: auto;">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">

                {{-- Header --}}
                <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                    <h5 class="modal-title mb-0">
                        <i class="fa-solid fa-qrcode me-2"></i>
                        Importar Compra por QR
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cerrar"></button>
                </div>

                <div class="modal-body p-3" style="position: relative; min-height: 120px;">

                    {{-- Overlay de carga (activo mientras Livewire procesa) --}}
                    <div wire:loading
                        wire:target="procesarUrl, finalizarCompra, añadirFondosYContinuar"
                        style="position:absolute; inset:0; background:rgba(255,255,255,0.88); z-index:10;
                               display:flex; flex-direction:column; align-items:center; justify-content:center;">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="text-muted mb-1">Procesando, por favor espera...</p>
                        <small class="text-muted">Creando productos y registrando compra</small>
                    </div>

                    {{-- ══════════ FASE: scanner ══════════ --}}
                    @if($fase === 'scanner')
                        <div class="text-center">
                            <p class="text-muted mb-3">
                                Apunta la cámara al código QR que contiene la URL del JSON de productos.
                            </p>

                            @if($errorUrl)
                                <div class="alert alert-danger py-2 mb-3">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    {{ $errorUrl }}
                                </div>
                            @endif

                            <div id="qr-reader" class="mx-auto rounded overflow-hidden" style="max-width: 320px;"></div>

                            <p class="text-muted mt-3 small">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                También puedes pegar la URL directamente:
                            </p>
                            <div class="input-group mt-1" style="max-width: 420px; margin: 0 auto;">
                                <input type="url"
                                    class="form-control"
                                    placeholder="https://..."
                                    wire:model="urlEscaneada"
                                    wire:keydown.enter="procesarUrl(urlEscaneada)">
                                <button class="btn btn-primary" wire:click="procesarUrl(urlEscaneada)">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- ══════════ FASE: procesando ══════════ --}}
                    @if($fase === 'procesando')
                        <div class="text-center mb-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Procesando productos...</p>
                        </div>
                        <div id="qr-log" class="rounded p-2 small overflow-auto"
                            style="background:#f8f9fa; max-height:300px; font-family:monospace;">
                            @foreach($logProceso as $entry)
                                <div class="
                                    @if($entry['tipo'] === 'success') text-success
                                    @elseif($entry['tipo'] === 'warning') text-warning
                                    @elseif($entry['tipo'] === 'error') text-danger
                                    @else text-secondary
                                    @endif
                                ">
                                    @if($entry['tipo'] === 'success') ✔
                                    @elseif($entry['tipo'] === 'warning') ⚠
                                    @elseif($entry['tipo'] === 'error') ✖
                                    @else →
                                    @endif
                                    {{ $entry['mensaje'] }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- ══════════ FASE: fondos ══════════ --}}
                    @if($fase === 'fondos')
                        <div class="mb-3">
                            <div id="qr-log" class="rounded p-2 small overflow-auto mb-3"
                                style="background:#f8f9fa; max-height:220px; font-family:monospace;">
                                @foreach($logProceso as $entry)
                                    <div class="
                                        @if($entry['tipo'] === 'success') text-success
                                        @elseif($entry['tipo'] === 'warning') text-warning
                                        @elseif($entry['tipo'] === 'error') text-danger
                                        @else text-secondary
                                        @endif
                                    ">
                                        @if($entry['tipo'] === 'success') ✔
                                        @elseif($entry['tipo'] === 'warning') ⚠
                                        @elseif($entry['tipo'] === 'error') ✖
                                        @else →
                                        @endif
                                        {{ $entry['mensaje'] }}
                                    </div>
                                @endforeach
                            </div>

                            <div class="alert alert-warning py-2">
                                <i class="fa-solid fa-wallet me-1"></i>
                                <strong>Saldo en caja:</strong> Bs. {{ number_format($saldoCaja, 2) }}
                                &nbsp;|&nbsp;
                                <strong>Total compra:</strong> Bs. {{ number_format($totalCompra, 2) }}
                                &nbsp;|&nbsp;
                                <strong>Faltante:</strong> Bs. {{ number_format(max(0, $totalCompra - $saldoCaja), 2) }}
                            </div>

                            <label class="form-label fw-semibold">Añadir fondos a caja</label>
                            <div class="input-group">
                                <span class="input-group-text">Bs.</span>
                                <input type="number"
                                    class="form-control"
                                    min="0"
                                    step="0.5"
                                    wire:model="montoAñadir"
                                    placeholder="0.00">
                                <button class="btn btn-success" wire:click="añadirFondosYContinuar">
                                    <i class="fa-solid fa-plus me-1"></i>Añadir y continuar
                                </button>
                            </div>

                            @if($errorFondos)
                                <div class="text-danger small mt-1">{{ $errorFondos }}</div>
                            @endif

                            <div class="mt-2">
                                <button class="btn btn-outline-secondary btn-sm" wire:click="omitirFondos">
                                    Continuar sin añadir fondos (resto a crédito)
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- ══════════ FASE: confirmar ══════════ --}}
                    @if($fase === 'confirmar')
                        <div class="mb-3">
                            <div id="qr-log" class="rounded p-2 small overflow-auto mb-3"
                                style="background:#f8f9fa; max-height:260px; font-family:monospace;">
                                @foreach($logProceso as $entry)
                                    <div class="
                                        @if($entry['tipo'] === 'success') text-success
                                        @elseif($entry['tipo'] === 'warning') text-warning
                                        @elseif($entry['tipo'] === 'error') text-danger
                                        @else text-secondary
                                        @endif
                                    ">
                                        @if($entry['tipo'] === 'success') ✔
                                        @elseif($entry['tipo'] === 'warning') ⚠
                                        @elseif($entry['tipo'] === 'error') ✖
                                        @else →
                                        @endif
                                        {{ $entry['mensaje'] }}
                                    </div>
                                @endforeach
                            </div>

                            <div class="alert alert-info py-2">
                                <div class="row text-center g-0">
                                    <div class="col-4">
                                        <div class="fw-bold fs-5">{{ $productosBuscados }}</div>
                                        <small class="text-muted">Productos</small>
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

                            @php
                                $efectivoAplicar = min($totalCompra, $saldoCaja);
                                $creditoAplicar  = max(0, $totalCompra - $saldoCaja);
                            @endphp

                            @if($creditoAplicar > 0)
                                <div class="alert alert-warning py-2 small">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    Se pagarán <strong>Bs. {{ number_format($efectivoAplicar, 2) }}</strong> en efectivo
                                    y <strong>Bs. {{ number_format($creditoAplicar, 2) }}</strong> quedarán a crédito.
                                </div>
                            @else
                                <div class="alert alert-success py-2 small">
                                    <i class="fa-solid fa-check me-1"></i>
                                    Saldo en caja suficiente. Se pagará <strong>Bs. {{ number_format($efectivoAplicar, 2) }}</strong> en efectivo.
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ══════════ FASE: resumen ══════════ --}}
                    @if($fase === 'resumen')
                        <div class="text-center py-3">
                            <i class="fa-solid fa-circle-check fa-4x text-success mb-3"></i>
                            <h4 class="text-success">¡Compra importada exitosamente!</h4>

                            <div class="row text-center g-0 my-3">
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

                </div>

                {{-- Footer --}}
                <div class="modal-footer py-2">
                    @if($fase === 'scanner')
                        <button class="btn btn-secondary" wire:click="cerrar">Cancelar</button>
                    @elseif($fase === 'procesando')
                        <span class="text-muted small">Procesando, por favor espera...</span>
                    @elseif($fase === 'fondos')
                        <button class="btn btn-secondary" wire:click="cerrar">Cancelar</button>
                    @elseif($fase === 'confirmar')
                        <button class="btn btn-secondary" wire:click="cerrar">Cancelar</button>
                        <button class="btn btn-success" wire:click="finalizarCompra" wire:loading.attr="disabled">
                            <span wire:loading wire:target="finalizarCompra">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                            </span>
                            <i wire:loading.remove wire:target="finalizarCompra" class="fa-solid fa-check me-1"></i>
                            Confirmar y Finalizar Compra
                        </button>
                    @elseif($fase === 'resumen')
                        <button class="btn btn-primary" wire:click="irACompra">
                            <i class="fa-solid fa-list me-1"></i>Ver Compras
                        </button>
                        <button class="btn btn-outline-secondary" wire:click="cerrar">Cerrar</button>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @endif

</div>


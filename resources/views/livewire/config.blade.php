<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="mb-0">
                                <i class="fa-solid fa-cog me-2"></i>
                                Configuración del Sistema
                            </h3>
                        </div>
                    </div>

                    <div class="card-body pt-3">
                        <!-- Tabs de navegación — solo PC -->
                        <ul class="nav nav-tabs nav-primary d-none d-md-flex" id="configTabs" role="tablist"
                            style="list-style: none; padding-left: 0;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}"
                                    wire:click="setTab('general')" type="button">
                                    <i class="fa-solid fa-gear me-1"></i>General
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'impresion' ? 'active' : '' }}"
                                    wire:click="setTab('impresion')" type="button">
                                    <i class="fa-solid fa-print me-1"></i>Impresión
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'whatsapp' ? 'active' : '' }}"
                                    wire:click="setTab('whatsapp')" type="button">
                                    <i class="fa-brands fa-whatsapp me-1"></i>WhatsApp
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'modulos' ? 'active' : '' }}"
                                    wire:click="setTab('modulos')" type="button">
                                    <i class="fa-solid fa-puzzle-piece me-1"></i>Módulos
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'importacion' ? 'active' : '' }}"
                                    wire:click="setTab('importacion')" type="button">
                                    <i class="fa-solid fa-file-import me-1"></i>Importación
                                </button>
                            </li>
                        </ul>

                        <!-- Navegación móvil — grilla de botones -->
                        <div class="d-grid d-md-none mb-3" style="grid-template-columns: repeat(5, 1fr); display: grid; gap: 8px;">
                            <button type="button"
                                wire:click="setTab('general')"
                                class="btn btn-sm d-flex flex-column align-items-center justify-content-center py-2 gap-1 {{ $activeTab === 'general' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                style="font-size: 0.65rem;">
                                <i class="fa-solid fa-gear" style="font-size: 1.2rem;"></i>
                                General
                            </button>
                            <button type="button"
                                wire:click="setTab('impresion')"
                                class="btn btn-sm d-flex flex-column align-items-center justify-content-center py-2 gap-1 {{ $activeTab === 'impresion' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                style="font-size: 0.65rem;">
                                <i class="fa-solid fa-print" style="font-size: 1.2rem;"></i>
                                Impresión
                            </button>
                            <button type="button"
                                wire:click="setTab('whatsapp')"
                                class="btn btn-sm d-flex flex-column align-items-center justify-content-center py-2 gap-1 {{ $activeTab === 'whatsapp' ? 'btn-success' : 'btn-outline-secondary' }}"
                                style="font-size: 0.65rem;">
                                <i class="fa-brands fa-whatsapp" style="font-size: 1.2rem;"></i>
                                WhatsApp
                            </button>
                            <button type="button"
                                wire:click="setTab('modulos')"
                                class="btn btn-sm d-flex flex-column align-items-center justify-content-center py-2 gap-1 {{ $activeTab === 'modulos' ? 'btn-warning' : 'btn-outline-secondary' }}"
                                style="font-size: 0.65rem;">
                                <i class="fa-solid fa-puzzle-piece" style="font-size: 1.2rem;"></i>
                                Módulos
                            </button>
                            <button type="button"
                                wire:click="setTab('importacion')"
                                class="btn btn-sm d-flex flex-column align-items-center justify-content-center py-2 gap-1 {{ $activeTab === 'importacion' ? 'btn-warning' : 'btn-outline-secondary' }}"
                                style="font-size: 0.65rem;">
                                <i class="fa-solid fa-file-import" style="font-size: 1.2rem;"></i>
                                Importar
                            </button>
                        </div>

                        <!-- Contenido de los tabs -->
                        <div class="tab-content mt-4">

                            <!-- Tab General -->
                            @if($activeTab === 'general')
                            <div class="tab-pane fade show active">
                                <div class="row">
                                    <!-- Datos de la Tienda -->
                                    <div class="col-md-6 mb-3">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-store me-2"></i>
                                                    Datos de la Tienda
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nombre de la Tienda</label>
                                                    <input type="text" class="form-control" wire:model="nombre_tienda"
                                                        wire:blur="guardarGeneral"
                                                        placeholder="Ej: Mi Tienda">
                                                    @error('nombre_tienda') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Dirección</label>
                                                    <input type="text" class="form-control" wire:model="direccion"
                                                        wire:blur="guardarGeneral"
                                                        placeholder="Ej: Av. Principal #123">
                                                    @error('direccion') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Teléfono</label>
                                                            <input type="text" class="form-control" wire:model="telefono"
                                                                wire:blur="guardarGeneral"
                                                                placeholder="77712345">
                                                            @error('telefono') <span class="text-danger small">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">NIT</label>
                                                            <input type="text" class="form-control" wire:model="nit"
                                                                wire:blur="guardarGeneral"
                                                                placeholder="1234567890">
                                                            @error('nit') <span class="text-danger small">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Email</label>
                                                    <input type="email" class="form-control" wire:model="email"
                                                        wire:blur="guardarGeneral"
                                                        placeholder="tienda@ejemplo.com">
                                                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Datos del Propietario -->
                                    <div class="col-md-6 mb-3">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-user-tie me-2"></i>
                                                    Datos del Propietario
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nombre del Propietario</label>
                                                    <input type="text" class="form-control" wire:model="propietario_nombre"
                                                        wire:blur="guardarGeneral"
                                                        placeholder="Nombre completo">
                                                    @error('propietario_nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Celular del Propietario</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fa-brands fa-whatsapp"></i></span>
                                                        <input type="text" class="form-control" wire:model="propietario_celular"
                                                            wire:blur="guardarGeneral"
                                                            placeholder="77712345">
                                                    </div>
                                                    @error('propietario_celular') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Logo de la Tienda -->
                                        <div class="card border shadow-sm mt-3">
                                            <div class="card-header bg-warning text-dark">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-image me-2"></i>
                                                    Logo de la Tienda
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-4 text-center">
                                                        @if($logo_actual)
                                                            <img src="{{ Storage::url($logo_actual) }}" alt="Logo"
                                                                class="img-fluid rounded shadow" style="max-height: 80px;">
                                                        @elseif($nuevo_logo)
                                                            <img src="{{ $nuevo_logo->temporaryUrl() }}" alt="Preview"
                                                                class="img-fluid rounded shadow" style="max-height: 80px;">
                                                        @else
                                                            <div class="bg-light rounded p-3">
                                                                <i class="fa-solid fa-image fa-3x text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-8">
                                                        <input type="file" class="form-control form-control-sm"
                                                            wire:model="nuevo_logo" accept="image/*">
                                                        <small class="text-muted">PNG o JPG. Máx 1MB</small>
                                                        @error('nuevo_logo') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                                        @if($logo_actual)
                                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2"
                                                                wire:click="eliminarLogo" wire:confirm="¿Eliminar el logo?">
                                                                <i class="fa-solid fa-trash me-1"></i> Eliminar
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Configuración de Sueldos -->
                                    <div class="col-md-6 mb-3">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-money-bill-wave me-2"></i>
                                                    Configuración de Sueldos
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Sueldo Base (Bs.)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Bs.</span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model="sueldo_base"
                                                            wire:blur="guardarGeneral"
                                                            placeholder="0.00">
                                                    </div>
                                                    <small class="text-muted">Sueldo base mensual para cálculos</small>
                                                    @error('sueldo_base') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Configuración de Red -->
                                    <div class="col-md-6 mb-3">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-secondary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-network-wired me-2"></i>
                                                    Configuración de Red
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">IP Local del Tenant</label>
                                                    <input type="text" class="form-control"
                                                        wire:model="ip_local"
                                                        wire:blur="guardarGeneral"
                                                        placeholder="192.168.1.100">
                                                    <small class="text-muted">Dirección IP para comunicación local</small>
                                                    @error('ip_local') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            @endif

                            <!-- Tab Impresión -->
                            @if($activeTab === 'impresion')
                            <div class="tab-pane fade show active"
                                 x-data="{
                                    printando: false,
                                    async enviarAgente(agentUrl, job, successMsg) {
                                        this.printando = true;
                                        try {
                                            const res = await fetch(agentUrl.replace(/\/$/, '') + '/api/print/universal', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json' },
                                                body: JSON.stringify(job),
                                                signal: AbortSignal.timeout(8000)
                                            });
                                            if (res.ok) {
                                                Swal.fire({ icon: 'success', title: successMsg || 'Impresión enviada', timer: 2000, showConfirmButton: false });
                                            } else {
                                                const txt = await res.text();
                                                Swal.fire({ icon: 'error', title: 'Error del agente', text: txt || 'Respuesta no válida' });
                                            }
                                        } catch (err) {
                                            Swal.fire({ icon: 'error', title: 'Agente no disponible', text: 'Verifica que el Print Agent esté corriendo en ' + agentUrl });
                                        } finally {
                                            this.printando = false;
                                        }
                                    }
                                 }"
                                 @enviar-a-agente.window="enviarAgente($event.detail.agentUrl, $event.detail.job, $event.detail.successMsg)">

                                <div class="row g-4 align-items-start">

                                    {{-- ══ Columna izquierda ══ --}}
                                    <div class="col-12 col-xl-4" x-data="{ mostrar: false }">

                                        {{-- Card unificada: Print Agent + Clave de Seguridad --}}
                                        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">

                                            {{-- Header --}}
                                            <div class="card-header d-flex align-items-center gap-2 py-3 px-4"
                                                 style="background:linear-gradient(135deg,#0d6efd,#0a58ca);">
                                                <i class="fa-solid fa-server text-white fs-5"></i>
                                                <div>
                                                    <h6 class="mb-0 text-white fw-bold">Print Agent</h6>
                                                    <small class="text-white-50">Servicio local de impresión</small>
                                                </div>
                                            </div>

                                            {{-- Sección: URL + Descargar --}}
                                            <div class="px-4 pt-3 pb-2 d-flex flex-column gap-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-circle-dot text-success" style="font-size:.7rem;"></i>
                                                    <span class="text-muted small font-monospace">{{ $printAgentUrl }}</span>
                                                </div>
                                                <a href="https://fadi.com.bo/download.php?file=installPrinterFADI.bat"
                                                   class="btn btn-success btn-sm d-flex align-items-center justify-content-center gap-2">
                                                    <i class="fa-solid fa-download"></i>
                                                    <span>Descargar Instalador</span>
                                                </a>
                                            </div>

                                            {{-- Divider: Clave de Seguridad --}}
                                            <div class="px-4 pt-3 pb-1 border-top mt-1">
                                                <p class="text-uppercase fw-bold mb-2 d-flex align-items-center gap-2"
                                                   style="font-size:.65rem;letter-spacing:.1em;color:#495057;">
                                                    <i class="fa-solid fa-shield-halved text-secondary"></i>
                                                    Clave de Seguridad
                                                </p>
                                                <div class="input-group input-group-sm mb-2">
                                                    <input :type="mostrar ? 'text' : 'password'"
                                                           class="form-control font-monospace"
                                                           wire:model.blur="print_agent_secret_key"
                                                           wire:change="guardarImpresion"
                                                           placeholder="Clave AES-256 hex (64 chars)"
                                                           maxlength="64">
                                                    <button class="btn btn-outline-secondary" type="button"
                                                            @click="mostrar = !mostrar" title="Mostrar / ocultar">
                                                        <i :class="mostrar ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary" type="button"
                                                            @click="navigator.clipboard.writeText($wire.print_agent_secret_key)"
                                                            title="Copiar al portapapeles">
                                                        <i class="fa-solid fa-copy"></i>
                                                    </button>
                                                </div>
                                                @error('print_agent_secret_key')
                                                    <p class="text-danger small mb-2">{{ $message }}</p>
                                                @enderror
                                                <button class="btn btn-warning btn-sm w-100 d-flex align-items-center justify-content-center gap-2 mb-3"
                                                        wire:click="regenerarPrintKey"
                                                        wire:confirm="¿Regenerar la clave? Deberás actualizarla también en el Print Agent."
                                                        wire:loading.attr="disabled">
                                                    <i class="fa-solid fa-rotate"></i>
                                                    <span>Regenerar clave</span>
                                                </button>
                                            </div>

                                            {{-- Footer: Pruebas de impresión --}}
                                            <div class="card-footer bg-light border-top px-4 py-3">
                                                <p class="text-uppercase text-muted fw-bold mb-2"
                                                   style="font-size:.65rem;letter-spacing:.1em;">Pruebas de impresión</p>
                                                <div class="d-grid gap-2">
                                                    <button type="button"
                                                            class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center gap-2"
                                                            wire:click="impresionPruebaLegacy"
                                                            wire:loading.attr="disabled"
                                                            :disabled="printando">
                                                        <span x-show="printando" class="spinner-border spinner-border-sm" role="status"></span>
                                                        <i class="fa-solid fa-print" x-show="!printando"></i>
                                                        <span>Imprimir Prueba</span>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center gap-2"
                                                            wire:click="imprimirUltimaVenta"
                                                            wire:loading.attr="disabled"
                                                            :disabled="printando">
                                                        <i class="fa-solid fa-cart-shopping"></i>
                                                        <span>Última Venta</span>
                                                    </button>
                                                    @if(prestamosHabilitados())
                                                    <button type="button"
                                                            class="btn btn-outline-warning btn-sm d-flex align-items-center justify-content-center gap-2"
                                                            wire:click="imprimirUltimoPrestamo"
                                                            wire:loading.attr="disabled"
                                                            :disabled="printando">
                                                        <i class="fa-solid fa-hand-holding-dollar"></i>
                                                        <span>Último Préstamo</span>
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- ══ Columna derecha ══ --}}
                                    <div class="col-12 col-xl-8">
                                        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                                            <div class="card-header d-flex align-items-center gap-2 py-3 px-4"
                                                 style="background:linear-gradient(135deg,#0dcaf0,#0aa2c0);">
                                                <i class="fa-solid fa-sliders text-white fs-5"></i>
                                                <div>
                                                    <h6 class="mb-0 text-white fw-bold">Configuración del Ticket</h6>
                                                    <small class="text-white-50">Impresora, papel y opciones</small>
                                                </div>
                                            </div>
                                            <div class="card-body px-4 py-4">

                                                {{-- Sección: impresora --}}
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-7">
                                                        <label class="form-label fw-semibold mb-1">
                                                            <i class="fa-solid fa-tag text-secondary me-1"></i>
                                                            Nombre en el agente
                                                        </label>
                                                        <input type="text"
                                                               class="form-control"
                                                               wire:model.blur="impresora_nombre"
                                                               wire:change="guardarImpresion"
                                                               placeholder="Nombre exacto de la impresora en el agente">
                                                        @error('impresora_nombre')
                                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label fw-semibold mb-1">
                                                            <i class="fa-solid fa-scroll text-secondary me-1"></i>
                                                            Tamaño de papel
                                                        </label>
                                                        <select class="form-select"
                                                                wire:model="papel_tamano"
                                                                wire:change="guardarImpresion">
                                                            <option value="58mm">58 mm — 32 columnas</option>
                                                            <option value="80mm">80 mm — 48 columnas</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Sección: opciones del ticket --}}
                                                <p class="text-uppercase text-muted fw-bold mb-3"
                                                   style="font-size:.65rem;letter-spacing:.1em;border-bottom:2px solid #dee2e6;padding-bottom:.4rem;">
                                                    Opciones del ticket
                                                </p>
                                                <div class="row g-3 mb-4">
                                                    @foreach([
                                                        ['field'=>'mostrar_logo',    'label'=>'Logo',             'desc'=>'Imprimir el logo de la empresa',    'icon'=>'fa-image',         'color'=>'text-primary'],
                                                        ['field'=>'corte_automatico','label'=>'Corte automático', 'desc'=>'Cortar el papel al finalizar',      'icon'=>'fa-scissors',      'color'=>'text-danger'],
                                                        ['field'=>'abrir_cajon',     'label'=>'Abrir cajón',      'desc'=>'Pulso eléctrico al imprimir venta', 'icon'=>'fa-cash-register', 'color'=>'text-success'],
                                                    ] as $opt)
                                                    <div class="col-12 col-sm-4">
                                                        <div class="border rounded-3 p-3 h-100 d-flex align-items-center justify-content-between gap-3 bg-light bg-opacity-50">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                                                                     style="width:2.4rem;height:2.4rem;min-width:2.4rem;">
                                                                    <i class="fa-solid {{ $opt['icon'] }} {{ $opt['color'] }}"></i>
                                                                </div>
                                                                <div>
                                                                    <p class="mb-0 fw-semibold text-dark" style="font-size:.9rem;">{{ $opt['label'] }}</p>
                                                                    <small class="text-muted">{{ $opt['desc'] }}</small>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-switch mb-0 flex-shrink-0">
                                                                <input class="form-check-input" type="checkbox" role="switch"
                                                                       wire:model="{{ $opt['field'] }}"
                                                                       wire:change="guardarImpresion"
                                                                       style="width:2.8rem;height:1.4rem;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>

                                                {{-- Sección: footer del ticket --}}
                                                <p class="text-uppercase text-muted fw-bold mb-3 mt-4"
                                                   style="font-size:.65rem;letter-spacing:.1em;border-bottom:2px solid #dee2e6;padding-bottom:.4rem;">
                                                    Footer del ticket
                                                </p>
                                                <div class="border rounded-3 p-3 bg-light bg-opacity-50 mb-4">
                                                    <div class="d-flex align-items-start gap-3 mb-2">
                                                        <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center flex-shrink-0"
                                                             style="width:2.4rem;height:2.4rem;">
                                                            <i class="fa-solid fa-ticket text-secondary"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="mb-0 fw-semibold text-dark" style="font-size:.9rem;">Mensaje de cierre</p>
                                                            <small class="text-muted">Se imprime automáticamente al final del ticket:</small>
                                                            <div class="mt-2 p-2 bg-white border rounded-2 text-center" style="font-size:.8rem; font-family:monospace; color:#444; line-height:1.6;">
                                                                <strong>¡Gracias por su compra!</strong><br>
                                                                @if($propietario_nombre) {{ $propietario_nombre }}<br>@endif
                                                                @if($propietario_celular) {{ $propietario_celular }}<br>@endif
                                                            </div>
                                                            <small class="text-muted mt-1 d-block">El nombre y teléfono del propietario se configuran en el tab <strong>General</strong>.</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Sección: impresión automática --}}
                                                <p class="text-uppercase text-muted fw-bold mb-3"
                                                   style="font-size:.65rem;letter-spacing:.1em;border-bottom:2px solid #dee2e6;padding-bottom:.4rem;">
                                                    Impresión automática
                                                </p>
                                                <div class="row g-3">
                                                    @if(ventasHabilitados())
                                                    <div class="col-12 col-sm-4">
                                                        <div class="border rounded-3 p-3 h-100 d-flex align-items-center justify-content-between gap-3 bg-light bg-opacity-50">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                                                                     style="width:2.4rem;height:2.4rem;min-width:2.4rem;">
                                                                    <i class="fa-solid fa-cart-shopping text-danger"></i>
                                                                </div>
                                                                <div>
                                                                    <p class="mb-0 fw-semibold text-dark" style="font-size:.9rem;">Ventas</p>
                                                                    <small class="text-muted">Al cerrar venta</small>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-switch mb-0 flex-shrink-0">
                                                                <input class="form-check-input" type="checkbox" role="switch"
                                                                       wire:model="impresion_auto_venta"
                                                                       wire:change="guardarImpresion"
                                                                       style="width:2.8rem;height:1.4rem;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(prestamosHabilitados())
                                                    <div class="col-12 col-sm-4">
                                                        <div class="border rounded-3 p-3 h-100 d-flex align-items-center justify-content-between gap-3 bg-light bg-opacity-50">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                                                                     style="width:2.4rem;height:2.4rem;min-width:2.4rem;">
                                                                    <i class="fa-solid fa-hand-holding-dollar text-warning"></i>
                                                                </div>
                                                                <div>
                                                                    <p class="mb-0 fw-semibold text-dark" style="font-size:.9rem;">Préstamos</p>
                                                                    <small class="text-muted">Al cerrar préstamo</small>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-switch mb-0 flex-shrink-0">
                                                                <input class="form-check-input" type="checkbox" role="switch"
                                                                       wire:model="impresion_auto_prestamo"
                                                                       wire:change="guardarImpresion"
                                                                       style="width:2.8rem;height:1.4rem;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(comprasHabilitados())
                                                    <div class="col-12 col-sm-4">
                                                        <div class="border rounded-3 p-3 h-100 d-flex align-items-center justify-content-between gap-3 bg-light bg-opacity-50">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                                                                     style="width:2.4rem;height:2.4rem;min-width:2.4rem;">
                                                                    <i class="fa-solid fa-boxes-stacked text-info"></i>
                                                                </div>
                                                                <div>
                                                                    <p class="mb-0 fw-semibold text-dark" style="font-size:.9rem;">Inventario</p>
                                                                    <small class="text-muted">Al cerrar inventario</small>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-switch mb-0 flex-shrink-0">
                                                                <input class="form-check-input" type="checkbox" role="switch"
                                                                       wire:model="impresion_auto_inventario"
                                                                       wire:change="guardarImpresion"
                                                                       style="width:2.8rem;height:1.4rem;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            @endif

                            <!-- Tab WhatsApp -->
                            @if($activeTab === 'whatsapp')
                            <div class="tab-pane fade show active">
                                <div class="alert alert-info mb-4">
                                    <i class="fa-solid fa-info-circle me-2"></i>
                                    <strong>Nota:</strong> Esta funcionalidad está en desarrollo. Próximamente podrás enviar notificaciones automáticas por WhatsApp.
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-brands fa-whatsapp me-2"></i>
                                                    API de WhatsApp Business
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            wire:model="whatsapp_enabled"
                                                            wire:change="guardarWhatsApp"
                                                            id="whatsappEnabled">
                                                        <label class="form-check-label fw-semibold" for="whatsappEnabled">
                                                            Habilitar WhatsApp
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Token de Acceso</label>
                                                    <input type="password" class="form-control"
                                                        wire:model="whatsapp_token"
                                                        wire:blur="guardarWhatsApp"
                                                        placeholder="Token de la API de WhatsApp">
                                                    <small class="text-muted">Obténlo desde Meta Business Suite</small>
                                                    @error('whatsapp_token') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Phone Number ID</label>
                                                    <input type="text" class="form-control"
                                                        wire:model="whatsapp_phone_id"
                                                        wire:blur="guardarWhatsApp"
                                                        placeholder="ID del número de teléfono">
                                                    @error('whatsapp_phone_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Tab Módulos -->
                            @if($activeTab === 'modulos')
                            <div class="tab-pane fade show active">
                                <div class="row g-3">

                                    <!-- Módulo Ventas -->
                                    <div class="col-md-6">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-shopping-cart me-2"></i>
                                                    Módulo de Ventas
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <!-- Habilitar módulo -->
                                                <div class="d-flex justify-content-between align-items-center pb-3 mb-3"
                                                     :class="{ 'border-bottom': $wire.ventas_enabled }">
                                                    <div>
                                                        <p class="fw-semibold mb-0">Habilitar módulo de ventas</p>
                                                        <small class="text-muted">Si está desactivado, el menú de Ventas desaparece y no se puede vender.</small>
                                                    </div>
                                                    <div class="form-check form-switch mb-0 ms-3">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                               wire:model="ventas_enabled"
                                                               wire:change="guardarModulos"
                                                               id="ventasEnabled"
                                                               style="width: 3rem; height: 1.5rem;">
                                                    </div>
                                                </div>

                                                <!-- Solo por unidad (solo si ventas habilitado) -->
                                                @if($ventas_enabled)
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="fw-semibold mb-0">Vender solo por unidad</p>
                                                        <small class="text-muted">
                                                            Oculta los campos de medida, cantidad y precio por mayor.
                                                            Todas las ventas se realizan por unidad al precio menor.
                                                        </small>
                                                    </div>
                                                    <div class="form-check form-switch mb-0 ms-3">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                               wire:model="ventas_solo_unidad"
                                                               wire:change="guardarModulos"
                                                               id="ventasSoloUnidad"
                                                               style="width: 3rem; height: 1.5rem;">
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Módulo Compras & Stock -->
                                    <div class="col-md-6">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-secondary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-basket-shopping me-2"></i>
                                                    Módulo de Compras y Stock
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="fw-semibold mb-0">Habilitar compras y control de stock</p>
                                                        <small class="text-muted">
                                                            Si está desactivado, el menú de Compras y Kardex desaparece.
                                                            Las ventas no verificarán ni modificarán el stock de productos.
                                                        </small>
                                                    </div>
                                                    <div class="form-check form-switch mb-0 ms-3">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                               wire:model="compras_enabled"
                                                               wire:change="guardarModulos"
                                                               id="comprasEnabled"
                                                               style="width: 3rem; height: 1.5rem;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Módulo Préstamos -->
                                    <div class="col-md-6">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-warning text-dark">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-handshake me-2"></i>
                                                    Módulo de Préstamos
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <!-- Habilitar módulo -->
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                                    <div>
                                                        <p class="fw-semibold mb-0">Habilitar módulo de préstamos</p>
                                                        <small class="text-muted">Si está desactivado, la opción no aparecerá en el menú ni en el header.</small>
                                                    </div>
                                                    <div class="form-check form-switch mb-0 ms-3">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                               wire:model="prestamos_enabled"
                                                               wire:change="guardarModulos"
                                                               id="prestamosEnabled"
                                                               style="width: 3rem; height: 1.5rem;">
                                                    </div>
                                                </div>

                                                <!-- Categoría de productos -->
                                                <div>
                                                    <label class="form-label fw-semibold">Categoría de productos para préstamos</label>
                                                    <select class="form-select"
                                                            wire:model="prestamos_categoria_id"
                                                            wire:change="guardarModulos">
                                                        <option value="">-- Todas las categorías --</option>
                                                        @foreach($categorias as $cat)
                                                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Solo se mostrarán productos de esta categoría al agregar ítems al préstamo.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Módulo Hospedajes -->
                                    <div class="col-md-6">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-hotel me-2"></i>
                                                    Módulo de Hospedajes
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="fw-semibold mb-0">Habilitar módulo de hospedajes</p>
                                                        <small class="text-muted">
                                                            Activa el control de habitaciones, check-in/check-out y panel visual de estado de habitaciones.
                                                            <br>Ideal para hoteles, hospedajes y alojamientos.
                                                        </small>
                                                    </div>
                                                    <div class="form-check form-switch mb-0 ms-3">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                               wire:model="hospedajes_enabled"
                                                               wire:change="guardarModulos"
                                                               id="hospedajesEnabled"
                                                               style="width: 3rem; height: 1.5rem;">
                                                    </div>
                                                </div>

                                                @if($hospedajes_enabled)
                                                <div class="mt-3 pt-3 border-top">
                                                    <p class="small text-muted mb-2"><i class="fa-solid fa-circle-info me-1"></i>Módulo habilitado. Accede desde el menú lateral:</p>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <a href="{{ route('habitaciones') }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fa-solid fa-door-open me-1"></i>Panel de Habitaciones
                                                        </a>
                                                        <a href="{{ route('hospedajes') }}" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fa-solid fa-clipboard-list me-1"></i>Historial
                                                        </a>
                                                        <a href="{{ route('tipos-habitacion') }}" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fa-solid fa-layer-group me-1"></i>Tipos y Tarifas
                                                        </a>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Zona de peligro: Resetear tenant -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card border border-danger shadow-sm">
                                            <div class="card-header bg-danger text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                                    Zona de Peligro
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                                    <div>
                                                        <p class="fw-semibold mb-1 text-danger">Resetear todos los datos de movimiento</p>
                                                        <small class="text-muted">
                                                            Elimina permanentemente todas las ventas, compras, préstamos, hospedajes,
                                                            inventarios, movimientos y kardex de este tenant. Los productos, categorías, clientes
                                                            y configuración <strong>no</strong> se eliminan.
                                                            <br><strong class="text-danger">Esta acción no se puede deshacer.</strong>
                                                        </small>
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-danger"
                                                        onclick="confirmarResetTenant()">
                                                        <i class="fa-solid fa-trash-can me-1"></i>
                                                        Resetear Datos
                                                    </button>
                                                </div>

                                                <hr class="my-3">

                                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                                    <div>
                                                        <p class="fw-semibold mb-1 text-danger">Resetear stock de productos</p>
                                                        <small class="text-muted">
                                                            Pone en <strong>0</strong> el stock de todos los productos de este tenant.
                                                            Útil para empezar un conteo desde cero sin borrar el historial de movimientos.
                                                            <br><strong class="text-danger">Esta acción no se puede deshacer.</strong>
                                                        </small>
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-danger"
                                                        onclick="confirmarResetStock()">
                                                        <i class="fa-solid fa-boxes-stacked me-1"></i>
                                                        Resetear Stock
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            @endif

                            <!-- Tab Importación -->
                            @if($activeTab === 'importacion')
                            <div class="tab-pane fade show active">
                                <div class="alert alert-warning mb-4">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                    <strong>Atención:</strong> La importación de datos reemplazará información existente. Asegúrate de tener un respaldo antes de importar.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-dark text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-file-import me-2"></i>
                                                    Configuración de Importación
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Formato de Archivo Preferido</label>
                                                    <select class="form-select" wire:model="formato_importacion"
                                                        wire:change="guardarImportacion">
                                                        <option value="excel">Excel (.xlsx, .xls)</option>
                                                        <option value="csv">CSV (.csv)</option>
                                                        <option value="json">JSON (.json)</option>
                                                    </select>
                                                    @error('formato_importacion') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-secondary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-upload me-2"></i>
                                                    Importar Datos
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted mb-3">
                                                    Selecciona el tipo de datos a importar:
                                                </p>
                                                <div class="d-grid gap-2">
                                                    <button class="btn btn-outline-primary" disabled>
                                                        <i class="fa-solid fa-boxes-stacked me-2"></i>
                                                        Importar Productos (Próximamente)
                                                    </button>
                                                    <button class="btn btn-outline-primary" disabled>
                                                        <i class="fa-solid fa-users me-2"></i>
                                                        Importar Clientes (Próximamente)
                                                    </button>
                                                    <button class="btn btn-outline-primary" disabled>
                                                        <i class="fa-solid fa-layer-group me-2"></i>
                                                        Importar Categorías (Próximamente)
                                                    </button>
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
    </div>

    @script
    <script>
        $wire.$watch('activeTab', value => {
            document.cookie = `config_active_tab=${encodeURIComponent(value)}; path=/; max-age=31536000; SameSite=Lax`;
        });

        $wire.on('recargar-pagina', () => {
            setTimeout(() => window.location.reload(), 500);
        });

        window.confirmarResetTenant = function () {
            Swal.fire({
                title: '¿Resetear todos los datos?',
                html: 'Esta acción eliminará permanentemente todas las <strong>ventas, compras, préstamos, hospedajes, inventarios, movimientos y kardex</strong>.<br><br>Los productos, categorías, clientes y configuración <strong>no</strong> se eliminarán.<br><br><span class="text-danger fw-bold">Esta acción no se puede deshacer.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, resetear todo',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Borrando todos los datos...',
                        html: '<i class="fa-solid fa-spinner fa-spin fa-2x"></i>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                    });
                    $wire.resetearTenant();
                }
            });
        };

        $wire.on('datos-reseteados', () => {
            Swal.fire({
                title: '¡Listo!',
                text: 'Todos los datos han sido eliminados correctamente.',
                icon: 'success',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Aceptar',
            });
        });

        window.confirmarResetStock = function () {
            Swal.fire({
                title: '¿Resetear el stock?',
                html: 'Esta acción pondrá en <strong>0</strong> el stock de todos los productos.<br><br>El historial de ventas, compras y kardex <strong>no</strong> se eliminará.<br><br><span class="text-danger fw-bold">Esta acción no se puede deshacer.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, resetear stock',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Reseteando stock...',
                        html: '<i class="fa-solid fa-spinner fa-spin fa-2x"></i>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                    });
                    $wire.resetearStock();
                }
            });
        };

        $wire.on('stock-reseteado', () => {
            Swal.fire({
                title: '¡Listo!',
                text: 'El stock de todos los productos ha sido puesto en 0.',
                icon: 'success',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Aceptar',
            });
        });
    </script>
    @endscript
</div>

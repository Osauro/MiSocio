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
                        <!-- Tabs de navegación -->
                        <ul class="nav nav-tabs nav-primary" id="configTabs" role="tablist" style="list-style: none; padding-left: 0;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}"
                                    wire:click="setTab('general')" type="button">
                                    <i class="fa-solid fa-gear me-1"></i>
                                    <span class="d-none d-md-inline">General</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'impresion' ? 'active' : '' }}"
                                    wire:click="setTab('impresion')" type="button">
                                    <i class="fa-solid fa-print me-1"></i>
                                    <span class="d-none d-md-inline">Impresión</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'whatsapp' ? 'active' : '' }}"
                                    wire:click="setTab('whatsapp')" type="button">
                                    <i class="fa-brands fa-whatsapp me-1"></i>
                                    <span class="d-none d-md-inline">WhatsApp</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'facebook' ? 'active' : '' }}"
                                    wire:click="setTab('facebook')" type="button">
                                    <i class="fa-brands fa-facebook me-1"></i>
                                    <span class="d-none d-md-inline">Facebook</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'importacion' ? 'active' : '' }}"
                                    wire:click="setTab('importacion')" type="button">
                                    <i class="fa-solid fa-file-import me-1"></i>
                                    <span class="d-none d-md-inline">Importación</span>
                                </button>
                            </li>
                        </ul>

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
                                                        placeholder="Ej: Mi Tienda">
                                                    @error('nombre_tienda') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Dirección</label>
                                                    <textarea class="form-control" wire:model="direccion" rows="2"
                                                        placeholder="Ej: Av. Principal #123"></textarea>
                                                    @error('direccion') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Teléfono</label>
                                                            <input type="text" class="form-control" wire:model="telefono"
                                                                placeholder="77712345">
                                                            @error('telefono') <span class="text-danger small">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">NIT</label>
                                                            <input type="text" class="form-control" wire:model="nit"
                                                                placeholder="1234567890">
                                                            @error('nit') <span class="text-danger small">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Email</label>
                                                    <input type="email" class="form-control" wire:model="email"
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
                                                        placeholder="Nombre completo">
                                                    @error('propietario_nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Celular del Propietario</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fa-brands fa-whatsapp"></i></span>
                                                        <input type="text" class="form-control" wire:model="propietario_celular"
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
                                                            wire:model="sueldo_base" placeholder="0.00">
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
                                                        wire:model="ip_local" placeholder="192.168.1.100">
                                                    <small class="text-muted">Dirección IP para comunicación local</small>
                                                    @error('ip_local') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 text-end">
                                    <button class="btn btn-primary" wire:click="guardarGeneral">
                                        <i class="fa-solid fa-save me-1"></i>
                                        Guardar Configuración General
                                    </button>
                                </div>
                            </div>
                            @endif

                            <!-- Tab Impresión -->
                            @if($activeTab === 'impresion')
                            <div class="tab-pane fade show active">
                                <div class="row">
                                    <!-- Selección de Impresora -->
                                    <div class="col-md-6 mb-3">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-print me-2"></i>
                                                    Impresora
                                                </h5>
                                                <button type="button" class="btn btn-sm btn-light" wire:click="detectarImpresoras"
                                                    wire:loading.attr="disabled" title="Detectar impresoras del sistema">
                                                    <span wire:loading.remove wire:target="detectarImpresoras">
                                                        <i class="fa-solid fa-sync"></i> Detectar
                                                    </span>
                                                    <span wire:loading wire:target="detectarImpresoras">
                                                        <i class="fa-solid fa-spinner fa-spin"></i> Buscando...
                                                    </span>
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <!-- Impresora seleccionada -->
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Impresora Seleccionada</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fa-solid fa-print"></i></span>
                                                        <input type="text" class="form-control" id="impresora_nombre"
                                                            wire:model="impresora_nombre" placeholder="Nombre de la impresora">
                                                    </div>
                                                    @error('impresora_nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>

                                                <!-- Lista de impresoras detectadas -->
                                                <div id="lista-impresoras-container" class="mb-3" style="display: none;">
                                                    <label class="form-label fw-semibold small">
                                                        <i class="fa-solid fa-list me-1"></i> Impresoras Disponibles
                                                    </label>
                                                    <div class="list-group list-group-flush" id="lista-impresoras" style="max-height: 200px; overflow-y: auto;">
                                                        <!-- Se llena dinámicamente -->
                                                    </div>
                                                </div>

                                                <!-- Agregar impresora de red -->
                                                <div class="border-top pt-3">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                                        data-bs-toggle="collapse" data-bs-target="#agregarIpCollapse">
                                                        <i class="fa-solid fa-network-wired me-1"></i>
                                                        Agregar Impresora de Red (IP)
                                                    </button>
                                                    <div class="collapse mt-2" id="agregarIpCollapse">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">IP:</span>
                                                            <input type="text" class="form-control" id="impresora_ip"
                                                                placeholder="192.168.1.100">
                                                            <input type="number" class="form-control" id="impresora_puerto"
                                                                placeholder="9100" style="max-width: 80px;">
                                                            <button type="button" class="btn btn-success" id="btn-agregar-ip">
                                                                <i class="fa-solid fa-plus"></i>
                                                            </button>
                                                        </div>
                                                        <small class="text-muted">Puerto por defecto: 9100 (RAW)</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tipo y Configuración -->
                                    <div class="col-md-6 mb-3">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-cog me-2"></i>
                                                    Configuración
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Tipo</label>
                                                            <select class="form-select" wire:model="impresora_tipo">
                                                                <option value="termica">Térmica (POS)</option>
                                                                <option value="laser">Láser</option>
                                                                <option value="inyeccion">Inyección</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Tamaño Papel</label>
                                                            <select class="form-select" wire:model="papel_tamano">
                                                                <option value="58mm">58mm</option>
                                                                <option value="80mm">80mm</option>
                                                                <option value="carta">Carta</option>
                                                                <option value="media-carta">Media Carta</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Ancho (caracteres)</label>
                                                            <input type="number" class="form-control" wire:model="ancho_caracteres"
                                                                min="32" max="80" placeholder="48">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Copias</label>
                                                            <input type="number" class="form-control"
                                                                wire:model="papel_copias" min="1" max="5">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Opciones de Impresora Térmica -->
                                        <div class="card border shadow-sm mt-3">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-sliders me-2"></i>
                                                    Opciones Extras
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" wire:model="corte_automatico" id="corteAutomatico">
                                                    <label class="form-check-label" for="corteAutomatico">
                                                        <i class="fa-solid fa-scissors me-1"></i> Corte automático de papel
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" wire:model="abrir_cajon" id="abrirCajon">
                                                    <label class="form-check-label" for="abrirCajon">
                                                        <i class="fa-solid fa-cash-register me-1"></i> Abrir cajón al imprimir
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" wire:model="sonido_apertura" id="sonidoApertura">
                                                    <label class="form-check-label" for="sonidoApertura">
                                                        <i class="fa-solid fa-volume-high me-1"></i> Sonido al abrir cajón
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-info" wire:click="impresionPrueba">
                                        <i class="fa-solid fa-print me-1"></i>
                                        Impresión de Prueba
                                    </button>
                                    <button class="btn btn-primary" wire:click="guardarImpresion">
                                        <i class="fa-solid fa-save me-1"></i>
                                        Guardar Configuración
                                    </button>
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
                                                            wire:model="whatsapp_enabled" id="whatsappEnabled">
                                                        <label class="form-check-label fw-semibold" for="whatsappEnabled">
                                                            Habilitar WhatsApp
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Token de Acceso</label>
                                                    <input type="password" class="form-control"
                                                        wire:model="whatsapp_token" placeholder="Token de la API de WhatsApp">
                                                    <small class="text-muted">Obténlo desde Meta Business Suite</small>
                                                    @error('whatsapp_token') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Phone Number ID</label>
                                                    <input type="text" class="form-control"
                                                        wire:model="whatsapp_phone_id" placeholder="ID del número de teléfono">
                                                    @error('whatsapp_phone_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-primary" wire:click="guardarWhatsApp">
                                        <i class="fa-solid fa-save me-1"></i>
                                        Guardar Configuración WhatsApp
                                    </button>
                                </div>
                            </div>
                            @endif

                            <!-- Tab Facebook -->
                            @if($activeTab === 'facebook')
                            <div class="tab-pane fade show active">
                                <div class="alert alert-info mb-4">
                                    <i class="fa-solid fa-info-circle me-2"></i>
                                    <strong>Nota:</strong> Esta funcionalidad está en desarrollo. Próximamente podrás publicar automáticamente en Facebook.
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-brands fa-facebook me-2"></i>
                                                    API de Facebook
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            wire:model="facebook_enabled" id="facebookEnabled">
                                                        <label class="form-check-label fw-semibold" for="facebookEnabled">
                                                            Habilitar Facebook
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Page ID</label>
                                                    <input type="text" class="form-control"
                                                        wire:model="facebook_page_id" placeholder="ID de la página de Facebook">
                                                    @error('facebook_page_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Access Token</label>
                                                    <input type="password" class="form-control"
                                                        wire:model="facebook_access_token" placeholder="Token de acceso de la página">
                                                    <small class="text-muted">Obténlo desde Facebook Developers</small>
                                                    @error('facebook_access_token') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-primary" wire:click="guardarFacebook">
                                        <i class="fa-solid fa-save me-1"></i>
                                        Guardar Configuración Facebook
                                    </button>
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
                                                    <select class="form-select" wire:model="formato_importacion">
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
                                <div class="mt-3 text-end">
                                    <button class="btn btn-primary" wire:click="guardarImportacion">
                                        <i class="fa-solid fa-save me-1"></i>
                                        Guardar Configuración
                                    </button>
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
        // Manejar impresoras detectadas del servidor
        $wire.on('impresoras-detectadas', (data) => {
            const container = document.getElementById('lista-impresoras-container');
            const lista = document.getElementById('lista-impresoras');
            const impresoras = data[0].impresoras || [];

            if (impresoras.length > 0) {
                lista.innerHTML = impresoras.map((imp, index) => `
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                        onclick="seleccionarImpresora('${imp.nombre}')">
                        <div>
                            <i class="fa-solid fa-print text-primary me-2"></i>
                            <strong>${imp.nombre}</strong>
                            ${imp.puerto ? `<small class="text-muted ms-2">(${imp.puerto})</small>` : ''}
                        </div>
                        <span class="badge bg-secondary">${imp.tipo}</span>
                    </button>
                `).join('');
                container.style.display = 'block';
            } else {
                lista.innerHTML = `
                    <div class="list-group-item text-muted text-center">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        No se encontraron impresoras en el servidor.
                        <br><small>Prueba agregando una impresora de red por IP.</small>
                    </div>
                `;
                container.style.display = 'block';
            }
        });

        // Función global para seleccionar impresora
        window.seleccionarImpresora = function(nombre) {
            document.getElementById('impresora_nombre').value = nombre;
            @this.set('impresora_nombre', nombre);

            // Marcar visualmente la seleccionada
            document.querySelectorAll('#lista-impresoras .list-group-item').forEach(item => {
                item.classList.remove('active');
                if (item.textContent.includes(nombre)) {
                    item.classList.add('active');
                }
            });
        };

        // Agregar impresora de red por IP
        document.getElementById('btn-agregar-ip')?.addEventListener('click', function() {
            const ip = document.getElementById('impresora_ip').value.trim();
            const puerto = document.getElementById('impresora_puerto').value.trim() || '9100';

            if (!ip) {
                Swal.fire({
                    icon: 'warning',
                    title: 'IP requerida',
                    text: 'Ingresa la dirección IP de la impresora',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            // Validar formato IP
            const ipRegex = /^(\d{1,3}\.){3}\d{1,3}$/;
            if (!ipRegex.test(ip)) {
                Swal.fire({
                    icon: 'error',
                    title: 'IP inválida',
                    text: 'Ingresa una dirección IP válida (ej: 192.168.1.100)',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            const nombreImpresora = `${ip}:${puerto}`;

            // Agregar a la lista
            const container = document.getElementById('lista-impresoras-container');
            const lista = document.getElementById('lista-impresoras');

            // Verificar si ya existe
            if (lista.innerHTML.includes(nombreImpresora)) {
                Swal.fire({
                    icon: 'info',
                    title: 'Ya existe',
                    text: 'Esta impresora ya está en la lista',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            // Agregar nuevo item
            const nuevoItem = document.createElement('button');
            nuevoItem.type = 'button';
            nuevoItem.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
            nuevoItem.onclick = function() { seleccionarImpresora(nombreImpresora); };
            nuevoItem.innerHTML = `
                <div>
                    <i class="fa-solid fa-network-wired text-success me-2"></i>
                    <strong>${nombreImpresora}</strong>
                </div>
                <span class="badge bg-success">Red</span>
            `;

            // Si la lista tiene el mensaje de "no encontraron", limpiarlo
            if (lista.querySelector('.text-muted.text-center')) {
                lista.innerHTML = '';
            }

            lista.appendChild(nuevoItem);
            container.style.display = 'block';

            // Seleccionar automáticamente
            seleccionarImpresora(nombreImpresora);

            // Limpiar campos
            document.getElementById('impresora_ip').value = '';
            document.getElementById('impresora_puerto').value = '';

            // Cerrar collapse
            const collapse = document.getElementById('agregarIpCollapse');
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) bsCollapse.hide();

            Swal.fire({
                icon: 'success',
                title: 'Impresora agregada',
                text: `Se agregó la impresora de red ${nombreImpresora}`,
                timer: 2000,
                showConfirmButton: false
            });
        });

        // Manejar impresión de prueba (fallback cuando no hay conexión directa)
        $wire.on('imprimir-prueba', (data) => {
            const config = data[0];

            // Mostrar aviso si hay error de conexión directa
            if (config.error) {
                Swal.fire({
                    icon: 'info',
                    title: 'Usando impresión por navegador',
                    html: `<p>No se pudo conectar directamente a la impresora:</p>
                           <small class="text-muted">${config.error}</small>
                           <p class="mt-2">Se usará el diálogo de impresión del navegador.</p>`,
                    showConfirmButton: true,
                    confirmButtonText: 'Continuar'
                }).then(() => {
                    imprimirPorNavegador(config);
                });
            } else {
                imprimirPorNavegador(config);
            }
        });

        // Función para imprimir usando el navegador
        function imprimirPorNavegador(config) {
            // Crear contenido de prueba
            const contenido = `
================================
       IMPRESIÓN DE PRUEBA
================================
Tienda: ${config.nombre_tienda}
Impresora: ${config.impresora || 'No configurada'}
Tipo: ${config.tipo}
Papel: ${config.papel}
Ancho: ${config.ancho} caracteres
================================
Corte automático: ${config.corte ? 'SÍ' : 'NO'}
Abrir cajón: ${config.abrir_cajon ? 'SÍ' : 'NO'}
Sonido: ${config.sonido ? 'SÍ' : 'NO'}
================================
     ¡Configuración correcta!
================================
`;

            // Crear ventana de impresión
            const printWindow = window.open('', '_blank', 'width=400,height=600');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Impresión de Prueba</title>
                    <style>
                        body {
                            font-family: 'Courier New', monospace;
                            font-size: 12px;
                            margin: 0;
                            padding: 10px;
                            width: ${config.papel === '58mm' ? '58mm' : '80mm'};
                        }
                        pre { margin: 0; white-space: pre-wrap; }
                        @media print {
                            @page { margin: 0; }
                            body { width: 100%; }
                        }
                    </style>
                </head>
                <body>
                    <pre>${contenido}</pre>
                </body>
                </html>
            `);
            printWindow.document.close();

            // Esperar a que cargue y luego imprimir
            printWindow.onload = function() {
                printWindow.print();
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            };
        }
    </script>
    @endscript
</div>

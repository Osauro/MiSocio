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
                                            <div class="card-header bg-secondary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-print me-2"></i>
                                                    Impresora
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nombre de la Impresora</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="impresora_nombre"
                                                            wire:model="impresora_nombre" placeholder="Selecciona o escribe el nombre">
                                                        <button type="button" class="btn btn-outline-secondary" id="btn-detectar-impresoras"
                                                            title="Detectar impresoras disponibles">
                                                            <i class="fa-solid fa-search"></i>
                                                        </button>
                                                    </div>
                                                    <small class="text-muted">Haz clic en el botón para detectar impresoras</small>
                                                    @error('impresora_nombre') <span class="text-danger small">{{ $message }}</span> @enderror

                                                    <!-- Lista de impresoras detectadas -->
                                                    <div id="lista-impresoras" class="mt-2" style="display: none;">
                                                        <label class="form-label small">Impresoras detectadas:</label>
                                                        <select class="form-select form-select-sm" id="select-impresora">
                                                            <option value="">Selecciona una impresora...</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Tipo de Impresora</label>
                                                    <select class="form-select" wire:model="impresora_tipo">
                                                        <option value="termica">Térmica (POS)</option>
                                                        <option value="laser">Láser</option>
                                                        <option value="inyeccion">Inyección de Tinta</option>
                                                    </select>
                                                    @error('impresora_tipo') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Ancho de Caracteres</label>
                                                    <input type="number" class="form-control" wire:model="ancho_caracteres"
                                                        min="32" max="80" placeholder="48">
                                                    <small class="text-muted">Para impresoras térmicas (32-80 caracteres)</small>
                                                    @error('ancho_caracteres') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Configuración de Papel -->
                                    <div class="col-md-6 mb-3">
                                        <div class="card border shadow-sm">
                                            <div class="card-header bg-warning text-dark">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-scroll me-2"></i>
                                                    Papel
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Tamaño de Papel</label>
                                                    <select class="form-select" wire:model="papel_tamano">
                                                        <option value="58mm">58mm (Térmica pequeña)</option>
                                                        <option value="80mm">80mm (Térmica estándar)</option>
                                                        <option value="carta">Carta (8.5" x 11")</option>
                                                        <option value="media-carta">Media Carta</option>
                                                    </select>
                                                    @error('papel_tamano') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Copias por Impresión</label>
                                                    <input type="number" class="form-control"
                                                        wire:model="papel_copias" min="1" max="5">
                                                    @error('papel_copias') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Opciones de Impresora Térmica -->
                                        <div class="card border shadow-sm mt-3">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-sliders me-2"></i>
                                                    Opciones de Impresora
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
                                        Guardar Configuración de Impresión
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
        // Detectar impresoras usando la API del navegador
        document.getElementById('btn-detectar-impresoras')?.addEventListener('click', async function() {
            const listaDiv = document.getElementById('lista-impresoras');
            const selectImpresora = document.getElementById('select-impresora');

            // Mostrar que estamos buscando
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            this.disabled = true;

            try {
                // Intentar usar Web USB API para impresoras
                if ('usb' in navigator) {
                    try {
                        const device = await navigator.usb.requestDevice({
                            filters: [
                                { classCode: 7 }, // Printer class
                            ]
                        });
                        if (device) {
                            selectImpresora.innerHTML = `<option value="${device.productName}">${device.productName} (${device.manufacturerName})</option>`;
                            listaDiv.style.display = 'block';
                        }
                    } catch (e) {
                        console.log('USB API no disponible o sin permisos');
                    }
                }

                // Fallback: mostrar opciones comunes
                selectImpresora.innerHTML = `
                    <option value="">Selecciona una impresora...</option>
                    <option value="default">Impresora predeterminada del sistema</option>
                    <option value="EPSON TM-T20III">EPSON TM-T20III</option>
                    <option value="EPSON TM-T88V">EPSON TM-T88V</option>
                    <option value="Star TSP100">Star TSP100</option>
                    <option value="XPrinter XP-58">XPrinter XP-58</option>
                    <option value="XPrinter XP-80">XPrinter XP-80</option>
                    <option value="Impresora personalizada">Otra impresora...</option>
                `;
                listaDiv.style.display = 'block';

                Swal.fire({
                    icon: 'info',
                    title: 'Detección de impresoras',
                    text: 'Se muestran impresoras comunes. Selecciona una o escribe el nombre exacto de tu impresora.',
                    timer: 3000,
                    showConfirmButton: false
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo detectar impresoras: ' + error.message
                });
            } finally {
                this.innerHTML = '<i class="fa-solid fa-search"></i>';
                this.disabled = false;
            }
        });

        // Al seleccionar una impresora
        document.getElementById('select-impresora')?.addEventListener('change', function() {
            const valor = this.value;
            if (valor && valor !== '') {
                document.getElementById('impresora_nombre').value = valor;
                @this.set('impresora_nombre', valor);
            }
        });

        // Manejar impresión de prueba
        $wire.on('imprimir-prueba', (data) => {
            const config = data[0];

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

            Swal.fire({
                icon: 'success',
                title: 'Impresión enviada',
                text: 'Se ha enviado la impresión de prueba. Verifica que se imprimió correctamente.',
                timer: 3000,
                showConfirmButton: false
            });
        });
    </script>
    @endscript
</div>

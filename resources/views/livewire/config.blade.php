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
                                <!-- Botones de acción -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('printer.install.download') }}" class="btn btn-success">
                                            <i class="fa-solid fa-download me-1"></i>
                                            Descargar Instalador
                                        </a>
                                        <button type="button" class="btn btn-outline-primary" wire:click="iniciarServicioPrinter">
                                            <i class="fa-solid fa-play me-1"></i>
                                            Iniciar Servicio
                                        </button>
                                    </div>
                                    <a href="http://localhost:5421" target="_blank" class="btn btn-outline-secondary" title="Abrir en nueva ventana">
                                        <i class="fa-solid fa-external-link-alt me-1"></i>
                                        Abrir en ventana
                                    </a>
                                </div>

                                <!-- Frame de configuración de impresora -->
                                <div class="card border shadow-sm">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="mb-0">
                                            <i class="fa-solid fa-print me-2"></i>
                                            Configuración de Impresora Local
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <iframe
                                            src="http://localhost:5421"
                                            style="width: 100%; height: 550px; border: none;"
                                            id="iframe-impresora"
                                            onload="this.nextElementSibling.style.display='none'; this.style.display='block';"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        </iframe>
                                        <div id="iframe-error" class="alert alert-warning m-3">
                                            <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                            <strong>Servicio no disponible</strong><br>
                                            <p class="mb-2">El servicio MiSocio Printer no está corriendo en <code>http://localhost:5421</code></p>
                                            <ol class="mb-0 ps-3">
                                                <li>Verifica que hayas descargado e instalado el programa con el botón <strong>"Descargar Instalador"</strong></li>
                                                <li>Asegúrate de que el servicio esté iniciado (consulta <strong>"Iniciar Servicio"</strong> para más información)</li>
                                                <li>Recarga esta página después de iniciar el servicio</li>
                                            </ol>
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
</div>

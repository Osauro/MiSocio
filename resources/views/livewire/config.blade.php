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
                                <button class="nav-link {{ $activeTab === 'facebook' ? 'active' : '' }}"
                                    wire:click="setTab('facebook')" type="button">
                                    <i class="fa-brands fa-facebook me-1"></i>Facebook
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
                                wire:click="setTab('facebook')"
                                class="btn btn-sm d-flex flex-column align-items-center justify-content-center py-2 gap-1 {{ $activeTab === 'facebook' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                style="font-size: 0.65rem;">
                                <i class="fa-brands fa-facebook" style="font-size: 1.2rem;"></i>
                                Facebook
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
                            <div class="tab-pane fade show active">

                                <!-- Botón descargar instalador a la derecha (solo PC) -->
                                <div class="d-none d-md-flex justify-content-end mb-3">
                                    <a href="https://fadi.com.bo/download.php?file=installPrinterFADI.bat"
                                       class="btn btn-success">
                                        <i class="fa-solid fa-download me-1"></i>
                                        Descargar Instalador
                                    </a>
                                </div>

                                <div class="row g-3">
                                    <!-- Tarjeta estado del servicio de impresión (solo PC) -->
                                    <div class="col-md-4 d-none d-md-block">
                                        <div class="card border shadow-sm h-100"
                                             x-data="{
                                                estado: 'verificando',
                                                version: '',
                                                async verificar() {
                                                    console.log('Verificando servicio de impresión desde el navegador...');
                                                    this.estado = 'verificando';
                                                    
                                                    try {
                                                        const controller = new AbortController();
                                                        const timeoutId = setTimeout(() => controller.abort(), 3000);
                                                        
                                                        const response = await fetch('http://localhost:1013/status', {
                                                            signal: controller.signal,
                                                            mode: 'cors'
                                                        });
                                                        
                                                        clearTimeout(timeoutId);
                                                        
                                                        if (response.ok) {
                                                            const data = await response.json();
                                                            this.estado = 'conectado';
                                                            this.version = data.version || '';
                                                            console.log('✓ Conectado a localhost:1013', data);
                                                        } else {
                                                            this.estado = 'desconectado';
                                                            console.log('✗ Respuesta no ok:', response.status);
                                                        }
                                                    } catch (error) {
                                                        this.estado = 'desconectado';
                                                        console.log('✗ Error conectando a localhost:1013:', error.message);
                                                    }
                                                }
                                             }"
                                             x-init="verificar()">
                                            <div class="card-header d-flex justify-content-between align-items-center"
                                                 :class="{
                                                    'bg-success text-white': estado === 'conectado',
                                                    'bg-danger text-white': estado === 'desconectado' || estado === 'error',
                                                    'bg-secondary text-white': estado === 'verificando'
                                                 }">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-server me-2"></i>
                                                    App de Impresión
                                                    <span x-show="version" x-text="'v' + version" class="small ms-1 opacity-75"></span>
                                                </h5>
                                                <span class="badge bg-white bg-opacity-25 d-flex align-items-center gap-1">
                                                    <i class="fa-solid fa-circle small"
                                                       :class="{
                                                           'text-success': estado === 'conectado',
                                                           'text-danger': estado === 'desconectado' || estado === 'error',
                                                           'text-warning': estado === 'verificando'
                                                       }"></i>
                                                    <span x-text="estado === 'conectado' ? 'Conectado' : (estado === 'desconectado' ? 'Desconectado' : (estado === 'error' ? 'Error' : 'Verificando...'))"></span>
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted small mb-3">
                                                    <i class="fa-solid fa-link me-1"></i>
                                                    localhost:1013
                                                </p>

                                                <!-- Botones de prueba -->
                                                <p class="fw-semibold mb-2">Pruebas de impresión:</p>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                            :disabled="estado !== 'conectado'"
                                                            @click="$wire.dispatch('imprimir-prueba-tipo', { tipo: 'venta' })">
                                                        <i class="fa-solid fa-cart-shopping me-1"></i>
                                                        Última Venta
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning btn-sm"
                                                            :disabled="estado !== 'conectado'"
                                                            @click="$wire.dispatch('imprimir-prueba-tipo', { tipo: 'prestamo' })">
                                                        <i class="fa-solid fa-hand-holding-dollar me-1"></i>
                                                        Último Préstamo
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info btn-sm"
                                                            :disabled="estado !== 'conectado'"
                                                            @click="$wire.dispatch('imprimir-prueba-tipo', { tipo: 'inventario' })">
                                                        <i class="fa-solid fa-boxes-stacked me-1"></i>
                                                        Último Inventario
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-footer d-flex justify-content-end">
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        @click="verificar()">
                                                    <i class="fa-solid fa-rotate-right me-1"></i>
                                                    Verificar conexión
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tarjeta configuración de papel -->
                                    <div class="col-md-4">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-file me-2"></i>
                                                    Configuración de Papel
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Tamaño de Papel</label>
                                                    <select class="form-select"
                                                            wire:model="papel_tamano"
                                                            wire:change="guardarImpresion">
                                                        <option value="58mm">58mm (Térmico pequeño)</option>
                                                        <option value="80mm">80mm (Térmico estándar)</option>
                                                    </select>
                                                    <small class="text-muted">
                                                        <i class="fa-solid fa-info-circle me-1"></i>
                                                        Selecciona el ancho del papel de tu impresora térmica. Esto afecta tanto la impresión directa como los PDFs generados.
                                                    </small>
                                                    @error('papel_tamano') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tarjeta configuración impresión automática -->
                                    <div class="col-md-4">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-print me-2"></i>
                                                    Impresión Automática
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted small mb-3">
                                                    Al finalizar el proceso se imprimirá automáticamente el ticket correspondiente (requiere la app de impresión activa).
                                                </p>

                                                <!-- Toggle Ventas -->
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                                    <div>
                                                        <p class="fw-semibold mb-0">
                                                            <i class="fa-solid fa-cart-shopping me-2 text-primary"></i>
                                                            Ventas
                                                        </p>
                                                        <small class="text-muted">Imprimir ticket al finalizar una venta</small>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                               wire:model="impresion_auto_venta"
                                                               wire:change="guardarImpresion"
                                                               id="toggle_auto_venta"
                                                               style="width: 3rem; height: 1.5rem;">
                                                    </div>
                                                </div>

                                                <!-- Toggle Préstamos -->
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                                    <div>
                                                        <p class="fw-semibold mb-0">
                                                            <i class="fa-solid fa-hand-holding-dollar me-2 text-warning"></i>
                                                            Préstamos
                                                        </p>
                                                        <small class="text-muted">Imprimir recibo al finalizar un préstamo</small>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                               wire:model="impresion_auto_prestamo"
                                                               wire:change="guardarImpresion"
                                                               id="toggle_auto_prestamo"
                                                               style="width: 3rem; height: 1.5rem;">
                                                    </div>
                                                </div>

                                                <!-- Toggle Inventario -->
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="fw-semibold mb-0">
                                                            <i class="fa-solid fa-boxes-stacked me-2 text-info"></i>
                                                            Inventario
                                                        </p>
                                                        <small class="text-muted">Imprimir reporte al finalizar un inventario</small>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                               wire:model="impresion_auto_inventario"
                                                               wire:change="guardarImpresion"
                                                               id="toggle_auto_inventario"
                                                               style="width: 3rem; height: 1.5rem;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer border-0">
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
                                                            wire:model="facebook_enabled"
                                                            wire:change="guardarFacebook"
                                                            id="facebookEnabled">
                                                        <label class="form-check-label fw-semibold" for="facebookEnabled">
                                                            Habilitar Facebook
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Page ID</label>
                                                    <input type="text" class="form-control"
                                                        wire:model="facebook_page_id"
                                                        wire:blur="guardarFacebook"
                                                        placeholder="ID de la página de Facebook">
                                                    @error('facebook_page_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Access Token</label>
                                                    <input type="password" class="form-control"
                                                        wire:model="facebook_access_token"
                                                        wire:blur="guardarFacebook"
                                                        placeholder="Token de acceso de la página">
                                                    <small class="text-muted">Obténlo desde Facebook Developers</small>
                                                    @error('facebook_access_token') <span class="text-danger small">{{ $message }}</span> @enderror
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
    </script>
    @endscript
</div>

<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\TenantConfig;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Config extends Component
{
    use SweetAlertTrait, RequiresTenant, WithFileUploads;

    public $activeTab = 'general';

    // General - Tienda
    public $nombre_tienda;
    public $direccion;
    public $telefono;
    public $email;
    public $nit;

    // General - Propietario
    public $propietario_nombre;
    public $propietario_celular;

    // General - Otros
    public $sueldo_base;
    public $ip_local;

    // Logo
    public $logo;
    public $logo_actual;
    public $nuevo_logo;

    // Impresión
    public $impresora_nombre;
    public $impresora_tipo;
    public $papel_tamano;
    public $papel_copias;
    public $corte_automatico;
    public $abrir_cajon;
    public $sonido_apertura;
    public $ancho_caracteres;
    public $impresion_auto_venta;
    public $impresion_auto_prestamo;
    public $impresion_auto_inventario;
    public $print_agent_secret_key;

    // WhatsApp API
    public $whatsapp_token;
    public $whatsapp_phone_id;
    public $whatsapp_enabled;

    // Préstamos
    public $prestamos_enabled;
    public $prestamos_categoria_id;

    // Hospedajes
    public $hospedajes_enabled;

    // Compras
    public $compras_enabled;

    // Ventas
    public $ventas_enabled;
    public $ventas_solo_unidad;

    // Facebook API
    public $facebook_page_id;
    public $facebook_access_token;
    public $facebook_enabled;

    // Importación
    public $formato_importacion;
    public $archivo_importacion;

    protected function rules()
    {
        return [
            // General - Tienda
            'nombre_tienda' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'nit' => 'nullable|string|max:50',
            // General - Propietario
            'propietario_nombre' => 'nullable|string|max:255',
            'propietario_celular' => 'nullable|string|max:50',
            // General - Otros
            'sueldo_base' => 'nullable|numeric|min:0',
            'ip_local' => 'nullable|string|max:45',
            // Logo
            'nuevo_logo' => 'nullable|image|max:1024',
            // Impresión
            'impresora_nombre' => 'nullable|string|max:255',
            'impresora_tipo' => 'required|in:termica,laser,inyeccion',
            'papel_tamano' => 'required|in:58mm,80mm,carta,media-carta',
            'papel_copias' => 'required|integer|min:1|max:5',
            'corte_automatico' => 'boolean',
            'abrir_cajon' => 'boolean',
            'sonido_apertura' => 'boolean',
            'ancho_caracteres' => 'nullable|integer|min:32|max:80',
            // WhatsApp
            'whatsapp_token' => 'nullable|string|max:500',
            'whatsapp_phone_id' => 'nullable|string|max:100',
            'whatsapp_enabled' => 'boolean',
            // Facebook
            'facebook_page_id' => 'nullable|string|max:100',
            'facebook_access_token' => 'nullable|string|max:500',
            'facebook_enabled' => 'boolean',
            // Préstamos
            'prestamos_enabled' => 'boolean',
            'prestamos_categoria_id' => 'nullable|integer|exists:categorias,id',
            // Hospedajes
            'hospedajes_enabled' => 'boolean',
            // Compras
            'compras_enabled' => 'boolean',
            // Ventas
            'ventas_enabled' => 'boolean',
            'ventas_solo_unidad' => 'boolean',
            // Importación
            'formato_importacion' => 'required|in:excel,csv,json',
        ];
    }

    public function mount()
    {
        $savedTab = $_COOKIE['config_active_tab'] ?? null;
        $allowed = ['general', 'impresion', 'whatsapp', 'modulos', 'importacion'];
        if ($savedTab && in_array($savedTab, $allowed)) {
            $this->activeTab = $savedTab;
        }

        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());

        // General - Tienda
        // Si no hay nombre en config, cargar desde el tenant
        $this->nombre_tienda = $config->nombre_tienda ?: currentTenant()?->name;
        $this->direccion = $config->direccion;
        $this->telefono = $config->telefono;
        $this->email = $config->email;
        $this->nit = $config->nit;

        // General - Propietario
        $this->propietario_nombre = $config->propietario_nombre;
        $this->propietario_celular = $config->propietario_celular;

        // General - Otros
        $this->sueldo_base = $config->sueldo_base;
        $this->ip_local = $config->ip_local;

        // Logo
        $this->logo_actual = $config->logo;

        // Impresión
        $this->impresora_nombre = $config->impresora_nombre;
        $this->impresora_tipo = $config->impresora_tipo;
        $this->papel_tamano = $config->papel_tamano;
        $this->papel_copias = $config->papel_copias;
        $this->corte_automatico = $config->corte_automatico ?? true;
        $this->abrir_cajon = $config->abrir_cajon ?? false;
        $this->sonido_apertura = $config->sonido_apertura ?? false;
        $this->ancho_caracteres = $config->ancho_caracteres ?? 48;
        $this->impresion_auto_venta = $config->impresion_auto_venta ?? false;
        $this->impresion_auto_prestamo = $config->impresion_auto_prestamo ?? false;
        $this->impresion_auto_inventario = $config->impresion_auto_inventario ?? false;
        $this->print_agent_secret_key = $config->print_agent_secret_key ?? '';

        // WhatsApp
        $this->whatsapp_token = $config->whatsapp_token;
        $this->whatsapp_phone_id = $config->whatsapp_phone_id;
        $this->whatsapp_enabled = $config->whatsapp_enabled;

        // Préstamos
        $this->prestamos_enabled = $config->prestamos_enabled ?? true;
        $this->prestamos_categoria_id = $config->prestamos_categoria_id;

        // Hospedajes
        $this->hospedajes_enabled = $config->hospedajes_enabled ?? false;

        // Compras
        $this->compras_enabled = $config->compras_enabled ?? true;

        // Ventas
        $this->ventas_enabled = $config->ventas_enabled ?? true;
        $this->ventas_solo_unidad = $config->ventas_solo_unidad ?? false;

        // Facebook
        $this->facebook_page_id = $config->facebook_page_id;
        $this->facebook_access_token = $config->facebook_access_token;
        $this->facebook_enabled = $config->facebook_enabled;

        // Importación
        $this->formato_importacion = $config->formato_importacion;
    }

    public function setTab($tab)
    {
        $allowed = ['general', 'impresion', 'whatsapp', 'modulos', 'importacion'];
        if (in_array($tab, $allowed)) {
            $this->activeTab = $tab;
        }
    }

    public function guardarGeneral()
    {
        $this->validate([
            'nombre_tienda' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'nit' => 'nullable|string|max:50',
            'propietario_nombre' => 'nullable|string|max:255',
            'propietario_celular' => 'nullable|string|max:50',
            'sueldo_base' => 'nullable|numeric|min:0',
            'ip_local' => 'nullable|string|max:45',
            'nuevo_logo' => 'nullable|image|max:1024',
        ]);

        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());

        // Procesar logo si se subió uno nuevo
        $logoPath = $config->logo;
        if ($this->nuevo_logo) {
            // Eliminar logo anterior si existe
            if ($config->logo && Storage::disk('public')->exists($config->logo)) {
                Storage::disk('public')->delete($config->logo);
            }
            // Guardar nuevo logo
            $logoPath = $this->nuevo_logo->store('logos/' . $this->getTenantId(), 'public');
            $this->logo_actual = $logoPath;
            $this->nuevo_logo = null;
        }

        $config->update([
            'nombre_tienda' => $this->nombre_tienda,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'nit' => $this->nit,
            'propietario_nombre' => $this->propietario_nombre,
            'propietario_celular' => $this->propietario_celular,
            'sueldo_base' => $this->sueldo_base ?? 0,
            'ip_local' => $this->ip_local,
            'logo' => $logoPath,
        ]);

        // Actualizar nombre del tenant si cambió
        if ($this->nombre_tienda) {
            $tenant = currentTenant();
            if ($tenant && $tenant->name !== $this->nombre_tienda) {
                $tenant->update(['name' => $this->nombre_tienda]);
            }
        }

        $this->toast('success', 'Configuración guardada');
    }

    public function updatedNuevoLogo()
    {
        if ($this->nuevo_logo) {
            $this->guardarGeneral();
        }
    }

    public function guardarImpresion()
    {
        $this->validate([
            'impresora_nombre' => 'nullable|string|max:255',
            'impresora_tipo' => 'required|in:termica,laser,inyeccion',
            'papel_tamano' => 'required|in:58mm,80mm,carta,media-carta',
            'papel_copias' => 'required|integer|min:1|max:5',
            'corte_automatico' => 'boolean',
            'abrir_cajon' => 'boolean',
            'sonido_apertura' => 'boolean',
            'ancho_caracteres' => 'nullable|integer|min:32|max:80',
            'impresion_auto_venta' => 'boolean',
            'impresion_auto_prestamo' => 'boolean',
            'impresion_auto_inventario' => 'boolean',
            'print_agent_secret_key' => 'nullable|string|size:64|regex:/^[a-fA-F0-9]+$/',
        ]);

        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());
        $config->update([
            'impresora_nombre' => $this->impresora_nombre,
            'impresora_tipo' => $this->impresora_tipo,
            'papel_tamano' => $this->papel_tamano,
            'papel_copias' => $this->papel_copias,
            'corte_automatico' => $this->corte_automatico ?? true,
            'abrir_cajon' => $this->abrir_cajon ?? false,
            'sonido_apertura' => $this->sonido_apertura ?? false,
            'ancho_caracteres' => $this->ancho_caracteres ?? 48,
            'impresion_auto_venta' => $this->impresion_auto_venta ?? false,
            'impresion_auto_prestamo' => $this->impresion_auto_prestamo ?? false,
            'impresion_auto_inventario' => $this->impresion_auto_inventario ?? false,
            'print_agent_secret_key' => $this->print_agent_secret_key ?: null,
        ]);

        $this->toast('success', 'Impresión guardada');
    }

    public function regenerarPrintKey()
    {
        $newKey = bin2hex(random_bytes(32));
        $this->print_agent_secret_key = $newKey;

        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());
        $config->update(['print_agent_secret_key' => $newKey]);

        $this->toast('success', 'Clave regenerada — cópiala en el Print Agent');
    }

    public function eliminarLogo()
    {
        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());

        if ($config->logo && Storage::disk('public')->exists($config->logo)) {
            Storage::disk('public')->delete($config->logo);
        }

        $config->update(['logo' => null]);
        $this->logo_actual = null;

        $this->alertSuccess('Logo eliminado correctamente');
    }

    public function detectarImpresoras()
    {
        // En hosting compartido no hay impresoras en el servidor.
        // Las impresoras están en el PC local del usuario.
        // Emitimos evento para que JavaScript detecte usando QZ Tray o muestre opciones.
        $this->dispatch('detectar-impresoras-local');
    }

    public function impresionPrueba()
    {
        if (!$this->impresora_nombre) {
            $this->alertError('Configura una impresora primero');
            return;
        }

        // En hosting compartido, la impresión debe hacerse desde el navegador del usuario
        // usando QZ Tray o el diálogo de impresión
        $this->dispatch('imprimir-prueba-qz', [
            'impresora' => $this->impresora_nombre,
            'tipo' => $this->impresora_tipo,
            'papel' => $this->papel_tamano,
            'corte' => $this->corte_automatico,
            'abrir_cajon' => $this->abrir_cajon,
            'sonido' => $this->sonido_apertura,
            'ancho' => $this->ancho_caracteres,
            'nombre_tienda' => $this->nombre_tienda ?? 'Mi Tienda',
        ]);
    }

    public function impresionPruebaLegacy()
    {
        if (!$this->impresora_nombre) {
            $this->alertError('Configura una impresora primero');
            return;
        }

        /** @var \App\Services\EscposPrinterService $svc */
        $svc  = app(\App\Services\EscposPrinterService::class);
        $key  = $this->print_agent_secret_key ?? config('print_agent.secret_key');
        $cols = ($this->papel_tamano === '58mm') ? 32 : 48;

        if (empty($key)) {
            $this->alertError('Configura la clave secreta del Print Agent primero');
            return;
        }

        $header = $svc->buildEscHeader([
            'store' => $this->nombre_tienda ?? 'Mi Tienda',
            'title' => 'IMPRESIÓN DE PRUEBA',
            'date'  => now()->format('d/m/Y H:i:s'),
        ], $cols);

        $body = "\x1B\x61\x01"
              . "Impresora: {$this->impresora_nombre}\n"
              . "Papel: {$this->papel_tamano}\n"
              . "Ancho: {$cols} caracteres\n"
              . str_repeat('-', $cols) . "\n"
              . "Corte: " . ($this->corte_automatico ? 'SÍ' : 'NO') . "\n"
              . "Cajón: " . ($this->abrir_cajon ? 'SÍ' : 'NO') . "\n";

        $footer = $svc->buildEscFooter(
            '¡Configuración correcta!',
            (bool) $this->corte_automatico,
            (bool) $this->abrir_cajon,
            5, $cols
        );

        $job = [
            'printer' => $this->impresora_nombre,
            'logo'    => false,
            'header'  => $svc->encryptSection($key, $header),
            'body'    => $svc->encryptSection($key, $body),
            'footer'  => $svc->encryptSection($key, $footer),
        ];

        // La llamada la hace el navegador (el agente corre en el PC del cliente)
        $this->dispatch('enviar-a-agente',
            agentUrl: config('print_agent.base_url'),
            job: $job,
            successMsg: 'Impresión de prueba enviada'
        );
    }

    public function imprimirUltimaVenta()
    {
        $tenantId = $this->getTenantId();
        $venta = \App\Models\Venta::with(['cliente', 'user', 'ventaItems.producto' => fn($q) => $q->withTrashed()])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->first();

        if (!$venta) {
            $this->alertError('No hay ventas registradas');
            return;
        }

        // Redirigir al TicketController (devuelve JSON) o usar el servicio directamente
        /** @var \App\Services\EscposPrinterService $svc */
        $svc    = app(\App\Services\EscposPrinterService::class);
        $config = \App\Models\TenantConfig::getOrCreateForTenant($tenantId);
        $cols   = ($config->papel_tamano === '58mm') ? 32 : 48;
        $key    = $config->print_agent_secret_key ?? config('print_agent.secret_key');

        if (empty($key)) {
            $this->alertError('Configura la clave secreta del Print Agent primero');
            return;
        }

        $header = [
            'store'   => $config->nombre_tienda ?? 'MI TIENDA',
            'address' => $config->direccion ?? '',
            'phone'   => $config->telefono ?? '',
            'nit'     => $config->nit ?? '',
            'title'   => 'VENTA #' . $venta->numero_folio,
            'date'    => $venta->created_at->format('d/m/Y H:i:s'),
            'user'    => $venta->user->name ?? '',
            'client'  => $venta->cliente->nombre ?? '',
        ];

        $items = $venta->ventaItems->map(function ($item) {
            $nombre = $item->producto ? $item->producto->nombre : ($item->nombre ?? 'Producto');
            return [
                'nombre'   => $nombre,
                'cantidad' => $item->cantidad . ($item->medida ? ' ' . $item->medida : ''),
                'precio'   => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
            ];
        })->toArray();

        $totales = array_filter([
            'TOTAL'    => (float) $venta->total,
            'efectivo' => (float) ($venta->efectivo ?? 0),
            'online'   => (float) ($venta->online   ?? 0),
            'credito'  => (float) ($venta->credito  ?? 0),
            'cambio'   => (float) ($venta->cambio   ?? 0),
        ], fn($v) => $v > 0);
        $totales['TOTAL'] = (float) $venta->total;

        $job = [
            'printer' => $config->impresora_nombre ?? '',
            'logo'    => (bool) ($config->logo ?? false),
            'header'  => $svc->encryptSection($key, $svc->buildEscHeader($header, $cols)),
            'body'    => $svc->encryptSection($key, $svc->buildEscBody($items, $cols)),
            'totals'  => $svc->encryptSection($key, $svc->buildEscTotals($totales, $cols)),
            'footer'  => $svc->encryptSection($key, $svc->buildEscFooter(
                '¡Gracias por su compra!',
                (bool) ($config->corte_automatico ?? true),
                (bool) ($config->abrir_cajon ?? false),
                3, $cols
            )),
        ];

        // La llamada la hace el navegador (el agente corre en el PC del cliente)
        $this->dispatch('enviar-a-agente',
            agentUrl: config('print_agent.base_url'),
            job: $job,
            successMsg: 'Venta #' . $venta->numero_folio . ' enviada a imprimir'
        );
    }

    public function imprimirUltimoPrestamo()
    {
        $tenantId = $this->getTenantId();
        $prestamo = \App\Models\Prestamo::with(['cliente', 'user', 'prestamoItems.producto' => fn($q) => $q->withTrashed()])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->first();

        if (!$prestamo) {
            $this->alertError('No hay préstamos registrados');
            return;
        }

        /** @var \App\Services\EscposPrinterService $svc */
        $svc    = app(\App\Services\EscposPrinterService::class);
        $config = \App\Models\TenantConfig::getOrCreateForTenant($tenantId);
        $cols   = ($config->papel_tamano === '58mm') ? 32 : 48;
        $key    = $config->print_agent_secret_key ?? config('print_agent.secret_key');

        if (empty($key)) {
            $this->alertError('Configura la clave secreta del Print Agent primero');
            return;
        }

        $header = [
            'store'  => $config->nombre_tienda ?? 'MI TIENDA',
            'address'=> $config->direccion ?? '',
            'phone'  => $config->telefono ?? '',
            'nit'    => $config->nit ?? '',
            'title'  => 'PRÉSTAMO #' . ($prestamo->numero_folio ?? $prestamo->id),
            'date'   => $prestamo->created_at->format('d/m/Y H:i:s'),
            'user'   => $prestamo->user->name ?? '',
            'client' => $prestamo->cliente->nombre ?? '',
        ];

        $items = $prestamo->prestamoItems->map(function ($item) {
            $nombre = $item->producto ? $item->producto->nombre : ($item->nombre ?? 'Producto');
            return [
                'nombre'   => $nombre,
                'cantidad' => $item->cantidad . ($item->medida ? ' ' . $item->medida : ''),
                'precio'   => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
            ];
        })->toArray();

        $totales = ['TOTAL' => (float) $prestamo->total];

        $job = [
            'printer' => $config->impresora_nombre ?? '',
            'logo'    => (bool) ($config->logo ?? false),
            'header'  => $svc->encryptSection($key, $svc->buildEscHeader($header, $cols)),
            'body'    => $svc->encryptSection($key, $svc->buildEscBody($items, $cols)),
            'totals'  => $svc->encryptSection($key, $svc->buildEscTotals($totales, $cols)),
            'footer'  => $svc->encryptSection($key, $svc->buildEscFooter(
                '¡Gracias!',
                (bool) ($config->corte_automatico ?? true),
                false, 3, $cols
            )),
        ];

        $this->dispatch('enviar-a-agente',
            agentUrl: config('print_agent.base_url'),
            job: $job,
            successMsg: 'Préstamo #' . ($prestamo->numero_folio ?? $prestamo->id) . ' enviado a imprimir'
        );
    }

    public function guardarWhatsApp()
    {
        $this->validate([
            'whatsapp_token' => 'nullable|string|max:500',
            'whatsapp_phone_id' => 'nullable|string|max:100',
            'whatsapp_enabled' => 'boolean',
        ]);

        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());
        $config->update([
            'whatsapp_token' => $this->whatsapp_token,
            'whatsapp_phone_id' => $this->whatsapp_phone_id,
            'whatsapp_enabled' => $this->whatsapp_enabled ?? false,
        ]);

        $this->toast('success', 'WhatsApp guardado');
    }

    public function guardarFacebook()
    {
        $this->validate([
            'facebook_page_id' => 'nullable|string|max:100',
            'facebook_access_token' => 'nullable|string|max:500',
            'facebook_enabled' => 'boolean',
        ]);

        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());
        $config->update([
            'facebook_page_id' => $this->facebook_page_id,
            'facebook_access_token' => $this->facebook_access_token,
            'facebook_enabled' => $this->facebook_enabled ?? false,
        ]);

        $this->toast('success', 'Facebook guardado');
    }

    public function guardarModulos()
    {
        $this->validate([
            'prestamos_enabled' => 'boolean',
            'prestamos_categoria_id' => 'nullable|integer|exists:categorias,id',
            'hospedajes_enabled' => 'boolean',
            'compras_enabled' => 'boolean',
            'ventas_enabled' => 'boolean',
            'ventas_solo_unidad' => 'boolean',
        ]);

        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());
        $config->update([
            'prestamos_enabled'      => $this->prestamos_enabled ?? true,
            'prestamos_categoria_id' => $this->prestamos_categoria_id,
            'hospedajes_enabled'     => $this->hospedajes_enabled ?? false,
            'compras_enabled'        => $this->compras_enabled ?? true,
            'ventas_enabled'         => $this->ventas_enabled ?? true,
            'ventas_solo_unidad'     => $this->ventas_solo_unidad ?? false,
        ]);

        $this->toast('success', 'Módulos guardados');
        $this->dispatch('recargar-pagina');
    }

    public function resetearTenant()
    {
        $tenantId = $this->getTenantId();

        \Illuminate\Support\Facades\DB::transaction(function () use ($tenantId) {
            // Obtener IDs de registros padre para borrar hijos sin tenant_id
            $ventaIds     = \Illuminate\Support\Facades\DB::table('ventas')->where('tenant_id', $tenantId)->pluck('id');
            $compraIds    = \Illuminate\Support\Facades\DB::table('compras')->where('tenant_id', $tenantId)->pluck('id');
            $prestamoIds  = \Illuminate\Support\Facades\DB::table('prestamos')->where('tenant_id', $tenantId)->pluck('id');
            $hospedajeIds = \Illuminate\Support\Facades\DB::table('hospedajes')->where('tenant_id', $tenantId)->pluck('id');
            $inventarioIds = \Illuminate\Support\Facades\DB::table('inventarios')->where('tenant_id', $tenantId)->pluck('id');

            // Borrar hijos primero
            if ($ventaIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('venta_items')->whereIn('venta_id', $ventaIds)->delete();
            }
            if ($compraIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('compra_items')->whereIn('compra_id', $compraIds)->delete();
            }
            if ($prestamoIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('prestamo_items')->whereIn('prestamo_id', $prestamoIds)->delete();
            }
            if ($hospedajeIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('hospedaje_habitaciones')->whereIn('hospedaje_id', $hospedajeIds)->delete();
            }
            if ($inventarioIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('inventario_items')->whereIn('inventario_id', $inventarioIds)->delete();
            }

            // Borrar padres
            \Illuminate\Support\Facades\DB::table('ventas')->where('tenant_id', $tenantId)->delete();
            \Illuminate\Support\Facades\DB::table('compras')->where('tenant_id', $tenantId)->delete();
            \Illuminate\Support\Facades\DB::table('prestamos')->where('tenant_id', $tenantId)->delete();
            \Illuminate\Support\Facades\DB::table('hospedajes')->where('tenant_id', $tenantId)->delete();
            \Illuminate\Support\Facades\DB::table('inventarios')->where('tenant_id', $tenantId)->delete();
            \Illuminate\Support\Facades\DB::table('movimientos')->where('tenant_id', $tenantId)->delete();
            \Illuminate\Support\Facades\DB::table('kardex')->where('tenant_id', $tenantId)->delete();
        });

        $this->dispatch('datos-reseteados');
    }

    public function resetearStock()
    {
        $tenantId = $this->getTenantId();

        \App\Models\Producto::where('tenant_id', $tenantId)->update(['stock' => 0]);

        $this->dispatch('stock-reseteado');
    }

    public function guardarImportacion()
    {
        $this->validate([
            'formato_importacion' => 'required|in:excel,csv,json',
        ]);

        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());
        $config->update([
            'formato_importacion' => $this->formato_importacion,
        ]);

        $this->toast('success', 'Importación guardada');
    }

    public function iniciarServicioPrinter()
    {
        $printerPath = 'C:\\PrinterFADI';
        $vbsFile = $printerPath . '\\servicio_oculto.vbs';
        $batFile = $printerPath . '\\printerStart.bat';

        // Verificar si existe el directorio
        if (!is_dir($printerPath)) {
            return $this->alertError('El servicio PrinterFADI no está instalado en ' . $printerPath);
        }

        // Verificar si el servicio ya está corriendo en el puerto 1013
        $command = 'netstat -ano | findstr ":1013" | findstr "LISTENING"';
        exec($command, $output, $returnCode);

        if ($returnCode === 0 && count($output) > 0) {
            // El servicio ya está corriendo, abrir el navegador
            $url = 'http://localhost:1013';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen('start "" "' . $url . '"', 'r'));
            }
            return $this->alertSuccess('El servicio ya está en ejecución. Abriendo navegador...');
        }

        // Intentar ejecutar el VBS (preferido porque es silencioso)
        if (file_exists($vbsFile)) {
            try {
                // Ejecutar el VBS en segundo plano usando wscript
                $command = 'wscript.exe "' . $vbsFile . '"';
                pclose(popen($command, 'r'));

                // Esperar 3 segundos y abrir navegador
                sleep(3);
                $url = 'http://localhost:1013';
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    pclose(popen('start "" "' . $url . '"', 'r'));
                }

                return $this->alertSuccess('Servicio PrinterFADI iniciado correctamente. Abriendo navegador...');
            } catch (\Exception $e) {
                // Si falla, intentar con el BAT
            }
        }

        // Intentar con el BAT si el VBS no existe o falló
        if (file_exists($batFile)) {
            try {
                $command = 'start /MIN "" "' . $batFile . '"';
                pclose(popen($command, 'r'));

                // Esperar 3 segundos y abrir navegador
                sleep(3);
                $url = 'http://localhost:1013';
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    pclose(popen('start "" "' . $url . '"', 'r'));
                }

                return $this->alertSuccess('Servicio PrinterFADI iniciado correctamente. Abriendo navegador...');
            } catch (\Exception $e) {
                return $this->alertError('Error al iniciar el servicio: ' . $e->getMessage());
            }
        }

        return $this->alertError('No se encontró el archivo de inicio del servicio en ' . $printerPath);
    }

    public function verificarServicioImpresion()
    {
        $baseUrl = rtrim(config('print_agent.base_url', 'http://localhost:9876'), '/');
        $connected = false;
        $version   = '';

        try {
            $ch = curl_init($baseUrl . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

            $response  = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 400) {
                $connected = true;
                if ($response) {
                    $data = json_decode($response, true);
                    $version = $data['version'] ?? '';
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('PrintAgent: error verificando servicio: ' . $e->getMessage());
        }

        // Fallback: verificar si el puerto está abierto
        if (!$connected) {
            $parsed = parse_url($baseUrl);
            $host   = $parsed['host'] ?? '127.0.0.1';
            $port   = $parsed['port'] ?? 9876;
            try {
                $socket = @fsockopen($host, $port, $errno, $errstr, 2);
                if ($socket) {
                    fclose($socket);
                    $connected = true;
                }
            } catch (\Throwable $e) {
                // sin conexión
            }
        }

        $this->dispatch('printer-status', connected: $connected, version: $version);
    }

    public function render()
    {
        $categorias     = Categoria::orderBy('nombre')->get();
        $printAgentUrl  = rtrim(config('print_agent.base_url', 'http://localhost:9876'), '/');
        return view('livewire.config', compact('categorias', 'printAgentUrl'));
    }
}

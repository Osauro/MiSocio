<?php

namespace App\Livewire;

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

    // WhatsApp API
    public $whatsapp_token;
    public $whatsapp_phone_id;
    public $whatsapp_enabled;

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
            // Importación
            'formato_importacion' => 'required|in:excel,csv,json',
        ];
    }

    public function mount()
    {
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());

        // General - Tienda
        $this->nombre_tienda = $config->nombre_tienda;
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

        // WhatsApp
        $this->whatsapp_token = $config->whatsapp_token;
        $this->whatsapp_phone_id = $config->whatsapp_phone_id;
        $this->whatsapp_enabled = $config->whatsapp_enabled;

        // Facebook
        $this->facebook_page_id = $config->facebook_page_id;
        $this->facebook_access_token = $config->facebook_access_token;
        $this->facebook_enabled = $config->facebook_enabled;

        // Importación
        $this->formato_importacion = $config->formato_importacion;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
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

        $this->success('Configuración general guardada correctamente');
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
        ]);

        $this->success('Configuración de impresión guardada correctamente');
    }

    public function eliminarLogo()
    {
        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());

        if ($config->logo && Storage::disk('public')->exists($config->logo)) {
            Storage::disk('public')->delete($config->logo);
        }

        $config->update(['logo' => null]);
        $this->logo_actual = null;

        $this->success('Logo eliminado correctamente');
    }

    public function detectarImpresoras()
    {
        $impresoras = [];

        // Detectar sistema operativo y listar impresoras
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: usar wmic
            $output = shell_exec('wmic printer get name,portname,drivername 2>&1');
            if ($output) {
                $lines = explode("\n", trim($output));
                array_shift($lines); // Quitar encabezado
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        // Parsear la línea (formato: DriverName  Name  PortName)
                        $parts = preg_split('/\s{2,}/', $line);
                        if (count($parts) >= 2) {
                            $impresoras[] = [
                                'nombre' => $parts[1] ?? $parts[0],
                                'driver' => $parts[0] ?? '',
                                'puerto' => $parts[2] ?? '',
                                'tipo' => 'sistema'
                            ];
                        }
                    }
                }
            }
        } else {
            // Linux/Mac: usar lpstat
            $output = shell_exec('lpstat -p 2>&1');
            if ($output && strpos($output, 'not found') === false) {
                $lines = explode("\n", trim($output));
                foreach ($lines as $line) {
                    if (preg_match('/printer\s+(\S+)/', $line, $matches)) {
                        $impresoras[] = [
                            'nombre' => $matches[1],
                            'driver' => '',
                            'puerto' => '',
                            'tipo' => 'sistema'
                        ];
                    }
                }
            }
        }

        $this->dispatch('impresoras-detectadas', ['impresoras' => $impresoras]);

        if (empty($impresoras)) {
            $this->info('No se detectaron impresoras del sistema. Puedes agregar una manualmente.');
        } else {
            $this->success('Se detectaron ' . count($impresoras) . ' impresora(s)');
        }
    }

    public function impresionPrueba()
    {
        // Emitir evento para que JavaScript maneje la impresión de prueba
        $this->dispatch('imprimir-prueba', [
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

        $this->success('Configuración de WhatsApp guardada correctamente');
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

        $this->success('Configuración de Facebook guardada correctamente');
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

        $this->success('Configuración de importación guardada correctamente');
    }

    public function render()
    {
        return view('livewire.config');
    }
}

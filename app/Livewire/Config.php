<?php

namespace App\Livewire;

use App\Models\TenantConfig;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Livewire\Component;
use Livewire\WithFileUploads;

class Config extends Component
{
    use SweetAlertTrait, RequiresTenant, WithFileUploads;

    public $activeTab = 'general';

    // General
    public $sueldo_base;
    public $ip_local;

    // Impresión
    public $impresora_nombre;
    public $impresora_tipo;
    public $papel_tamano;
    public $papel_copias;

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
            // General
            'sueldo_base' => 'nullable|numeric|min:0',
            'ip_local' => 'nullable|string|max:45',
            // Impresión
            'impresora_nombre' => 'nullable|string|max:255',
            'impresora_tipo' => 'required|in:termica,laser,inyeccion',
            'papel_tamano' => 'required|in:58mm,80mm,carta,media-carta',
            'papel_copias' => 'required|integer|min:1|max:5',
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

        // General
        $this->sueldo_base = $config->sueldo_base;
        $this->ip_local = $config->ip_local;

        // Impresión
        $this->impresora_nombre = $config->impresora_nombre;
        $this->impresora_tipo = $config->impresora_tipo;
        $this->papel_tamano = $config->papel_tamano;
        $this->papel_copias = $config->papel_copias;

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
            'sueldo_base' => 'nullable|numeric|min:0',
            'ip_local' => 'nullable|string|max:45',
        ]);

        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());
        $config->update([
            'sueldo_base' => $this->sueldo_base ?? 0,
            'ip_local' => $this->ip_local,
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
        ]);

        $config = TenantConfig::getOrCreateForTenant($this->getTenantId());
        $config->update([
            'impresora_nombre' => $this->impresora_nombre,
            'impresora_tipo' => $this->impresora_tipo,
            'papel_tamano' => $this->papel_tamano,
            'papel_copias' => $this->papel_copias,
        ]);

        $this->success('Configuración de impresión guardada correctamente');
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

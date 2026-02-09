<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantConfig extends Model
{
    protected $fillable = [
        'tenant_id',
        // General
        'sueldo_base',
        'ip_local',
        // Impresión
        'impresora_nombre',
        'impresora_tipo',
        'papel_tamano',
        'papel_copias',
        // WhatsApp API
        'whatsapp_token',
        'whatsapp_phone_id',
        'whatsapp_enabled',
        // Facebook API
        'facebook_page_id',
        'facebook_access_token',
        'facebook_enabled',
        // Importación
        'ultima_importacion',
        'formato_importacion',
    ];

    protected $casts = [
        'sueldo_base' => 'decimal:2',
        'papel_copias' => 'integer',
        'whatsapp_enabled' => 'boolean',
        'facebook_enabled' => 'boolean',
        'ultima_importacion' => 'datetime',
    ];

    /**
     * Obtener el tenant de esta configuración.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtener o crear la configuración para un tenant.
     */
    public static function getOrCreateForTenant(int $tenantId): self
    {
        return self::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'sueldo_base' => 0,
                'impresora_tipo' => 'termica',
                'papel_tamano' => '80mm',
                'papel_copias' => 1,
                'formato_importacion' => 'excel',
            ]
        );
    }
}

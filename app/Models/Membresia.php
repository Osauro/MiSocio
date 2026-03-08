<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Membresia extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_suscripcion_id',
        'plan_nombre',
        'duracion_meses',
        'fecha_inicio',
        'fecha_fin',
        'monto',
        'estado_pago',
        'comprobante_url',
        'verificado_por',
        'verificado_at',
        'notas_verificacion',
        'stripe_id',
        'datos_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'duracion_meses' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'verificado_at' => 'datetime',
    ];

    /**
     * El método "booted" del modelo.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && currentTenantId()) {
                $builder->where('tenant_id', currentTenantId());
            }
        });
    }

    /**
     * Obtener el tenant al que pertenece la membresía.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function planSuscripcion(): BelongsTo
    {
        return $this->belongsTo(PlanSuscripcion::class, 'plan_suscripcion_id');
    }

    /**
     * Obtener el usuario que verificó el pago.
     */
    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_por');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Hospedaje extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'cliente_id',
        'acompanantes',
        'numero_folio',
        'estado',
        'fecha_entrada',
        'fecha_salida_estimada',
        'fecha_salida_real',
        'numero_personas',
        'observaciones',
        'efectivo',
        'online',
        'credito',
        'total',
    ];

    protected $casts = [
        'fecha_entrada'           => 'datetime',
        'fecha_salida_estimada'   => 'datetime',
        'fecha_salida_real'       => 'datetime',
        'numero_personas'         => 'integer',
        'efectivo'                => 'decimal:2',
        'online'                  => 'decimal:2',
        'credito'                 => 'decimal:2',
        'total'                   => 'decimal:2',
        'acompanantes'            => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && currentTenantId()) {
                $builder->where('hospedajes.tenant_id', currentTenantId());
            }
        });

        static::creating(function ($hospedaje) {
            if (empty($hospedaje->numero_folio)) {
                $ultimo = static::withoutGlobalScopes()
                    ->where('tenant_id', $hospedaje->tenant_id)
                    ->max('numero_folio');
                $hospedaje->numero_folio = ($ultimo ?? 0) + 1;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function habitaciones(): HasMany
    {
        return $this->hasMany(HospedajeHabitacion::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class TipoHabitacion extends Model
{
    protected $table = 'tipo_habitaciones';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'caracteristicas',
        'capacidad_maxima',
        'color',
        'imagen',
        'activo',
    ];

    protected $casts = [
        'capacidad_maxima' => 'integer',
        'activo' => 'boolean',
        'caracteristicas' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && currentTenantId()) {
                $builder->where('tipo_habitaciones.tenant_id', currentTenantId());
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class);
    }

    public function tarifas(): HasMany
    {
        return $this->hasMany(TarifaHabitacion::class);
    }

    public function tarifaPor(string $modalidad): ?TarifaHabitacion
    {
        return $this->tarifas->where('modalidad', $modalidad)->where('activo', true)->first();
    }
}

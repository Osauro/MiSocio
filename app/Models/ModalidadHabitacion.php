<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ModalidadHabitacion extends Model
{
    protected $table = 'modalidades_habitacion';

    protected $fillable = ['tenant_id', 'nombre', 'horas', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
        'horas'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && currentTenantId()) {
                $builder->where('modalidades_habitacion.tenant_id', currentTenantId());
            }
        });
    }

    // ─── Relaciones ────────────────────────────────────────────────
    public function tarifas()
    {
        return $this->hasMany(TarifaHabitacion::class, 'modalidad', 'nombre');
    }
}

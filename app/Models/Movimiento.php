<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Movimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'detalle',
        'ingreso',
        'egreso',
        'saldo',
    ];

    protected $casts = [
        'ingreso' => 'decimal:2',
        'egreso' => 'decimal:2',
        'saldo' => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (currentTenantId()) {
                $builder->where('tenant_id', currentTenantId());
            }
        });

        static::creating(function ($movimiento) {
            if (!$movimiento->tenant_id) {
                $movimiento->tenant_id = currentTenantId();
            }
            if (!$movimiento->user_id) {
                $movimiento->user_id = auth()->id();
            }

            // Calcular el saldo basado en el último movimiento
            $ultimoMovimiento = static::withoutGlobalScope('tenant')
                ->where('tenant_id', $movimiento->tenant_id)
                ->orderBy('id', 'desc')
                ->first();

            $saldoAnterior = $ultimoMovimiento ? $ultimoMovimiento->saldo : 0;
            $movimiento->saldo = $saldoAnterior + $movimiento->ingreso - $movimiento->egreso;
        });
    }

    /**
     * Relación con el usuario que registró el movimiento.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

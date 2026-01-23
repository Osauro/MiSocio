<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Compra extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'proveedor_id',
        'estado',
        'efectivo',
        'online',
        'credito',
    ];

    protected $casts = [
        'efectivo' => 'decimal:2',
        'online' => 'decimal:2',
        'credito' => 'decimal:2',
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
     * Obtener el tenant al que pertenece la compra.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtener el usuario que realizó la compra.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el proveedor (cliente) de la compra.
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'proveedor_id');
    }

    /**
     * Obtener los items de la compra.
     */
    public function compraItems(): HasMany
    {
        return $this->hasMany(CompraItem::class);
    }
}

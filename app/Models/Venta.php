<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'cliente_id',
        'estado',
        'efectivo',
        'online',
        'credito',
        'cambio',
    ];

    protected $casts = [
        'efectivo' => 'decimal:2',
        'online' => 'decimal:2',
        'credito' => 'decimal:2',
        'cambio' => 'decimal:2',
    ];

    /**
     * Obtener el tenant al que pertenece la venta.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtener el usuario que realizó la venta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el cliente de la venta.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Obtener los items de la venta.
     */
    public function ventaItems(): HasMany
    {
        return $this->hasMany(VentaItem::class);
    }
}

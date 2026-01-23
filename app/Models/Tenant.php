<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'theme_number',
        'domain',
        'subscription_type',
        'amount',
        'bill_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bill_date' => 'date',
    ];

    /**
     * Obtener los usuarios del tenant.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    /**
     * Obtener los productos del tenant.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Obtener las categorías del tenant.
     */
    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }

    /**
     * Obtener los clientes del tenant.
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    /**
     * Obtener las ventas del tenant.
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Obtener las membresías del tenant.
     */
    public function membresias(): HasMany
    {
        return $this->hasMany(Membresia::class);
    }
}

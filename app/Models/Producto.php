<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Producto extends Model
{
    protected $fillable = [
        'tenant_id',
        'categoria_id',
        'nombre',
        'codigo',
        'imagen',
        'medida',
        'cantidad',
        'precio_de_compra',
        'precio_por_mayor',
        'precio_por_menor',
        'stock',
    ];

    protected $casts = [
        'precio_de_compra' => 'decimal:2',
        'precio_por_mayor' => 'decimal:2',
        'precio_por_menor' => 'decimal:2',
        'stock' => 'integer',
        'cantidad' => 'integer',
    ];

    /**
     * El método "booted" del modelo.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && Auth::user()->tenant_id) {
                $builder->where('tenant_id', Auth::user()->tenant_id);
            }
        });
    }

    /**
     * Obtener el tenant al que pertenece el producto.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtener la categoría del producto.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Obtener los items de venta del producto.
     */
    public function ventaItems(): HasMany
    {
        return $this->hasMany(VentaItem::class);
    }
}

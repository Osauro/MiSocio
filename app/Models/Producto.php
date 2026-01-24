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
        'control',
        'vencidos',
    ];

    protected $casts = [
        'precio_de_compra' => 'decimal:2',
        'precio_por_mayor' => 'decimal:2',
        'precio_por_menor' => 'decimal:2',
        'stock' => 'integer',
        'cantidad' => 'integer',
        'control' => 'boolean',
        'vencidos' => 'integer',
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

    /**
     * Relación con tags (many-to-many).
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'producto_tag')->withTimestamps();
    }

    /**
     * Sincronizar tags del producto desde un string separado por comas.
     * Normaliza y crea tags si no existen.
     */
    public function syncTagsFromString(?string $tagsString): void
    {
        if (empty($tagsString)) {
            $this->tags()->detach();
            return;
        }

        $tagNames = array_map('trim', explode(',', $tagsString));
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            if (!empty($tagName)) {
                $tag = Tag::findOrCreateByName($tagName);
                $tagIds[] = $tag->id;
            }
        }

        $this->tags()->sync($tagIds);
    }

    /**
     * Obtener los tags como string separado por comas.
     */
    public function getTagsStringAttribute(): string
    {
        return $this->tags->pluck('nombre')->join(', ');
    }

    /**
     * Obtiene el stock formateado en cajas y unidades
     */
    public function getStockFormateadoAttribute(): string
    {
        if ($this->stock == 0) {
            return '0';
        }

        if ($this->cantidad <= 1) {
            // Si no hay conversión, mostrar solo el stock
            return number_format($this->stock, 0);
        }

        $cajas = floor($this->stock / $this->cantidad);
        $unidades = $this->stock % $this->cantidad;

        // Abreviatura de la medida (primera letra en minúscula)
        $medidaAbrev = strtolower(substr($this->medida, 0, 1));

        if ($cajas > 0 && $unidades > 0) {
            return "{$cajas}{$medidaAbrev} - {$unidades}u";
        } elseif ($cajas > 0) {
            return "{$cajas}{$medidaAbrev}";
        } else {
            return "{$unidades}u";
        }
    }

    /**
     * Obtiene la medida con primera letra en mayúscula
     */
    public function getMedidaFormateadaAttribute(): string
    {
        return ucfirst(strtolower($this->medida));
    }
}

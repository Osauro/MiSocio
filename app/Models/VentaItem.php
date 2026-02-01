<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaItem extends Model
{
    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Obtener la venta a la que pertenece el item.
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Obtener el producto del item.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Obtiene la cantidad formateada en cajas y unidades
     */
    public function getCantidadFormateadaAttribute(): string
    {
        if ($this->cantidad == 0 || !$this->producto) {
            return '0';
        }

        $cantidadProducto = $this->producto->cantidad ?? 1;
        $medida = $this->producto->medida ?? 'u';

        if ($cantidadProducto <= 1) {
            // Si no hay conversión, mostrar solo la cantidad con unidad
            return intval($this->cantidad) . strtolower(substr($medida, 0, 1));
        }

        $cajas = floor($this->cantidad / $cantidadProducto);
        $unidades = $this->cantidad % $cantidadProducto;

        // Abreviatura de la medida (primera letra en minúscula)
        $medidaAbrev = strtolower(substr($medida, 0, 1));

        if ($cajas > 0 && $unidades > 0) {
            return "{$cajas}{$medidaAbrev} - {$unidades}u";
        } elseif ($cajas > 0) {
            return "{$cajas}{$medidaAbrev}";
        } else {
            return "{$unidades}u";
        }
    }
}

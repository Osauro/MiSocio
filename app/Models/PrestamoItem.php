<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrestamoItem extends Model
{
    protected $fillable = [
        'prestamo_id',
        'producto_id',
        'cantidad',
        'cantidad_devuelta',
        'precio_deposito',
        'subtotal_deposito',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'cantidad_devuelta' => 'integer',
        'precio_deposito' => 'decimal:2',
        'subtotal_deposito' => 'decimal:2',
    ];

    /**
     * Obtener el préstamo al que pertenece el item.
     */
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
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

    /**
     * Obtiene la cantidad devuelta formateada en cajas y unidades
     */
    public function getCantidadDevueltaFormateadaAttribute(): string
    {
        if ($this->cantidad_devuelta == 0 || !$this->producto) {
            return '0';
        }

        $cantidadProducto = $this->producto->cantidad ?? 1;
        $medida = $this->producto->medida ?? 'u';

        if ($cantidadProducto <= 1) {
            return intval($this->cantidad_devuelta) . strtolower(substr($medida, 0, 1));
        }

        $cajas = floor($this->cantidad_devuelta / $cantidadProducto);
        $unidades = $this->cantidad_devuelta % $cantidadProducto;
        $medidaAbrev = strtolower(substr($medida, 0, 1));

        if ($cajas > 0 && $unidades > 0) {
            return "{$cajas}{$medidaAbrev} - {$unidades}u";
        } elseif ($cajas > 0) {
            return "{$cajas}{$medidaAbrev}";
        } else {
            return "{$unidades}u";
        }
    }

    /**
     * Obtiene la cantidad pendiente de devolución
     */
    public function getCantidadPendienteAttribute(): int
    {
        return $this->cantidad - $this->cantidad_devuelta;
    }

    /**
     * Obtiene la cantidad pendiente formateada
     */
    public function getCantidadPendienteFormateadaAttribute(): string
    {
        $pendiente = $this->cantidad_pendiente;

        if ($pendiente == 0 || !$this->producto) {
            return '0';
        }

        $cantidadProducto = $this->producto->cantidad ?? 1;
        $medida = $this->producto->medida ?? 'u';

        if ($cantidadProducto <= 1) {
            return intval($pendiente) . strtolower(substr($medida, 0, 1));
        }

        $cajas = floor($pendiente / $cantidadProducto);
        $unidades = $pendiente % $cantidadProducto;
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

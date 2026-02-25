<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_compra',
        'precio',
        'beneficio',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_compra' => 'decimal:2',
        'precio' => 'decimal:2',
        'beneficio' => 'decimal:2',
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

    /**
     * Obtiene el precio de compra por paquete/medida completa
     * Si el producto tiene cantidad > 1, muestra el precio del paquete
     * Si es unitario, muestra el precio unitario
     */
    public function getPrecioCompraPorPaqueteAttribute(): float
    {
        if (!$this->producto) {
            return $this->precio_compra;
        }

        $cantidadPorMedida = $this->producto->cantidad ?? 1;

        // Si la cantidad es <= 1, es unitario, retornar el precio tal cual
        if ($cantidadPorMedida <= 1) {
            return $this->precio_compra;
        }

        // Si hay paquetes, calcular el precio por paquete
        // El precio está guardado como el precio del paquete completo
        return $this->precio_compra;
    }

    /**
     * Obtiene el precio de venta por paquete/medida completa
     * Si el producto tiene cantidad > 1, muestra el precio del paquete
     * Si es unitario, muestra el precio unitario
     */
    public function getPrecioPorPaqueteAttribute(): float
    {
        if (!$this->producto) {
            return $this->precio;
        }

        $cantidadPorMedida = $this->producto->cantidad ?? 1;

        // Si la cantidad es <= 1, es unitario, retornar el precio tal cual
        if ($cantidadPorMedida <= 1) {
            return $this->precio;
        }

        // Si hay paquetes, el precio ya está guardado como precio por paquete
        return $this->precio;
    }

    /**
     * Obtiene el beneficio por paquete/medida completa
     */
    public function getBeneficioPorPaqueteAttribute(): float
    {
        if (!$this->producto) {
            return $this->beneficio;
        }

        $cantidadPorMedida = $this->producto->cantidad ?? 1;

        if ($cantidadPorMedida <= 1) {
            return $this->beneficio;
        }

        // Calcular beneficio por paquete
        $totalPaquetes = floor($this->cantidad / $cantidadPorMedida);
        if ($totalPaquetes <= 0) {
            return 0;
        }

        return $this->beneficio / $totalPaquetes;
    }
}

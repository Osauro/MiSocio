<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Kardex extends Model
{
    protected $table = 'kardex';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'producto_id',
        'entrada',
        'salida',
        'anterior',
        'saldo',
        'precio',
        'total',
        'obs'
    ];

    protected $casts = [
        'entrada' => 'integer',
        'salida' => 'integer',
        'anterior' => 'integer',
        'saldo' => 'integer',
        'precio' => 'decimal:2',
        'total' => 'decimal:2',
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

    // Relaciones
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Formatea una cantidad en cajas y unidades según el producto
     */
    private function formatearCantidad($cantidad): string
    {
        if (!$this->producto || $cantidad == 0) {
            return '0';
        }

        $cantidadPorMedida = $this->producto->cantidad;

        if ($cantidadPorMedida <= 1) {
            // Si no hay conversión, mostrar solo la cantidad
            return number_format($cantidad, 0);
        }

        $cajas = floor($cantidad / $cantidadPorMedida);
        $unidades = $cantidad % $cantidadPorMedida;

        // Abreviatura de la medida (primera letra en minúscula)
        $medidaAbrev = strtolower(substr($this->producto->medida, 0, 1));

        if ($cajas > 0 && $unidades > 0) {
            return "{$cajas}{$medidaAbrev} - {$unidades}u";
        } elseif ($cajas > 0) {
            return "{$cajas}{$medidaAbrev}";
        } else {
            return "{$unidades}u";
        }
    }

    /**
     * Obtiene el anterior formateado
     */
    public function getAnteriorFormateadoAttribute(): string
    {
        return $this->formatearCantidad($this->anterior);
    }

    /**
     * Obtiene la entrada formateada
     */
    public function getEntradaFormateadoAttribute(): string
    {
        return $this->formatearCantidad($this->entrada);
    }

    /**
     * Obtiene la salida formateada
     */
    public function getSalidaFormateadoAttribute(): string
    {
        return $this->formatearCantidad($this->salida);
    }

    /**
     * Obtiene la entrada/salida formateada
     */
    public function getMovimientoFormateadoAttribute(): string
    {
        if ($this->entrada > 0) {
            return $this->formatearCantidad($this->entrada);
        } elseif ($this->salida > 0) {
            return $this->formatearCantidad($this->salida);
        }
        return '-';
    }

    /**
     * Obtiene el saldo formateado
     */
    public function getSaldoFormateadoAttribute(): string
    {
        return $this->formatearCantidad($this->saldo);
    }
}

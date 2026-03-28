<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaHabitacion extends Model
{
    protected $table = 'tarifas_habitacion';

    protected $fillable = [
        'tenant_id',
        'tipo_habitacion_id',
        'modalidad',
        'precio',
        'precio_por_persona',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_por_persona' => 'boolean',
        'activo' => 'boolean',
    ];

    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class);
    }

    public function calcularPrecio(int $personas = 1): float
    {
        $base = (float) $this->precio;
        return $this->precio_por_persona ? $base * $personas : $base;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospedajeHabitacion extends Model
{
    public $timestamps = false;

    protected $table = 'hospedaje_habitaciones';

    protected $fillable = [
        'hospedaje_id',
        'habitacion_id',
        'tarifa_id',
        'modalidad',
        'unidades',
        'numero_personas',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'unidades'        => 'decimal:2',
        'numero_personas' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function hospedaje(): BelongsTo
    {
        return $this->belongsTo(Hospedaje::class);
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function tarifa(): BelongsTo
    {
        return $this->belongsTo(TarifaHabitacion::class, 'tarifa_id');
    }
}

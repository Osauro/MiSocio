<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Habitacion extends Model
{
    use SoftDeletes;

    protected $table = 'habitaciones';

    protected $fillable = [
        'tenant_id',
        'tipo_habitacion_id',
        'numero',
        'piso',
        'estado',
        'descripcion',
    ];

    protected $casts = [
        'piso' => 'integer',
    ];

    // Ciclo de estados al hacer clic
    public const CICLO_ESTADOS = [
        'disponible'   => 'ocupada',
        'ocupada'      => 'limpieza',
        'limpieza'     => 'disponible',
        'mantenimiento'=> 'disponible',
    ];

    public const COLORES_ESTADO = [
        'disponible'    => '#198754', // verde
        'ocupada'       => '#dc3545', // rojo
        'limpieza'      => '#fd7e14', // naranja
        'mantenimiento' => '#6c757d', // gris
    ];

    public const ICONOS_ESTADO = [
        'disponible'    => 'fa-check-circle',
        'ocupada'       => 'fa-person-digging',
        'limpieza'      => 'fa-broom',
        'mantenimiento' => 'fa-tools',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && currentTenantId()) {
                $builder->where('habitaciones.tenant_id', currentTenantId());
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class);
    }

    public function hospedajeHabitaciones(): HasMany
    {
        return $this->hasMany(HospedajeHabitacion::class);
    }

    public function hospedajeActivo()
    {
        return $this->hasOneThrough(Hospedaje::class, HospedajeHabitacion::class, 'habitacion_id', 'id', 'id', 'hospedaje_id')
            ->where('hospedajes.estado', 'activo');
    }

    public function getColorEstadoAttribute(): string
    {
        return self::COLORES_ESTADO[$this->estado] ?? '#6c757d';
    }

    public function getIconoEstadoAttribute(): string
    {
        return self::ICONOS_ESTADO[$this->estado] ?? 'fa-question-circle';
    }

    public function getEstadoSiguienteAttribute(): string
    {
        return self::CICLO_ESTADOS[$this->estado] ?? 'disponible';
    }
}

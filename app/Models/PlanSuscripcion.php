<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanSuscripcion extends Model
{
    protected $table = 'planes_suscripcion';

    protected $fillable = [
        'nombre',
        'slug',
        'duracion_meses',
        'precio',
        'descripcion',
        'caracteristicas',
        'activo',
        'orden',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'duracion_meses' => 'integer',
        'activo' => 'boolean',
        'orden' => 'integer',
        'caracteristicas' => 'array',
    ];

    /**
     * Relación con tenants que usan este plan.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_suscripcion_id');
    }

    /**
     * Scope para obtener solo planes activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para ordenar por posición.
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    /**
     * Obtener el nombre formateado con la duración.
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} - Bs. {$this->precio}";
    }

    /**
     * Obtener la descripción de la duración.
     */
    public function getDuracionTextoAttribute(): string
    {
        $textos = [
            1 => '1 mes',
            3 => '3 meses',
            6 => '6 meses',
            12 => '12 meses',
        ];

        return $textos[$this->duracion_meses] ?? "{$this->duracion_meses} meses";
    }
}

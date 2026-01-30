<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Venta extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'cliente_id',
        'numero_folio',
        'estado',
        'efectivo',
        'online',
        'credito',
        'cambio',
    ];

    protected $casts = [
        'efectivo' => 'decimal:2',
        'online' => 'decimal:2',
        'credito' => 'decimal:2',
        'cambio' => 'decimal:2',
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

        // Asignar ID y número de folio automáticamente buscando huecos
        static::creating(function ($venta) {
            // Buscar hueco en IDs
            if (empty($venta->id)) {
                $venta->id = static::buscarIdDisponible();
            }

            // Buscar hueco en folios del tenant
            if (empty($venta->numero_folio)) {
                $venta->numero_folio = static::buscarFolioDisponible($venta->tenant_id);
            }
        });
    }

    /**
     * Buscar el primer ID disponible (reutilizando huecos).
     */
    protected static function buscarIdDisponible()
    {
        $idsExistentes = static::withoutGlobalScopes()
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (empty($idsExistentes)) {
            return 1;
        }

        $idEsperado = 1;
        foreach ($idsExistentes as $id) {
            if ($id != $idEsperado) {
                return $idEsperado;
            }
            $idEsperado++;
        }

        return max($idsExistentes) + 1;
    }

    /**
     * Buscar el primer folio disponible para el tenant (reutilizando huecos).
     */
    protected static function buscarFolioDisponible($tenantId)
    {
        $foliosExistentes = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('numero_folio')
            ->pluck('numero_folio')
            ->toArray();

        if (empty($foliosExistentes)) {
            return 1;
        }

        $folioEsperado = 1;
        foreach ($foliosExistentes as $folio) {
            if ($folio != $folioEsperado) {
                return $folioEsperado;
            }
            $folioEsperado++;
        }

        return max($foliosExistentes) + 1;
    }

    /**
     * Obtener el tenant al que pertenece la venta.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtener el usuario que realizó la venta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el cliente de la venta.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Obtener los items de la venta.
     */
    public function ventaItems(): HasMany
    {
        return $this->hasMany(VentaItem::class);
    }
}

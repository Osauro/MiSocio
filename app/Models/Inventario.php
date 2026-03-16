<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Inventario extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'numero_folio',
        'estado',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && currentTenantId()) {
                $builder->where('tenant_id', currentTenantId());
            }
        });

        static::creating(function ($inventario) {
            if (empty($inventario->numero_folio)) {
                $inventario->numero_folio = static::buscarFolioDisponible($inventario->tenant_id);
            }
        });
    }

    protected static function buscarFolioDisponible(int $tenantId): int
    {
        $ultimo = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->max('numero_folio');

        return ($ultimo ?? 0) + 1;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventarioItem::class);
    }
}

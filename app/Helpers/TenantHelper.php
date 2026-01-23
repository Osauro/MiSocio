<?php

namespace App\Helpers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class TenantHelper
{
    /**
     * Obtener el tenant actual de la sesión.
     */
    public static function current(): ?Tenant
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return $user->currentTenant();
    }

    /**
     * Obtener el ID del tenant actual.
     */
    public static function currentId(): ?int
    {
        $tenant = self::current();
        return $tenant ? $tenant->id : null;
    }

    /**
     * Cambiar el tenant actual.
     */
    public static function switch(int $tenantId): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->switchTenant($tenantId);
    }

    /**
     * Verificar si hay un tenant actual.
     */
    public static function hasCurrent(): bool
    {
        return self::current() !== null;
    }
}

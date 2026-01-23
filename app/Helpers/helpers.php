<?php

use App\Helpers\TenantHelper;

if (!function_exists('currentTenant')) {
    /**
     * Obtener el tenant actual.
     */
    function currentTenant(): ?\App\Models\Tenant
    {
        return TenantHelper::current();
    }
}

if (!function_exists('currentTenantId')) {
    /**
     * Obtener el ID del tenant actual.
     */
    function currentTenantId(): ?int
    {
        return TenantHelper::currentId();
    }
}

if (!function_exists('switchTenant')) {
    /**
     * Cambiar el tenant actual.
     */
    function switchTenant(int $tenantId): bool
    {
        return TenantHelper::switch($tenantId);
    }
}

if (!function_exists('hasTenant')) {
    /**
     * Verificar si hay un tenant actual.
     */
    function hasTenant(): bool
    {
        return TenantHelper::hasCurrent();
    }
}

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

if (!function_exists('getThemeColor')) {
    /**
     * Obtener el color del tema basado en el theme_number.
     */
    function getThemeColor(?int $themeNumber = null): string
    {
        $colors = [
            1 => '#308e87', // Color 1 - Default verde
            2 => '#57375d', // Color 2 - Púrpura
            3 => '#0766ad', // Color 3 - Azul
            4 => '#025464', // Color 4 - Verde azulado
            5 => '#884a39', // Color 5 - Marrón
            6 => '#0c356a', // Color 6 - Azul oscuro
        ];

        if ($themeNumber === null) {
            $themeNumber = currentTenant()?->theme_number ?? 1;
        }

        return $colors[$themeNumber] ?? $colors[1];
    }
}

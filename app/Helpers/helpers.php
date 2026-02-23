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
            1 => '#308e87', // Color 1 - Verde azulado
            2 => '#57375d', // Color 2 - Púrpura
            3 => '#0766ad', // Color 3 - Azul
            4 => '#025464', // Color 4 - Verde azulado oscuro
            5 => '#884a39', // Color 5 - Marrón
            6 => '#0c356a', // Color 6 - Azul oscuro
            7 => '#c7253e', // Color 7 - Rojo
            8 => '#16423c', // Color 8 - Verde bosque
            9 => '#5c469c', // Color 9 - Púrpura oscuro
            10 => '#1a1a40', // Color 10 - Azul marino
        ];

        if ($themeNumber === null) {
            $themeNumber = currentTenant()?->theme_number ?? 1;
        }

        return $colors[$themeNumber] ?? $colors[1];
    }
}

// ============================================
// HELPERS PARA GESTIÓN DE ROLES
// ============================================

if (!function_exists('currentUserRole')) {
    /**
     * Obtener el rol del usuario actual en el tenant activo.
     *
     * @return string|null 'landlord', 'tenant', 'user' o null
     */
    function currentUserRole(): ?string
    {
        return auth()->user()?->roleInCurrentTenant();
    }
}

if (!function_exists('isLandlord')) {
    /**
     * Verificar si el usuario actual es Super Admin (Landlord del sistema).
     * Este es un rol global, no específico de un tenant.
     *
     * @return bool
     */
    function isLandlord(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Alias de isLandlord() - Verificar si el usuario es Super Admin del sistema.
     *
     * @return bool
     */
    function isSuperAdmin(): bool
    {
        return isLandlord();
    }
}

if (!function_exists('isTenantAdmin')) {
    /**
     * Verificar si el usuario actual es Tenant (administrador) en el tenant activo.
     *
     * @return bool
     */
    function isTenantAdmin(): bool
    {
        return currentUserRole() === 'tenant';
    }
}

if (!function_exists('isUser')) {
    /**
     * Verificar si el usuario actual es User (operador) en el tenant activo.
     *
     * @return bool
     */
    function isUser(): bool
    {
        return currentUserRole() === 'user';
    }
}

if (!function_exists('canManageTenant')) {
    /**
     * Verificar si el usuario puede administrar el tenant actual.
     * Tienen acceso completo a: productos, categorías, usuarios, clientes, configuraciones.
     *
     * Super Admins pueden administrar cualquier tenant.
     * Usuarios con rol 'tenant' pueden administrar su tenant asignado.
     *
     * @return bool
     */
    function canManageTenant(): bool
    {
        return auth()->user()?->canManageCurrentTenant() ?? false;
    }
}

if (!function_exists('hasRole')) {
    /**
     * Verificar si el usuario tiene un rol específico o está en una lista de roles.
     *
     * @param string|array $roles Rol único o array de roles permitidos
     * @return bool
     */
    function hasRole(string|array $roles): bool
    {
        $currentRole = currentUserRole();

        if (is_array($roles)) {
            return in_array($currentRole, $roles);
        }

        return $currentRole === $roles;
    }
}

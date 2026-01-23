<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageTenant
{
    /**
     * Verificar que el usuario pueda administrar el tenant (Landlord o Tenant Admin).
     *
     * Pueden administrar:
     * - Productos y categorías
     * - Usuarios del tenant
     * - Clientes
     * - Compras
     * - Configuraciones del tenant
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!canManageTenant()) {
            abort(403, 'Acceso denegado. No tienes permisos de administrador.');
        }

        return $next($request);
    }
}

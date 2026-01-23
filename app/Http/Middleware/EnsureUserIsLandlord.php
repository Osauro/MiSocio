<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsLandlord
{
    /**
     * Verificar que el usuario sea Landlord en el tenant actual.
     *
     * El Landlord tiene acceso completo al sistema:
     * - Gestión de pagos y suscripciones
     * - Administración de tenants
     * - Todas las funcionalidades del tenant
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!isLandlord()) {
            abort(403, 'Acceso denegado. Solo Landlords pueden acceder a esta sección.');
        }

        return $next($request);
    }
}

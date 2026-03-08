<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActive
{
    /**
     * Verifica que el tenant actual no tenga la suscripción vencida.
     *
     * - Si está vencido y el usuario es TenantAdmin → redirige a suscripcion con aviso.
     * - Si está vencido y el usuario es operador (User) → redirige a pantalla de bloqueo.
     * - Rutas exentas: suscripcion, tenant.expirado (para no generar loop de redirección).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rutas exentas: renovación y página de bloqueo
        if ($request->routeIs('suscripcion', 'tenant.expirado')) {
            return $next($request);
        }

        $tenant = currentTenant();

        if (!$tenant) {
            return $next($request);
        }

        // Determinar si el tenant está vencido:
        // bill_date definida y ya pasó (isPast incluye el mismo día hacia atrás)
        $vencido = $tenant->bill_date && $tenant->bill_date->startOfDay()->isPast()
                   && $tenant->bill_date->startOfDay()->lt(now()->startOfDay());

        if ($vencido) {
            if (isTenantAdmin() || isLandlord()) {
                return redirect()->route('suscripcion')
                    ->with('warning', '⚠️ Tu suscripción venció el ' . $tenant->bill_date->format('d/m/Y') . '. Renueva tu plan para continuar usando el sistema.');
            }

            // Operador sin privilegios de renovación
            return redirect()->route('tenant.expirado');
        }

        return $next($request);
    }
}

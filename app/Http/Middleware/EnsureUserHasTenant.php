<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Permitir acceso a la página de suscripción sin tenant
        if ($request->routeIs('suscripcion.create')) {
            return $next($request);
        }

        // Verificar que el usuario tenga al menos un tenant asignado usando DB directo
        $tieneTenant = DB::table('tenant_user')
            ->where('user_id', $user->id)
            ->exists();

        if (!$tieneTenant) {
            // Si no tiene tenants, redirigir a crear tienda (aplica a TODOS los usuarios, incluido super admin)
            return redirect()->route('suscripcion.create')->with('info', 'Necesitas crear tu primera tienda para continuar.');
        }

        // Asegurar que haya un tenant actual en la sesión
        if (!$user->currentTenant()) {
            // Si no hay tenant actual, intentar establecer el primero disponible
            $firstTenantId = DB::table('tenant_user')
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->value('tenant_id');

            if ($firstTenantId) {
                session(['current_tenant_id' => $firstTenantId]);
            } else {
                Auth::logout();
                return redirect()->route('login')->with('error', 'No se pudo establecer una tienda activa.');
            }
        }

        // Redirigir usuarios (operadores) directamente a ventas si intentan acceder al dashboard
        if ($request->routeIs('home') && isUser()) {
            return redirect()->route('ventas');
        }

        return $next($request);
    }
}

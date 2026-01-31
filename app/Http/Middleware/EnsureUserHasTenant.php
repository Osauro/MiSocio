<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Verificar que el usuario tenga al menos un tenant asignado
        if ($user->tenants()->wherePivot('is_active', true)->count() === 0) {
            // Si no tiene tenants, cerrar sesión y redirigir al login con mensaje
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tu cuenta no está asociada a ninguna tienda. Contacta al administrador.');
        }

        // Asegurar que haya un tenant actual en la sesión
        if (!$user->currentTenant()) {
            // Si no hay tenant actual, intentar establecer el primero disponible
            $firstTenant = $user->tenants()->wherePivot('is_active', true)->first();
            if ($firstTenant) {
                session(['current_tenant_id' => $firstTenant->id]);
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

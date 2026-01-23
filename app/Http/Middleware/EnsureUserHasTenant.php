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

        // Verificar que el usuario tenga un tenant_id asignado
        if (!Auth::user()->tenant_id) {
            // Si no tiene tenant, cerrar sesión y redirigir al login con mensaje
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tu cuenta no está asociada a ninguna tienda. Contacta al administrador.');
        }

        return $next($request);
    }
}

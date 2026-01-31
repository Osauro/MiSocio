<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Si es Super Admin, redirigir a la vista de landlord
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.home');
        }

        // Para usuarios normales, verificar que tengan tenants asignados
        if ($user->tenants()->wherePivot('is_active', true)->count() > 0) {
            // Establecer el primer tenant activo si no hay uno en sesión
            if (!$user->currentTenant()) {
                $firstTenant = $user->tenants()->wherePivot('is_active', true)->first();
                if ($firstTenant) {
                    session(['current_tenant_id' => $firstTenant->id]);
                }
            }

            // Redirigir según el rol del usuario en el tenant actual
            $role = currentUserRole();

            if ($role === 'user') {
                // Operadores van directo a ventas
                return redirect()->route('ventas');
            } elseif ($role === 'tenant') {
                // Administradores del tenant van al dashboard
                return redirect()->route('home');
            }
        }

        // Fallback por defecto
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

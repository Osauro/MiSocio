<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait RequiresTenant
{
    /**
     * Inicializar la protección de tenant.
     */
    public function bootRequiresTenant(): void
    {
        // Verificar que el usuario esté autenticado y tenga un tenant
        if (!Auth::check() || !Auth::user()->tenant_id) {
            abort(403, 'No tienes acceso a este recurso. Necesitas estar asociado a una tienda.');
        }
    }
}

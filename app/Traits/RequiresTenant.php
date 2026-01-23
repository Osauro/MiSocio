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
        // Verificar que el usuario esté autenticado y tenga un tenant actual
        if (!Auth::check() || !currentTenant()) {
            abort(403, 'No tienes acceso a este recurso. Necesitas estar asociado a una tienda.');
        }
    }

    /**
     * Obtener el ID del tenant actual.
     */
    protected function getTenantId(): ?int
    {
        return currentTenantId();
    }
}

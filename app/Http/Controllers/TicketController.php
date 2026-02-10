<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\TenantConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function venta($ventaId)
    {
        $venta = Venta::with(['cliente', 'user', 'ventaItems.producto' => function ($query) {
            $query->withTrashed();
        }])->findOrFail($ventaId);

        // Verificar que el usuario tenga acceso
        if (!Auth::check()) {
            abort(403);
        }

        $config = TenantConfig::getOrCreateForTenant(currentTenantId());

        $pdf = Pdf::loadView('pdf.ticket-venta', compact('venta', 'config'));

        // Configurar tamaño del papel: 80mm de ancho, alto auto
        $pdf->setPaper([0, 0, 226.77, 850], 'portrait'); // 80mm = 226.77 puntos

        return $pdf->stream('ticket-venta-' . $venta->numero_folio . '.pdf');
    }
}

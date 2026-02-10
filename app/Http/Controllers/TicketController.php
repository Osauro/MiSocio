<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\TenantConfig;
use App\Services\PrinterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Cargar venta con relaciones necesarias
     */
    private function cargarVenta($ventaId)
    {
        if (!Auth::check()) abort(403);

        return Venta::with(['cliente', 'user', 'ventaItems.producto' => function ($query) {
            $query->withTrashed();
        }])->findOrFail($ventaId);
    }

    /**
     * Ticket PDF (para descargar/archivar)
     */
    public function venta($ventaId)
    {
        $venta = $this->cargarVenta($ventaId);
        $config = TenantConfig::getOrCreateForTenant(currentTenantId());

        $pdf = Pdf::loadView('pdf.ticket-venta', compact('venta', 'config'));
        $pdf->setPaper([0, 0, 226.77, 850], 'portrait');

        return $pdf->stream('ticket-venta-' . $venta->numero_folio . '.pdf');
    }

    /**
     * Ticket HTML para imprimir desde el navegador.
     * Se abre en nueva pestaña y ejecuta window.print() automáticamente.
     * El tamaño del papel se ajusta con @page { size: 80mm auto }.
     */
    public function ventaHtml($ventaId)
    {
        $venta = $this->cargarVenta($ventaId);
        $config = TenantConfig::getOrCreateForTenant(currentTenantId());

        return view('ticket.venta-print', compact('venta', 'config'));
    }

    /**
     * Ticket ESC/POS raw (datos binarios generados con mike42/escpos-php).
     * Se devuelve como binario para enviar directamente a la impresora.
     * El papel se ajusta automáticamente porque ESC/POS es rollo continuo.
     */
    public function ventaEscpos($ventaId)
    {
        $venta = $this->cargarVenta($ventaId);
        $config = TenantConfig::getOrCreateForTenant(currentTenantId());

        try {
            $rawData = PrinterService::generarRawTicketVenta($venta, $config);

            return response($rawData)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Length', strlen($rawData))
                ->header('Cache-Control', 'no-cache, no-store');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar ticket ESC/POS: ' . $e->getMessage()
            ], 500);
        }
    }
}

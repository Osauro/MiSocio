<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Prestamo;
use App\Models\Inventario;
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

        // Ajustar tamaño del papel según configuración
        // 58mm = 164.41 puntos, 80mm = 226.77 puntos
        $paperWidth = ($config->papel_tamano === '58mm') ? 164.41 : 226.77;
        $pdf->setPaper([0, 0, $paperWidth, 850], 'portrait');

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

    /**
     * Cargar préstamo con relaciones necesarias
     */
    private function cargarPrestamo($prestamoId)
    {
        if (!Auth::check()) abort(403);

        return Prestamo::with(['cliente', 'user', 'prestamoItems.producto' => function ($query) {
            $query->withTrashed();
        }])->findOrFail($prestamoId);
    }

    /**
     * Ticket PDF de préstamo (para descargar/imprimir)
     */
    public function prestamo($prestamoId)
    {
        $prestamo = $this->cargarPrestamo($prestamoId);
        $config = TenantConfig::getOrCreateForTenant(currentTenantId());

        $pdf = Pdf::loadView('pdf.prestamo', compact('prestamo', 'config'));

        // Ajustar tamaño del papel
        $paperWidth = ($config->papel_tamano === '58mm') ? 164.41 : 226.77;
        $pdf->setPaper([0, 0, $paperWidth, 850], 'portrait');

        return $pdf->stream('ticket-prestamo-' . $prestamo->numero_folio . '.pdf');
    }

    /**
     * Ticket HTML de préstamo para imprimir desde el navegador
     */
    public function prestamoHtml($prestamoId)
    {
        $prestamo = $this->cargarPrestamo($prestamoId);
        $config = TenantConfig::getOrCreateForTenant(currentTenantId());

        return view('ticket.prestamo-print', compact('prestamo', 'config'));
    }

    /**
     * PDF de inventario (A4)
     */
    public function inventario($inventarioId)
    {
        if (!Auth::check()) abort(403);

        $inventario = Inventario::with(['user', 'items.producto' => function ($q) {
            $q->withTrashed();
        }])->findOrFail($inventarioId);

        $config = TenantConfig::getOrCreateForTenant(currentTenantId());

        $pdf = Pdf::loadView('pdf.inventario', compact('inventario', 'config'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('inventario-' . str_pad($inventario->numero_folio, 6, '0', STR_PAD_LEFT) . '.pdf');
    }
}

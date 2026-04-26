<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Prestamo;
use App\Models\Inventario;
use App\Models\TenantConfig;
use App\Services\EscposPrinterService;
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
     * Envía el ticket de venta al Print Agent local (ESC/POS).
     * El agente recibe las secciones encriptadas y se encarga de enviarlas
     * a la impresora física. Devuelve JSON con el resultado.
     */
    public function ventaEscpos($ventaId)
    {
        $venta  = $this->cargarVenta($ventaId);
        $config = TenantConfig::getOrCreateForTenant(currentTenantId());

        /** @var EscposPrinterService $svc */
        $svc  = app(EscposPrinterService::class);
        $key  = $svc->getSecretKey();
        $cols = ($config->papel_tamano === '58mm') ? 32 : 48;

        // ── Nombre de la impresora ──────────────────────────────────────
        $printerName = $config->impresora_nombre ?? 'Fadi';

        // ── Header ─────────────────────────────────────────────────────
        $headerData = [
            'store'   => $config->nombre_tienda ?? 'MI TIENDA',
            'address' => $config->direccion      ?? '',
            'phone'   => $config->telefono       ?? '',
            'nit'     => $config->nit            ?? '',
            'title'   => 'VENTA #' . $venta->numero_folio,
            'date'    => $venta->created_at->format('d/m/Y H:i:s'),
            'user'    => $venta->user->name  ?? '',
            'client'  => $venta->cliente->nombre ?? '',
        ];

        // ── Items ──────────────────────────────────────────────────────
        $items = $venta->ventaItems->map(function ($item) {
            $producto = $item->producto;
            $nombre   = $producto ? $producto->nombre : ($item->nombre ?? 'Producto');
            $cant     = $item->cantidad . ($item->medida ? ' ' . $item->medida : '');

            return [
                'nombre'   => $nombre,
                'cantidad' => $cant,
                'precio'   => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
            ];
        })->toArray();

        // ── Totales ────────────────────────────────────────────────────
        $totalesData = array_filter([
            'TOTAL'    => (float) $venta->total,
            'efectivo' => (float) ($venta->efectivo ?? 0),
            'online'   => (float) ($venta->online   ?? 0),
            'credito'  => (float) ($venta->credito  ?? 0),
            'cambio'   => (float) ($venta->cambio   ?? 0),
        ], fn($v) => $v > 0);

        $totalesData['TOTAL'] = (float) $venta->total;

        // ── Job encriptado ─────────────────────────────────────────────
        $job = [
            'logo'   => (bool) ($config->logo ?? false),
            'header' => $svc->encryptSection($key, $svc->buildEscHeader($headerData, $cols)),
            'body'   => $svc->encryptSection($key, $svc->buildEscBody($items, $cols)),
            'totals' => $svc->encryptSection($key, $svc->buildEscTotals($totalesData, $cols)),
            'footer' => $svc->encryptSection($key, $svc->buildEscFooter(
                '¡Gracias por su compra!',
                (bool) ($config->corte_automatico ?? true),
                (bool) ($config->abrir_cajon      ?? false),
                3,
                $cols
            )),
        ];

        $result = $svc->print($printerName, $job);

        if ($result['ok']) {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'error'   => $result['error'] ?? 'Error desconocido',
        ], $result['status'] ?: 503);
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

<?php

namespace App\Traits;

use App\Models\TenantConfig;
use App\Services\EscposPrinterService;

/**
 * Trait que proporciona la lógica de construcción de jobs ESC/POS
 * y despacho al Print Agent para componentes Livewire.
 *
 * PHP solo construye los bytes ESC/POS y los base64-codifica.
 * El agente local (via /api/encrypt/section + /api/encrypt) maneja
 * la encriptación y el protocolo print://.
 */
trait PrintsViaAgent
{
    protected function dispatchVentaPrint(\App\Models\Venta $venta, TenantConfig $config): void
    {
        /** @var EscposPrinterService $svc */
        $svc     = app(EscposPrinterService::class);
        $papel   = $config->papel_tamano_ventas ?: ($config->papel_tamano   ?? '80mm');
        $printer = $config->impresora_ventas    ?: ($config->impresora_nombre ?? '');
        $cols    = ($papel === '58mm') ? 32 : 48;

        $headerData = [
            'store'   => $config->nombre_tienda ?? 'MI TIENDA',
            'address' => $config->direccion     ?? '',
            'phone'   => $config->telefono      ?? '',
            'nit'     => $config->nit           ?? '',
            'title'   => 'VENTA #' . $venta->numero_folio,
            'date'    => $venta->created_at->format('d/m/Y H:i:s'),
            'user'    => $venta->user->name      ?? '',
            'client'  => $venta->cliente->nombre ?? '',
        ];

        $items = $venta->ventaItems->map(function ($item) {
            $nombre = $item->producto ? $item->producto->nombre : ($item->nombre ?? 'Producto');
            return [
                'nombre'   => $nombre,
                'cantidad' => $item->cantidad . ($item->medida ? ' ' . $item->medida : ''),
                'precio'   => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
            ];
        })->toArray();

        $totales = array_filter([
            'TOTAL'    => (float) $venta->total,
            'efectivo' => (float) ($venta->efectivo ?? 0),
            'online'   => (float) ($venta->online   ?? 0),
            'credito'  => (float) ($venta->credito  ?? 0),
            'cambio'   => (float) ($venta->cambio   ?? 0),
        ], fn($v) => $v > 0);
        $totales['TOTAL'] = (float) $venta->total;

        $this->dispatch('enviar-a-agente',
            agentUrl:   config('print_agent.base_url'),
            printer:    $printer,
            logo:       (bool) ($config->mostrar_logo ?? true),
            sections:   [
                'header' => base64_encode($svc->buildEscHeader($headerData, $cols)),
                'body'   => base64_encode($svc->buildEscBody($items, $cols)),
                'totals' => base64_encode($svc->buildEscTotals($totales, $cols)),
                'footer' => base64_encode($svc->buildEscFooter(
                    $this->printFooterLines($config),
                    (bool) ($config->corte_automatico ?? true),
                    (bool) ($config->abrir_cajon      ?? false),
                    5, $cols
                )),
            ],
            successMsg: 'Venta #' . $venta->numero_folio . ' enviada a imprimir'
        );
    }

    protected function dispatchPrestamoPrint(\App\Models\Prestamo $prestamo, TenantConfig $config): void
    {
        /** @var EscposPrinterService $svc */
        $svc     = app(EscposPrinterService::class);
        $papel   = $config->papel_tamano_prestamos ?: ($config->papel_tamano    ?? '80mm');
        $printer = $config->impresora_prestamos    ?: ($config->impresora_nombre ?? '');
        $cols    = ($papel === '58mm') ? 32 : 48;

        $headerData = [
            'store'   => $config->nombre_tienda ?? 'MI TIENDA',
            'address' => $config->direccion     ?? '',
            'phone'   => $config->telefono      ?? '',
            'nit'     => $config->nit           ?? '',
            'title'   => 'PRÉSTAMO #' . ($prestamo->numero_folio ?? $prestamo->id),
            'date'    => $prestamo->created_at->format('d/m/Y H:i:s'),
            'user'    => $prestamo->user->name      ?? '',
            'client'  => $prestamo->cliente->nombre ?? '',
        ];

        $items = $prestamo->prestamoItems->map(function ($item) {
            $nombre = $item->producto ? $item->producto->nombre : ($item->nombre ?? 'Producto');
            return [
                'nombre'   => $nombre,
                'cantidad' => $item->cantidad . ($item->medida ? ' ' . $item->medida : ''),
                'precio'   => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
            ];
        })->toArray();

        $this->dispatch('enviar-a-agente',
            agentUrl:   config('print_agent.base_url'),
            printer:    $printer,
            logo:       (bool) ($config->mostrar_logo ?? true),
            sections:   [
                'header' => base64_encode($svc->buildEscHeader($headerData, $cols)),
                'body'   => base64_encode($svc->buildEscBody($items, $cols)),
                'totals' => base64_encode($svc->buildEscTotals(['TOTAL' => (float) $prestamo->total], $cols)),
                'footer' => base64_encode($svc->buildEscFooter(
                    $this->printFooterLines($config),
                    (bool) ($config->corte_automatico ?? true),
                    false, 5, $cols
                )),
            ],
            successMsg: 'Préstamo #' . ($prestamo->numero_folio ?? $prestamo->id) . ' enviado a imprimir'
        );
    }

    private function printFooterLines(TenantConfig $config): array
    {
        $lines = ['¡Gracias por su compra!'];
        if (!empty($config->propietario_nombre))  $lines[] = $config->propietario_nombre;
        if (!empty($config->propietario_celular)) $lines[] = $config->propietario_celular;
        return $lines;
    }
}

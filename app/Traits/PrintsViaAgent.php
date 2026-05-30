<?php

namespace App\Traits;

use App\Models\TenantConfig;
use App\Services\EscposPrinterService;

/**
 * Trait que proporciona la lógica de construcción de jobs ESC/POS
 * y despacho al Print Agent para componentes Livewire.
 */
trait PrintsViaAgent
{
    /**
     * Construye el job de impresión para una venta y lo despacha al agente.
     * Usa la impresora/papel específicos del módulo "ventas" si están configurados,
     * con fallback a los valores globales.
     */
    protected function dispatchVentaPrint(\App\Models\Venta $venta, TenantConfig $config): void
    {
        /** @var EscposPrinterService $svc */
        $svc  = app(EscposPrinterService::class);
        $key  = $config->print_agent_secret_key ?? config('print_agent.secret_key');

        if (empty($key)) {
            return; // Sin clave no se puede enviar
        }

        $papel  = $config->papel_tamano_ventas  ?: ($config->papel_tamano  ?? '80mm');
        $printer = $config->impresora_ventas    ?: ($config->impresora_nombre ?? '');
        $cols   = ($papel === '58mm') ? 32 : 48;

        $header = [
            'store'   => $config->nombre_tienda ?? 'MI TIENDA',
            'address' => $config->direccion     ?? '',
            'phone'   => $config->telefono      ?? '',
            'nit'     => $config->nit           ?? '',
            'title'   => 'VENTA #' . $venta->numero_folio,
            'date'    => $venta->created_at->format('d/m/Y H:i:s'),
            'user'    => $venta->user->name     ?? '',
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

        $job = [
            'printer' => $printer,
            'logo'    => (bool) ($config->mostrar_logo ?? true),
            'header'  => $svc->encryptSection($key, $svc->buildEscHeader($header, $cols)),
            'body'    => $svc->encryptSection($key, $svc->buildEscBody($items, $cols)),
            'totals'  => $svc->encryptSection($key, $svc->buildEscTotals($totales, $cols)),
            'footer'  => $svc->encryptSection($key, $svc->buildEscFooter(
                $this->printFooterLines($config),
                (bool) ($config->corte_automatico ?? true),
                (bool) ($config->abrir_cajon      ?? false),
                5, $cols
            )),
        ];

        $this->dispatch('enviar-a-agente',
            agentUrl: config('print_agent.base_url'),
            job: $job,
            successMsg: 'Venta #' . $venta->numero_folio . ' enviada a imprimir'
        );
    }

    /**
     * Construye el job de impresión para un préstamo y lo despacha al agente.
     */
    protected function dispatchPrestamoPrint(\App\Models\Prestamo $prestamo, TenantConfig $config): void
    {
        /** @var EscposPrinterService $svc */
        $svc  = app(EscposPrinterService::class);
        $key  = $config->print_agent_secret_key ?? config('print_agent.secret_key');

        if (empty($key)) {
            return;
        }

        $papel   = $config->papel_tamano_prestamos  ?: ($config->papel_tamano   ?? '80mm');
        $printer = $config->impresora_prestamos     ?: ($config->impresora_nombre ?? '');
        $cols    = ($papel === '58mm') ? 32 : 48;

        $header = [
            'store'   => $config->nombre_tienda ?? 'MI TIENDA',
            'address' => $config->direccion     ?? '',
            'phone'   => $config->telefono      ?? '',
            'nit'     => $config->nit           ?? '',
            'title'   => 'PRÉSTAMO #' . ($prestamo->numero_folio ?? $prestamo->id),
            'date'    => $prestamo->created_at->format('d/m/Y H:i:s'),
            'user'    => $prestamo->user->name     ?? '',
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

        $totales = ['TOTAL' => (float) $prestamo->total];

        $job = [
            'printer' => $printer,
            'logo'    => (bool) ($config->mostrar_logo ?? true),
            'header'  => $svc->encryptSection($key, $svc->buildEscHeader($header, $cols)),
            'body'    => $svc->encryptSection($key, $svc->buildEscBody($items, $cols)),
            'totals'  => $svc->encryptSection($key, $svc->buildEscTotals($totales, $cols)),
            'footer'  => $svc->encryptSection($key, $svc->buildEscFooter(
                $this->printFooterLines($config),
                (bool) ($config->corte_automatico ?? true),
                false, 5, $cols
            )),
        ];

        $this->dispatch('enviar-a-agente',
            agentUrl: config('print_agent.base_url'),
            job: $job,
            successMsg: 'Préstamo #' . ($prestamo->numero_folio ?? $prestamo->id) . ' enviado a imprimir'
        );
    }

    /**
     * Líneas del footer: agradecimiento + propietario + celular.
     */
    private function printFooterLines(TenantConfig $config): array
    {
        $lines = ['¡Gracias por su compra!'];
        if (!empty($config->propietario_nombre)) {
            $lines[] = $config->propietario_nombre;
        }
        if (!empty($config->propietario_celular)) {
            $lines[] = $config->propietario_celular;
        }
        return $lines;
    }
}

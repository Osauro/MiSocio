<?php

namespace App\Console\Commands;

use App\Models\TenantConfig;
use App\Models\Venta;
use App\Services\GreenApiService;
use App\Services\TicketImageService;
use Illuminate\Console\Command;

class TestTicketWhatsApp extends Command
{
    protected $signature = 'greenapi:test-ticket
                            {--venta= : ID de la venta a usar (por defecto la última)}
                            {--tenant= : ID del tenant (por defecto el del landlord_phone)}';

    protected $description = 'Genera un ticket de venta como imagen y lo envía por WhatsApp (prueba)';

    public function handle(GreenApiService $greenApi, TicketImageService $ticketService): int
    {
        // ── Obtener tenant config ──────────────────────────────────────────
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            $config = TenantConfig::where('tenant_id', $tenantId)->first();
        } else {
            $config = TenantConfig::first();
        }

        if (!$config) {
            $this->error('No se encontró ninguna configuración de tenant.');
            return self::FAILURE;
        }

        $this->info("Tenant: {$config->nombre_tienda} (ID: {$config->tenant_id})");

        // ── Obtener venta ──────────────────────────────────────────────────
        $ventaId = $this->option('venta');

        if ($ventaId) {
            $venta = Venta::withoutGlobalScopes()->with(['ventaItems.producto', 'cliente', 'user'])->find($ventaId);
        } else {
            $venta = Venta::withoutGlobalScopes()
                ->where('tenant_id', $config->tenant_id)
                ->with(['ventaItems.producto', 'cliente', 'user'])
                ->latest()
                ->first();
        }

        if (!$venta) {
            $this->error('No se encontró ninguna venta.');
            return self::FAILURE;
        }

        $this->info("Venta: #{$venta->numero_folio} (ID: {$venta->id})");

        // ── Generar imagen de prueba ───────────────────────────────────────
        $this->line('Generando imagen del ticket...');
        $png     = $ticketService->generarTicketVenta($venta, $config);
        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "ticket_test_{$venta->id}.png";
        file_put_contents($tmpPath, $png);
        $this->info("Imagen guardada en: {$tmpPath} (" . strlen($png) . " bytes)");

        if (!$this->option('no-interaction') && !$this->confirm('¿Enviar la imagen por WhatsApp?', true)) {
            $this->warn('Envío cancelado.');
            return self::SUCCESS;
        }

        // ── Enviar ─────────────────────────────────────────────────────────
        $this->line('Enviando ticket por WhatsApp...');
        $result = $greenApi->sendVentaImagen($venta, $config);

        if ($result) {
            $this->info('✅ Ticket enviado correctamente por WhatsApp.');
        } else {
            $this->error('❌ Error al enviar el ticket. Revisa los logs.');
        }

        @unlink($tmpPath);

        return $result ? self::SUCCESS : self::FAILURE;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Prestamo;
use App\Models\TenantConfig;
use App\Services\GreenApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotificarVencimientosPrestamo extends Command
{
    protected $signature = 'prestamos:notificar-vencimiento';
    protected $description = 'Envía notificaciones WhatsApp para préstamos que vencen mañana o vencieron hoy';

    public function handle(GreenApiService $greenApi): int
    {
        $hoy   = now()->toDateString();
        $manana = now()->addDay()->toDateString();

        // Préstamos que vencen MAÑANA (aviso previo)
        $porVencer = Prestamo::withoutGlobalScopes()
            ->with(['cliente', 'tenant'])
            ->where('estado', 'Prestado')
            ->whereDate('expired_at', $manana)
            ->get();

        // Préstamos que vencieron HOY
        $vencidosHoy = Prestamo::withoutGlobalScopes()
            ->with(['cliente', 'tenant'])
            ->where('estado', 'Prestado')
            ->whereDate('expired_at', $hoy)
            ->get();

        $enviados = 0;

        foreach ($porVencer as $prestamo) {
            try {
                $config = TenantConfig::where('tenant_id', $prestamo->tenant_id)->first();
                if (!$config || empty($config->greenapi_notif_vencimiento_prestamo)) continue;

                if ($greenApi->notifyVencimientoPrestamo($prestamo, $config, true)) {
                    $enviados++;
                    $this->info("Aviso mañana → Folio #{$prestamo->numero_folio} | {$prestamo->cliente?->nombre}");
                }
            } catch (\Throwable $e) {
                Log::error("Error notif vencimiento préstamo #{$prestamo->id}: " . $e->getMessage());
            }
        }

        foreach ($vencidosHoy as $prestamo) {
            try {
                $config = TenantConfig::where('tenant_id', $prestamo->tenant_id)->first();
                if (!$config || empty($config->greenapi_notif_vencimiento_prestamo)) continue;

                if ($greenApi->notifyVencimientoPrestamo($prestamo, $config, false)) {
                    $enviados++;
                    $this->info("Aviso vencido HOY → Folio #{$prestamo->numero_folio} | {$prestamo->cliente?->nombre}");
                }
            } catch (\Throwable $e) {
                Log::error("Error notif vencido préstamo #{$prestamo->id}: " . $e->getMessage());
            }
        }

        $this->info("Total notificaciones enviadas: {$enviados}");

        return self::SUCCESS;
    }
}

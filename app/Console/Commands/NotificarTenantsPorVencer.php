<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\GreenApiService;
use Illuminate\Console\Command;

class NotificarTenantsPorVencer extends Command
{
    protected $signature = 'greenapi:notificar-por-vencer';

    protected $description = 'Envía notificaciones WhatsApp a tenants que vencen en los próximos 3 días';

    public function handle(GreenApiService $svc): int
    {
        $tenants = Tenant::withoutGlobalScopes()
            ->where('status', 1)
            ->whereBetween('bill_date', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->get();

        foreach ($tenants as $tenant) {
            $diasRestantes = (int) now()->diffInDays($tenant->bill_date, false);
            if ($diasRestantes < 0) continue;

            try {
                $svc->notifyTenantPorVencer($tenant, $diasRestantes);
                $this->info("Notificado: {$tenant->name} ({$diasRestantes} días)");
            } catch (\Throwable $e) {
                $this->error("Error notificando {$tenant->name}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Movimiento;
use App\Models\Tenant;
use Illuminate\Console\Command;

class RecalcularSaldosMovimientos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movimientos:recalcular-saldos {--tenant= : ID del tenant específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula todos los saldos de movimientos del tenant desde 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            // Recalcular para un tenant específico
            $this->recalcularParaTenant($tenantId);
        } else {
            // Recalcular para todos los tenants
            $tenants = Tenant::all();

            $this->info("Recalculando saldos para {$tenants->count()} tenants...");

            foreach ($tenants as $tenant) {
                $this->recalcularParaTenant($tenant->id);
            }
        }

        $this->info('¡Proceso completado!');
    }

    private function recalcularParaTenant($tenantId)
    {
        $this->info("Procesando Tenant ID: {$tenantId}");

        // Obtener todos los movimientos del tenant ordenados por ID
        $movimientos = Movimiento::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->orderBy('id', 'asc')
            ->get();

        if ($movimientos->isEmpty()) {
            $this->warn("  No hay movimientos para el tenant {$tenantId}");
            return;
        }

        $saldo = 0;
        $actualizados = 0;

        // Recalcular cada saldo
        foreach ($movimientos as $movimiento) {
            $saldo = $saldo + $movimiento->ingreso - $movimiento->egreso;

            // Actualizar solo si el saldo es diferente
            if ($movimiento->saldo != $saldo) {
                $movimiento->saldo = $saldo;
                $movimiento->saveQuietly(); // Guardar sin disparar eventos
                $actualizados++;
            }
        }

        $this->info("  ✓ {$movimientos->count()} movimientos procesados");
        $this->info("  ✓ {$actualizados} saldos actualizados");
        $this->info("  ✓ Saldo final: Bs. " . number_format($saldo, 2));
    }
}

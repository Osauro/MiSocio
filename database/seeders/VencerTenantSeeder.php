<?php

/**
 * Script para simular tenant 1 vencido hace 1 día con suscripción anual.
 *
 * Uso:
 *   php artisan db:seed --class=VencerTenantSeeder
 *
 * Para restaurar a activo:
 *   php artisan db:seed --class=VencerTenantSeeder --restore
 */

namespace Database\Seeders;

use App\Models\PlanSuscripcion;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VencerTenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::find(1);

        if (!$tenant) {
            $this->command->error('No se encontró el tenant con ID 1.');
            return;
        }

        // Guardar valores originales antes de modificar
        $original = [
            'bill_date'           => $tenant->bill_date,
            'subscription_type'   => $tenant->subscription_type,
            'plan_suscripcion_id' => $tenant->plan_suscripcion_id,
            'amount'              => $tenant->amount,
        ];

        $this->command->info("Tenant: [{$tenant->id}] {$tenant->name}");
        $this->command->table(
            ['Campo', 'Valor actual'],
            collect($original)->map(fn($v, $k) => [$k, $v ?? 'null'])->values()->toArray()
        );

        // Buscar plan anual (field: slug)
        $planAnual = PlanSuscripcion::where('slug', 'anual')->first()
                  ?? PlanSuscripcion::whereRaw("LOWER(nombre) LIKE '%anual%'")->first();

        // Fecha: vencido AYER
        $fechaVencida = Carbon::yesterday();

        $tenant->update([
            'subscription_type'   => 'anual',
            'bill_date'           => $fechaVencida,
            'plan_suscripcion_id' => $planAnual?->id ?? $tenant->plan_suscripcion_id,
            'amount'              => $planAnual?->precio ?? $tenant->amount,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Tenant 1 actualizado a VENCIDO (ayer):');
        $this->command->table(
            ['Campo', 'Nuevo valor'],
            [
                ['subscription_type',   $tenant->subscription_type],
                ['bill_date',           $fechaVencida->format('Y-m-d') . ' (ayer)'],
                ['plan_suscripcion_id', $tenant->plan_suscripcion_id ?? 'null'],
                ['amount',              $tenant->amount],
            ]
        );

        if ($planAnual) {
            $this->command->info("Plan usado: [{$planAnual->id}] {$planAnual->nombre} — Bs. {$planAnual->precio}");
        } else {
            $this->command->warn('No se encontró un plan de tipo "anual" en planes_suscripcion. Se mantuvo el plan actual.');
        }

        $this->command->newLine();
        $this->command->warn('Para restaurar los valores originales ejecuta:');
        $sql = "UPDATE tenants SET "
            . "subscription_type='" . ($original['subscription_type'] ?? 'demo') . "', "
            . "bill_date='" . (is_object($original['bill_date']) ? $original['bill_date']->format('Y-m-d') : ($original['bill_date'] ?? 'NULL')) . "', "
            . "plan_suscripcion_id=" . ($original['plan_suscripcion_id'] ?? 'NULL') . ", "
            . "amount=" . ($original['amount'] ?? 0) . " "
            . "WHERE id=1;";
        $this->command->line("  " . $sql);
    }
}

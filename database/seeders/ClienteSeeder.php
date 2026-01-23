<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los tenants activos
        $tenants = Tenant::where('status', 1)->get();

        foreach ($tenants as $tenant) {
            // Crear el primer cliente "SN" para ventas rápidas
            Cliente::factory()
                ->sinNombre()
                ->create([
                    'tenant_id' => $tenant->id,
                ]);

            // Crear entre 20 y 30 clientes aleatorios para cada tenant
            $cantidadClientes = rand(20, 30);

            Cliente::factory()
                ->count($cantidadClientes)
                ->create([
                    'tenant_id' => $tenant->id,
                ]);
        }

        $this->command->info('Clientes creados exitosamente para todos los tenants.');
    }
}

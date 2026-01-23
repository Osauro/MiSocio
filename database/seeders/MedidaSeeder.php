<?php

namespace Database\Seeders;

use App\Models\Medida;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class MedidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        $medidas = [
            'botella',
            'lata',
            'caja',
            'unidad',
            'litro',
            'mililitro',
            'paquete',
            'six pack',
        ];

        foreach ($tenants as $tenant) {
            foreach ($medidas as $medida) {
                Medida::create([
                    'tenant_id' => $tenant->id,
                    'nombre' => $medida,
                ]);
            }
        }
    }
}

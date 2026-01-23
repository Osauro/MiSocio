<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'Licorería El Paceño',
                'domain' => 'elpaceno',
                'subscription_type' => 'anual',
                'amount' => 150.00,
                'theme_number' => 5,
            ],
            [
                'name' => 'Distribuidora Illimani',
                'domain' => 'illimani',
                'subscription_type' => 'mensual',
                'amount' => 100.00,
                'theme_number' => 2,
            ],
            [
                'name' => 'Supermercado San Miguel',
                'domain' => 'sanmiguel',
                'subscription_type' => 'mensual',
                'amount' => 120.00,
                'theme_number' => 3,
            ],
            [
                'name' => 'Minimarket Zona Sur',
                'domain' => 'zonasur',
                'subscription_type' => 'anual',
                'amount' => 180.00,
                'theme_number' => 4,
            ],
            [
                'name' => 'Bodega Central',
                'domain' => 'bodegacentral',
                'subscription_type' => 'mensual',
                'amount' => 90.00,
                'theme_number' => 6,
            ],
            [
                'name' => 'Tienda El Progreso',
                'domain' => 'elprogreso',
                'subscription_type' => 'mensual',
                'amount' => 110.00,
                'theme_number' => 1,
            ],
            [
                'name' => 'Abarrotes La Paz',
                'domain' => 'abarroteslapaz',
                'subscription_type' => 'mensual',
                'amount' => 95.00,
                'theme_number' => 5,
            ],
            [
                'name' => 'Comercial Los Andes',
                'domain' => 'losandes',
                'subscription_type' => 'anual',
                'amount' => 200.00,
                'theme_number' => 2,
            ],
            [
                'name' => 'Tienda Familiar',
                'domain' => 'familiar',
                'subscription_type' => 'mensual',
                'amount' => 85.00,
                'theme_number' => 3,
            ],
            [
                'name' => 'Market Express',
                'domain' => 'marketexpress',
                'subscription_type' => 'mensual',
                'amount' => 130.00,
                'theme_number' => 4,
            ],
        ];

        foreach ($tenants as $tenantData) {
            Tenant::create([
                'name' => $tenantData['name'],
                'domain' => $tenantData['domain'],
                'subscription_type' => $tenantData['subscription_type'],
                'amount' => $tenantData['amount'],
                'theme_number' => $tenantData['theme_number'],
                'bill_date' => now()->addMonth(),
                'status' => 1,
            ]);
        }
    }
}

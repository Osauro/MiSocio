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
        Tenant::create([
            'name' => 'Licorería El Paceño',
            'domain' => 'elpaceno',
            'subscription_type' => 'anual',
            'amount' => 150.00,
            'bill_date' => now()->addMonth(),
            'status' => 1,
        ]);

        Tenant::create([
            'name' => 'Distribuidora Illimani',
            'domain' => 'illimani',
            'subscription_type' => 'mensual',
            'amount' => 100.00,
            'bill_date' => now()->addMonth(),
            'status' => 1,
        ]);
    }
}

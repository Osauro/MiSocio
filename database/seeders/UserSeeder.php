<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Landlord - Super Admin del sistema
        // Este usuario gestiona el sistema completo (tenants, pagos, planes)
        User::create([
            'name' => 'Administrador LicoPOS',
            'celular' => '73010688',
            'password' => Hash::make('5421'),
            'is_super_admin' => true, // Landlord/Super Admin
        ]);

        // Sistema limpio - Sin tenants ni usuarios adicionales
        // Los usuarios y tenants se crearán según las suscripciones
    }
}

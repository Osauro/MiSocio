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
        // Usuario principal - Asociado a TODOS los tenants (10)
        $diego = User::create([
            'name' => 'Diego Alejandro Quinta Rios',
            'celular' => '73010688',
            'password' => Hash::make('5421'),
        ]);

        // Asociar Diego a los 10 tenants
        for ($i = 1; $i <= 10; $i++) {
            $role = $i === 1 ? 'landlord' : 'tenant'; // Primer tenant como landlord, resto como tenant
            $diego->tenants()->attach($i, [
                'role' => $role,
                'is_active' => true,
            ]);
        }

        // Usuario adicional - Solo en El Paceño
        $maria = User::create([
            'name' => 'María González',
            'celular' => '70123456',
            'password' => Hash::make('password'),
        ]);

        $maria->tenants()->attach(1, [
            'role' => 'user',
            'is_active' => true,
        ]);

        // Usuario adicional - Solo en Illimani
        $juan = User::create([
            'name' => 'Juan Pérez',
            'celular' => '71234567',
            'password' => Hash::make('password'),
        ]);

        $juan->tenants()->attach(2, [
            'role' => 'user',
            'is_active' => true,
        ]);

        // Usuario adicional - En ambos tenants
        $carlos = User::create([
            'name' => 'Carlos Rodríguez',
            'celular' => '72345678',
            'password' => Hash::make('password'),
        ]);

        $carlos->tenants()->attach([
            1 => ['role' => 'user', 'is_active' => true],
            2 => ['role' => 'tenant', 'is_active' => true],
        ]);
    }
}

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
        // Usuario principal - Asociado a AMBOS tenants
        $diego = User::create([
            'name' => 'Diego Alejandro Quinta Rios',
            'celular' => '73010688',
            'password' => Hash::make('5421'),
        ]);

        // Asociar Diego a Licorería El Paceño como tenant
        $diego->tenants()->attach(1, [
            'role' => 'tenant',
            'is_active' => true,
        ]);

        // Asociar Diego a Distribuidora Illimani como landlord
        $diego->tenants()->attach(2, [
            'role' => 'landlord',
            'is_active' => true,
        ]);

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

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
        $user = User::create([
            'name' => 'Diego Alejandro Quinta Rios',
            'celular' => '73010688',
            'password' => Hash::make('5421'),
        ]);

        // Asociar el usuario con el tenant
        $user->tenants()->attach(1, [
            'role' => 'tenant',
            'is_active' => true,
        ]);
    }
}

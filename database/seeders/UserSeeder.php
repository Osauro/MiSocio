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
        User::create([
            'tenant_id' => 1, // Asociado a Licorera El Paceño
            'name' => 'Diego Alejandro Quinta Rios',
            'celular' => '73010688',
            'password' => Hash::make('5421'),
            'role' => 'tenant',
        ]);
    }
}

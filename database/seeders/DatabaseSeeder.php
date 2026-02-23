<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Sistema limpio - Solo landlord y planes de suscripción
        $this->call([
            UserSeeder::class,              // Crea usuario landlord
            PlanesSuscripcionSeeder::class, // Crea planes de suscripción
        ]);

        // Seeders de datos de demostración (comentados)
        // Descomenta si necesitas datos de prueba
        // $this->call([
        //     TenantSeeder::class,
        //     CategoriaSeeder::class,
        //     MedidaSeeder::class,
        //     ProductoSeeder::class,
        //     ClienteSeeder::class,
        // ]);
    }
}

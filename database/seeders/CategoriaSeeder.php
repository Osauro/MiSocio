<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        $categorias = [
            ['nombre' => 'Cervezas', 'imagen' => null],
            ['nombre' => 'Vinos', 'imagen' => null],
            ['nombre' => 'Licores', 'imagen' => null],
            ['nombre' => 'Refrescos', 'imagen' => null],
            ['nombre' => 'Whisky y Ron', 'imagen' => null],
            ['nombre' => 'Bebidas Energéticas', 'imagen' => null],
        ];

        foreach ($tenants as $tenant) {
            foreach ($categorias as $categoria) {
                Categoria::create([
                    'tenant_id' => $tenant->id,
                    'nombre' => $categoria['nombre'],
                    'imagen' => $categoria['imagen'],
                ]);
            }
        }
    }
}

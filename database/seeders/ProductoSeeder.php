<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Obtener categorías del tenant
            $cervezas = Categoria::where('tenant_id', $tenant->id)->where('nombre', 'Cervezas')->first();
            $vinos = Categoria::where('tenant_id', $tenant->id)->where('nombre', 'Vinos')->first();
            $licores = Categoria::where('tenant_id', $tenant->id)->where('nombre', 'Licores')->first();
            $refrescos = Categoria::where('tenant_id', $tenant->id)->where('nombre', 'Refrescos')->first();
            $whiskeyRon = Categoria::where('tenant_id', $tenant->id)->where('nombre', 'Whisky y Ron')->first();
            $energeticas = Categoria::where('tenant_id', $tenant->id)->where('nombre', 'Bebidas Energéticas')->first();

            // Cervezas bolivianas y populares
            $productos = [
                // CERVEZAS
                [
                    'categoria_id' => $cervezas->id,
                    'nombre' => 'Cerveza Paceña 620ml',
                    'codigo' => 'CRV001',
                    'medida' => 'botella',
                    'cantidad' => 620,
                    'precio_de_compra' => 8.00,
                    'precio_por_mayor' => 10.00,
                    'precio_por_menor' => 12.00,
                    'stock' => 150,
                ],
                [
                    'categoria_id' => $cervezas->id,
                    'nombre' => 'Cerveza Huari 620ml',
                    'codigo' => 'CRV002',
                    'medida' => 'botella',
                    'cantidad' => 620,
                    'precio_de_compra' => 7.50,
                    'precio_por_mayor' => 9.50,
                    'precio_por_menor' => 11.00,
                    'stock' => 120,
                ],
                [
                    'categoria_id' => $cervezas->id,
                    'nombre' => 'Cerveza Taquiña 620ml',
                    'codigo' => 'CRV003',
                    'medida' => 'botella',
                    'cantidad' => 620,
                    'precio_de_compra' => 7.50,
                    'precio_por_mayor' => 9.50,
                    'precio_por_menor' => 11.00,
                    'stock' => 100,
                ],
                [
                    'categoria_id' => $cervezas->id,
                    'nombre' => 'Cerveza Ducal 355ml Lata',
                    'codigo' => 'CRV004',
                    'medida' => 'lata',
                    'cantidad' => 355,
                    'precio_de_compra' => 6.00,
                    'precio_por_mayor' => 7.50,
                    'precio_por_menor' => 9.00,
                    'stock' => 200,
                ],
                [
                    'categoria_id' => $cervezas->id,
                    'nombre' => 'Cerveza Corona 355ml',
                    'codigo' => 'CRV005',
                    'medida' => 'botella',
                    'cantidad' => 355,
                    'precio_de_compra' => 12.00,
                    'precio_por_mayor' => 15.00,
                    'precio_por_menor' => 18.00,
                    'stock' => 80,
                ],
                [
                    'categoria_id' => $cervezas->id,
                    'nombre' => 'Cerveza Heineken 330ml',
                    'codigo' => 'CRV006',
                    'medida' => 'botella',
                    'cantidad' => 330,
                    'precio_de_compra' => 11.00,
                    'precio_por_mayor' => 14.00,
                    'precio_por_menor' => 16.00,
                    'stock' => 90,
                ],

                // VINOS
                [
                    'categoria_id' => $vinos->id,
                    'nombre' => 'Vino Kohlberg Cabernet 750ml',
                    'codigo' => 'VIN001',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 45.00,
                    'precio_por_mayor' => 55.00,
                    'precio_por_menor' => 65.00,
                    'stock' => 40,
                ],
                [
                    'categoria_id' => $vinos->id,
                    'nombre' => 'Vino Campos de Solana Malbec 750ml',
                    'codigo' => 'VIN002',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 55.00,
                    'precio_por_mayor' => 70.00,
                    'precio_por_menor' => 85.00,
                    'stock' => 30,
                ],
                [
                    'categoria_id' => $vinos->id,
                    'nombre' => 'Vino La Concepción Tannat 750ml',
                    'codigo' => 'VIN003',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 50.00,
                    'precio_por_mayor' => 65.00,
                    'precio_por_menor' => 75.00,
                    'stock' => 35,
                ],
                [
                    'categoria_id' => $vinos->id,
                    'nombre' => 'Vino Aranjuez Blanco 750ml',
                    'codigo' => 'VIN004',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 38.00,
                    'precio_por_mayor' => 48.00,
                    'precio_por_menor' => 58.00,
                    'stock' => 45,
                ],

                // LICORES
                [
                    'categoria_id' => $licores->id,
                    'nombre' => 'Singani Rujero 750ml',
                    'codigo' => 'LIC001',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 35.00,
                    'precio_por_mayor' => 45.00,
                    'precio_por_menor' => 55.00,
                    'stock' => 60,
                ],
                [
                    'categoria_id' => $licores->id,
                    'nombre' => 'Singani de Oro 750ml',
                    'codigo' => 'LIC002',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 32.00,
                    'precio_por_mayor' => 42.00,
                    'precio_por_menor' => 50.00,
                    'stock' => 70,
                ],
                [
                    'categoria_id' => $licores->id,
                    'nombre' => 'Aguardiente Ceibo 750ml',
                    'codigo' => 'LIC003',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 25.00,
                    'precio_por_mayor' => 32.00,
                    'precio_por_menor' => 40.00,
                    'stock' => 50,
                ],
                [
                    'categoria_id' => $licores->id,
                    'nombre' => 'Licor de Coca Agwa 700ml',
                    'codigo' => 'LIC004',
                    'medida' => 'botella',
                    'cantidad' => 700,
                    'precio_de_compra' => 80.00,
                    'precio_por_mayor' => 100.00,
                    'precio_por_menor' => 120.00,
                    'stock' => 25,
                ],
                [
                    'categoria_id' => $licores->id,
                    'nombre' => 'Pisco Capel 750ml',
                    'codigo' => 'LIC005',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 55.00,
                    'precio_por_mayor' => 70.00,
                    'precio_por_menor' => 85.00,
                    'stock' => 40,
                ],

                // WHISKY Y RON
                [
                    'categoria_id' => $whiskeyRon->id,
                    'nombre' => 'Whisky Johnnie Walker Red Label 750ml',
                    'codigo' => 'WHR001',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 120.00,
                    'precio_por_mayor' => 145.00,
                    'precio_por_menor' => 170.00,
                    'stock' => 30,
                ],
                [
                    'categoria_id' => $whiskeyRon->id,
                    'nombre' => 'Whisky Johnnie Walker Black Label 750ml',
                    'codigo' => 'WHR002',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 180.00,
                    'precio_por_mayor' => 220.00,
                    'precio_por_menor' => 260.00,
                    'stock' => 20,
                ],
                [
                    'categoria_id' => $whiskeyRon->id,
                    'nombre' => 'Ron Bacardi Blanco 750ml',
                    'codigo' => 'WHR003',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 75.00,
                    'precio_por_mayor' => 95.00,
                    'precio_por_menor' => 110.00,
                    'stock' => 35,
                ],
                [
                    'categoria_id' => $whiskeyRon->id,
                    'nombre' => 'Ron Havana Club 3 Años 700ml',
                    'codigo' => 'WHR004',
                    'medida' => 'botella',
                    'cantidad' => 700,
                    'precio_de_compra' => 85.00,
                    'precio_por_mayor' => 105.00,
                    'precio_por_menor' => 125.00,
                    'stock' => 28,
                ],
                [
                    'categoria_id' => $whiskeyRon->id,
                    'nombre' => 'Whisky Jack Daniels 750ml',
                    'codigo' => 'WHR005',
                    'medida' => 'botella',
                    'cantidad' => 750,
                    'precio_de_compra' => 200.00,
                    'precio_por_mayor' => 240.00,
                    'precio_por_menor' => 280.00,
                    'stock' => 15,
                ],

                // REFRESCOS
                [
                    'categoria_id' => $refrescos->id,
                    'nombre' => 'Coca Cola 2L',
                    'codigo' => 'REF001',
                    'medida' => 'botella',
                    'cantidad' => 2000,
                    'precio_de_compra' => 10.00,
                    'precio_por_mayor' => 12.00,
                    'precio_por_menor' => 14.00,
                    'stock' => 200,
                ],
                [
                    'categoria_id' => $refrescos->id,
                    'nombre' => 'Coca Cola 500ml',
                    'codigo' => 'REF002',
                    'medida' => 'botella',
                    'cantidad' => 500,
                    'precio_de_compra' => 4.50,
                    'precio_por_mayor' => 5.50,
                    'precio_por_menor' => 6.50,
                    'stock' => 300,
                ],
                [
                    'categoria_id' => $refrescos->id,
                    'nombre' => 'Simba Frutilla 2L',
                    'codigo' => 'REF003',
                    'medida' => 'botella',
                    'cantidad' => 2000,
                    'precio_de_compra' => 8.00,
                    'precio_por_mayor' => 10.00,
                    'precio_por_menor' => 12.00,
                    'stock' => 180,
                ],
                [
                    'categoria_id' => $refrescos->id,
                    'nombre' => 'Sprite 2L',
                    'codigo' => 'REF004',
                    'medida' => 'botella',
                    'cantidad' => 2000,
                    'precio_de_compra' => 10.00,
                    'precio_por_mayor' => 12.00,
                    'precio_por_menor' => 14.00,
                    'stock' => 150,
                ],
                [
                    'categoria_id' => $refrescos->id,
                    'nombre' => 'Fanta Naranja 2L',
                    'codigo' => 'REF005',
                    'medida' => 'botella',
                    'cantidad' => 2000,
                    'precio_de_compra' => 10.00,
                    'precio_por_mayor' => 12.00,
                    'precio_por_menor' => 14.00,
                    'stock' => 160,
                ],
                [
                    'categoria_id' => $refrescos->id,
                    'nombre' => 'Agua Vital 2L',
                    'codigo' => 'REF006',
                    'medida' => 'botella',
                    'cantidad' => 2000,
                    'precio_de_compra' => 5.00,
                    'precio_por_mayor' => 6.50,
                    'precio_por_menor' => 8.00,
                    'stock' => 250,
                ],
                [
                    'categoria_id' => $refrescos->id,
                    'nombre' => 'Agua Vital 500ml',
                    'codigo' => 'REF007',
                    'medida' => 'botella',
                    'cantidad' => 500,
                    'precio_de_compra' => 2.00,
                    'precio_por_mayor' => 2.50,
                    'precio_por_menor' => 3.00,
                    'stock' => 400,
                ],

                // BEBIDAS ENERGÉTICAS
                [
                    'categoria_id' => $energeticas->id,
                    'nombre' => 'Red Bull 250ml',
                    'codigo' => 'ENR001',
                    'medida' => 'lata',
                    'cantidad' => 250,
                    'precio_de_compra' => 12.00,
                    'precio_por_mayor' => 15.00,
                    'precio_por_menor' => 18.00,
                    'stock' => 120,
                ],
                [
                    'categoria_id' => $energeticas->id,
                    'nombre' => 'Monster Energy 473ml',
                    'codigo' => 'ENR002',
                    'medida' => 'lata',
                    'cantidad' => 473,
                    'precio_de_compra' => 13.00,
                    'precio_por_mayor' => 16.00,
                    'precio_por_menor' => 19.00,
                    'stock' => 100,
                ],
                [
                    'categoria_id' => $energeticas->id,
                    'nombre' => 'Speed Max 250ml',
                    'codigo' => 'ENR003',
                    'medida' => 'lata',
                    'cantidad' => 250,
                    'precio_de_compra' => 8.00,
                    'precio_por_mayor' => 10.00,
                    'precio_por_menor' => 12.00,
                    'stock' => 150,
                ],
                [
                    'categoria_id' => $energeticas->id,
                    'nombre' => 'Burn Energy 250ml',
                    'codigo' => 'ENR004',
                    'medida' => 'lata',
                    'cantidad' => 250,
                    'precio_de_compra' => 10.00,
                    'precio_por_mayor' => 12.50,
                    'precio_por_menor' => 15.00,
                    'stock' => 110,
                ],
            ];

            // Crear productos para el tenant
            foreach ($productos as $producto) {
                Producto::create([
                    'tenant_id' => $tenant->id,
                    'categoria_id' => $producto['categoria_id'],
                    'nombre' => $producto['nombre'],
                    'codigo' => $producto['codigo'],
                    'imagen' => null,
                    'medida' => $producto['medida'],
                    'cantidad' => $producto['cantidad'],
                    'precio_de_compra' => $producto['precio_de_compra'],
                    'precio_por_mayor' => $producto['precio_por_mayor'],
                    'precio_por_menor' => $producto['precio_por_menor'],
                    'stock' => $producto['stock'],
                ]);
            }
        }
    }
}

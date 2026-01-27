<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los tenants
        $tenants = \App\Models\Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->error('No se encontraron tenants.');
            return;
        }

        $this->command->info("Generando compras para {$tenants->count()} tenants...");

        foreach ($tenants as $tenant) {
            $this->command->info("Procesando tenant ID: {$tenant->id} - {$tenant->name}");

            // Obtener usuarios asociados al tenant mediante la relación
            $usuarios = $tenant->users;
            $proveedores = Cliente::where('tenant_id', $tenant->id)->get();
            $productos = Producto::where('tenant_id', $tenant->id)->get();

            if ($usuarios->isEmpty() || $proveedores->isEmpty() || $productos->isEmpty()) {
                $this->command->warn("Tenant {$tenant->id} no tiene usuarios, clientes o productos. Saltando...");
                continue;
            }

            $cantidadCompras = rand(20, 50);
            $this->command->info("  Generando {$cantidadCompras} compras para tenant {$tenant->id}...");

            $estados = ['Completo', 'Pendiente'];
            $estadosPesos = [
                'Completo' => 80,    // 80% completas
                'Pendiente' => 20,   // 20% pendientes
            ];

            for ($i = 0; $i < $cantidadCompras; $i++) {
                // Seleccionar estado según probabilidades
                $rand = rand(1, 100);
                if ($rand <= 80) {
                    $estado = 'Completo';
                } else {
                    $estado = 'Pendiente';
                }

                // Generar fecha aleatoria en los últimos 6 meses
                $fechaAleatoria = now()->subDays(rand(0, 180));

                // Crear items de compra primero para calcular el total
                $cantidadItems = rand(1, 8);
                $productosCompra = $productos->random(min($cantidadItems, $productos->count()));

                $totalCompra = 0;
                $items = [];

                foreach ($productosCompra as $producto) {
                    $cantidad = rand(1, 50);
                    // Precio de compra ligeramente menor al precio actual del producto
                    $precioCompra = $producto->precio_de_compra * (rand(90, 100) / 100);
                    $subtotal = $cantidad * $precioCompra;
                    $totalCompra += $subtotal;

                    $items[] = [
                        'producto_id' => $producto->id,
                        'cantidad' => $cantidad,
                        'precio' => round($precioCompra, 2),
                        'subtotal' => round($subtotal, 2),
                    ];
                }

                // Distribuir el total entre métodos de pago (solo efectivo y crédito)
                $tipoPago = rand(1, 100);
                if ($tipoPago <= 70) {
                    // 70% solo efectivo
                    $efectivo = $totalCompra;
                    $credito = 0;
                } else {
                    // 30% efectivo + crédito
                    $efectivo = round($totalCompra * (rand(40, 70) / 100), 2);
                    $credito = round($totalCompra - $efectivo, 2);
                }

                // Crear la compra
                $compra = Compra::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $usuarios->random()->id,
                    'proveedor_id' => $proveedores->random()->id,
                    'estado' => $estado,
                    'efectivo' => $efectivo,
                    'credito' => $credito,
                    'created_at' => $fechaAleatoria,
                    'updated_at' => $fechaAleatoria,
                ]);

                // Crear los items de compra
                foreach ($items as $item) {
                    CompraItem::create([
                        'compra_id' => $compra->id,
                        'producto_id' => $item['producto_id'],
                        'cantidad' => $item['cantidad'],
                        'precio' => $item['precio'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                if (($i + 1) % 10 == 0) {
                    $this->command->info("  Creadas " . ($i + 1) . " compras...");
                }
            }

            $this->command->info("  ✓ Completado: {$cantidadCompras} compras creadas para tenant {$tenant->id}");
        }

        $this->command->info('✓ Proceso completado para todos los tenants.');
    }
}

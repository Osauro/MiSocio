<?php

namespace Database\Seeders;

use App\Models\Kardex;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class KardexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los productos agrupados por tenant
        $productosPorTenant = Producto::with('tenant')->get()->groupBy('tenant_id');

        if ($productosPorTenant->isEmpty()) {
            $this->command->warn('No hay productos para generar kardex');
            return;
        }

        $tiposMovimiento = [
            'Compra inicial',
            'Venta',
            'Ajuste de inventario',
            'Devolución',
            'Incremento por conteo',
            'Decremento por merma',
            'Venta mostrador',
            'Compra proveedor'
        ];

        foreach ($productosPorTenant as $tenantId => $productos) {
            // Obtener usuarios del tenant
            $users = User::whereHas('tenants', function($q) use ($tenantId) {
                $q->where('tenants.id', $tenantId);
            })->get();

            if ($users->isEmpty()) {
                $this->command->warn("No hay usuarios para el tenant {$tenantId}");
                continue;
            }

            $this->command->info("Generando kardex para tenant {$tenantId} ({$productos->count()} productos)...");

            // Generar movimientos para cada producto
            foreach ($productos as $producto) {
                $saldoActual = 0;
                $fechaBase = Carbon::now()->subDays(30);

                // Generar entre 5 y 15 movimientos por producto
                $cantidadMovimientos = rand(5, 15);

                for ($i = 0; $i < $cantidadMovimientos; $i++) {
                    $anterior = $saldoActual;
                    $esEntrada = rand(0, 1) === 1 || $saldoActual < 5; // Más probabilidad de entrada si hay poco stock

                    if ($esEntrada) {
                        $entrada = rand(5, 50);
                        $salida = 0;
                        $saldoActual += $entrada;
                    } else {
                        $entrada = 0;
                        $salida = rand(1, min(10, $saldoActual)); // No vender más de lo que hay
                        $saldoActual -= $salida;
                    }

                    $precio = $esEntrada ? $producto->precio_de_compra : $producto->precio_por_menor;
                    $cantidad = $entrada > 0 ? $entrada : $salida;
                    $total = $cantidad * $precio;

                    Kardex::create([
                        'tenant_id' => $tenantId,
                        'user_id' => $users->random()->id,
                        'producto_id' => $producto->id,
                        'entrada' => $entrada,
                        'salida' => $salida,
                        'anterior' => $anterior,
                        'saldo' => $saldoActual,
                        'precio' => $precio,
                        'total' => $total,
                        'obs' => $tiposMovimiento[array_rand($tiposMovimiento)],
                        'created_at' => $fechaBase->copy()->addDays($i)->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                        'updated_at' => now()
                    ]);
                }

                // Actualizar el stock del producto con el saldo final
                $producto->update(['stock' => $saldoActual]);
            }
        }

        $this->command->info('Kardex poblado con datos de ejemplo exitosamente para todos los tenants!');
    }
}

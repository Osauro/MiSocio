<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== RECALCULAR PRECIOS POR MAYOR ===\n\n";
echo "Este script recalcula precio_por_mayor = cantidad × precio_por_menor\n\n";

$productos = DB::table('productos')
    ->whereNotNull('cantidad')
    ->where('cantidad', '>', 1)
    ->whereNotNull('precio_por_menor')
    ->get(['id', 'nombre', 'cantidad', 'precio_por_menor', 'precio_por_mayor']);

$actualizados = 0;
$correctos = 0;

foreach ($productos as $producto) {
    $precioCalculado = $producto->cantidad * $producto->precio_por_menor;

    if (abs($producto->precio_por_mayor - $precioCalculado) > 0.01) {
        DB::table('productos')
            ->where('id', $producto->id)
            ->update(['precio_por_mayor' => $precioCalculado]);

        echo "✓ {$producto->nombre}\n";
        echo "  Cantidad: {$producto->cantidad} | Precio por menor: Bs. {$producto->precio_por_menor}\n";
        echo "  Anterior: Bs. {$producto->precio_por_mayor} → Nuevo: Bs. {$precioCalculado}\n\n";
        $actualizados++;
    } else {
        $correctos++;
    }
}

echo "=== RESULTADO ===\n";
echo "Productos actualizados: {$actualizados}\n";
echo "Productos ya correctos: {$correctos}\n";
echo "Total verificados: " . ($actualizados + $correctos) . "\n";
